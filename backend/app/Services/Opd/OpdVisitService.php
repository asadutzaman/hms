<?php

namespace App\Services\Opd;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Exceptions\ApiException;
use App\Models\Appointment;
use App\Models\OpdVisit;
use App\Repositories\OpdVisitRepository;
use App\Repositories\PatientRepository;
use App\Services\CommonService;
use App\Validators\OpdVisitValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class OpdVisitService
{
    protected $visitRepo;
    protected $patientRepo;
    protected $common;
    protected $validatorCls;

    public function __construct(
        OpdVisitRepository $visitRepo,
        PatientRepository $patientRepo,
        CommonService $common,
    ) {
        $this->visitRepo = $visitRepo;
        $this->patientRepo = $patientRepo;
        $this->common = $common;
        $this->validatorCls = OpdVisitValidator::class;
    }

    /**
     * Create an OPD visit (walk-in or from appointment).
     *
     * @param array $data validated request input
     * @param int   $actorId authenticated user id
     */
    public function create(array $data, int $actorId): OpdVisit
    {
        $data = $this->normalizeOnCreate($data);
        $this->validateOrThrow($data, 'POST');

        return DB::transaction(function () use ($data, $actorId) {
            // If linked to an appointment, ensure the appointment is not already used
            $apptId = $data['appointment_id'] ?? null;
            if ($apptId) {
                $appt = Appointment::query()->lockForUpdate()->find($apptId);
                if (!$appt) {
                    throw new ApiException('Linked appointment does not exist.', 422);
                }
                $existing = OpdVisit::query()
                    ->where('appointment_id', $apptId)
                    ->whereNull('deleted_at')
                    ->first();
                if ($existing) {
                    throw new ApiException('This appointment is already linked to an OPD visit.', 422);
                }
            }

            $visit = $this->visitRepo->create(array_merge($data, [
                'status'      => $data['status'] ?? OpdVisitStatusEnum::WAITING,
                'created_by'  => $actorId,
                'updated_by'  => $actorId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]));

            // Audit log
            $this->visitRepo->logAudit(
                $visit,
                OpdVisitActionEnum::CREATE,
                null,
                $visit->status,
                $actorId,
                'OPD visit created',
                [
                    'visit_type'    => $visit->visit_type,
                    'appointment_id' => $visit->appointment_id,
                ],
            );

            return $visit->fresh();
        });
    }

    /**
     * Update an existing OPD visit (non-status fields).
     */
    public function update(int $id, array $data, int $actorId): OpdVisit
    {
        $this->validateOrThrow($data, 'PUT');

        return DB::transaction(function () use ($id, $data, $actorId) {
            $visit = $this->visitRepo->find($id);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            // Prevent editing terminal-state visits
            if (OpdVisitStatusEnum::isTerminal((string) $visit->status)) {
                throw new ApiException(
                    "Cannot edit a visit in terminal state '{$visit->status}'.",
                    422,
                );
            }

            $this->visitRepo->update($id, array_merge($data, [
                'updated_by' => $actorId,
                'updated_at' => now(),
            ]));

            $visit = $this->visitRepo->find($id);

            $this->visitRepo->logAudit(
                $visit,
                OpdVisitActionEnum::UPDATE,
                null,
                null,
                $actorId,
                'OPD visit updated',
                ['changed_fields' => array_keys($data)],
            );

            return $visit;
        });
    }

    /**
     * Transition the visit status with transition-matrix enforcement.
     */
    public function transition(int $id, string $toStatus, int $actorId, ?string $remarks = null, array $meta = []): OpdVisit
    {
        return DB::transaction(function () use ($id, $toStatus, $actorId, $remarks, $meta) {
            $visit = $this->visitRepo->find($id);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            if (!in_array($toStatus, OpdVisitStatusEnum::getKeys(), true)) {
                throw new ApiException("Invalid status '{$toStatus}'.", 422);
            }

            if (!OpdVisitStatusEnum::canTransition((string) $visit->status, $toStatus)) {
                throw new ApiException(
                    "Cannot transition from '{$visit->status}' to '{$toStatus}'.",
                    422,
                );
            }

            $updated = $this->visitRepo->transitionStatus(
                $id,
                $toStatus,
                $actorId,
                $remarks,
                $meta,
                OpdVisitActionEnum::STATUS_CHANGE,
            );

            return $updated;
        });
    }

    /**
     * Cancel an OPD visit with reason. Allowed from any non-terminal state.
     */
    public function cancel(int $id, int $actorId, string $reason): OpdVisit
    {
        return DB::transaction(function () use ($id, $actorId, $reason) {
            $visit = $this->visitRepo->find($id);
            if (!$visit) {
                throw new ApiException('OPD visit not found.', 404);
            }

            if (OpdVisitStatusEnum::isTerminal((string) $visit->status)) {
                throw new ApiException(
                    "Cannot cancel a visit in terminal state '{$visit->status}'.",
                    422,
                );
            }

            return $this->visitRepo->transitionStatus(
                $id,
                OpdVisitStatusEnum::CANCELLED,
                $actorId,
                $reason,
                ['reason' => $reason],
                OpdVisitActionEnum::CANCEL,
            );
        });
    }

    /**
     * Fetch visits filtered + paginated via OData.
     */
    public function list()
    {
        return $this->visitRepo->getList();
    }

    public function find(int $id): ?OpdVisit
    {
        return $this->visitRepo->find($id);
    }

    public function delete(int $id, int $actorId): bool
    {
        $visit = $this->visitRepo->find($id);
        if (!$visit) {
            throw new ApiException('OPD visit not found.', 404);
        }
        if ((string) $visit->status !== OpdVisitStatusEnum::WAITING) {
            throw new ApiException(
                "Only WAITING visits can be deleted (current: {$visit->status}).",
                422,
            );
        }
        return $this->visitRepo->delete($id);
    }

    /* ---------------- internals ---------------- */

    protected function validateOrThrow(array $data, string $fakeMethod): void
    {
        $rules = (new $this->validatorCls())->rules();
        // mutate the captured request to the desired method so rules() returns POST/PUT shape
        $ref = new \ReflectionClass($this->validatorCls);
        $v   = app($this->validatorCls);

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ApiException(
                'Validation failed: ' . implode('; ', $validator->errors()->all()),
                422,
                $validator->errors()->toArray(),
            );
        }
    }

    protected function normalizeOnCreate(array $data): array
    {
        // Derive visit_date from request or default to today
        if (empty($data['visit_date'])) {
            $data['visit_date'] = Carbon::now()->toDateString();
        }
        // Default status if absent
        if (empty($data['status'])) {
            $data['status'] = OpdVisitStatusEnum::WAITING;
        }
        return $data;
    }
}
