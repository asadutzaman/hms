<?php

namespace App\Repositories;

use App\Enums\IpdAdmissionActionEnum;
use App\Enums\IpdAdmissionStatusEnum;
use App\Exceptions\ApiException;
use App\Models\Bed;
use App\Models\IpdAdmission;
use App\Models\IpdAdmissionAuditLog;
use App\Services\ODataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpdAdmissionRepository extends BaseRepository
{
    /**
    * @var IpdAdmission
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['admission_no', 'admission_type', 'diagnosis_at_admission'];

    public function __construct()
    {
        $this->model = new IpdAdmission();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): IpdAdmission
    {
        return $this->newQuery()
            ->with([
                'patient',
                'opdVisit',
                'attendingDoctor',
                'department',
                'ward',
                'bed',
                'branch',
                'bill.items',
                'bill.payments',
                'advancePayments',
                'auditLogs.actor',
            ])
            ->findOrFail($id);
    }

    /* ---------- Admission ---------- */

    /**
     * Admit a patient to a bed. Locks the target bed row first (before the
     * free-bed check) — necessary to serialize two concurrent admissions
     * racing for the same bed; the partial unique index on
     * ipd_admissions(bed_id) is the last-resort DB guarantee, this gives a
     * clean 409 instead of a raw constraint-violation exception.
     */
    public function admit(array $data, int $actorId): IpdAdmission
    {
        return DB::transaction(function () use ($data, $actorId) {
            $bedId = (int) $data['bed_id'];
            $bed = Bed::query()->lockForUpdate()->findOrFail($bedId);

            $this->assertBedIsFree($bedId);

            $ward = $bed->ward;
            $admissionDate = $data['admission_date'] ?? now();
            $dateYmd = Carbon::parse($admissionDate)->toDateString();

            $admission = $this->create(array_merge($data, [
                'admission_no'      => $this->generateAdmissionNo($dateYmd),
                'bed_id'            => $bed->id,
                'ward_id'           => $bed->ward_id,
                'branch_id'         => optional($ward)->branch_id,
                'admission_date'    => $admissionDate,
                'admission_status'  => IpdAdmissionStatusEnum::ADMITTED,
                'admitted_by'       => $actorId,
                'created_by'        => $actorId,
                'updated_by'        => $actorId,
            ]));

            $bed->bed_status = 'occupied';
            $bed->save();

            $this->logAudit(
                $admission,
                IpdAdmissionActionEnum::ADMIT,
                null,
                IpdAdmissionStatusEnum::ADMITTED,
                $actorId,
                'Patient admitted',
                [
                    'bed_id'     => $bed->id,
                    'ward_id'    => $bed->ward_id,
                    'daily_rate' => (string) $bed->daily_rate,
                ],
            );

            // Every admission gets an (empty) bill shell up front so billing
            // never has to be "generated" separately — room charges accrue
            // onto it via IpdBillService::refreshRoomCharges().
            app(\App\Services\Ipd\IpdBillService::class)->generate($admission->id, $actorId);

            return $admission->fresh();
        });
    }

    /**
     * Move an admitted patient to a different bed. Does not change
     * admission_status. Locks the target bed row first (same reasoning as
     * admit()), then the current bed (to safely flip it back to vacant).
     */
    public function transferBed(int $admissionId, int $toBedId, int $actorId, ?string $reason = null): IpdAdmission
    {
        return DB::transaction(function () use ($admissionId, $toBedId, $actorId, $reason) {
            $admission = $this->newQuery()->lockForUpdate()->findOrFail($admissionId);

            if ($admission->admission_status !== IpdAdmissionStatusEnum::ADMITTED) {
                throw new ApiException(
                    "Cannot transfer bed for an admission in state '{$admission->admission_status}'.",
                    422,
                );
            }

            $toBed = Bed::query()->lockForUpdate()->findOrFail($toBedId);

            if ($toBed->id === $admission->bed_id) {
                throw new ApiException('Patient is already assigned to this bed.', 422);
            }

            $this->assertBedIsFree($toBedId);

            $fromBedId = $admission->bed_id;
            $fromWardId = $admission->ward_id;
            $fromBed = Bed::query()->lockForUpdate()->find($fromBedId);

            $admission->bed_id = $toBed->id;
            $admission->ward_id = $toBed->ward_id;
            $admission->branch_id = optional($toBed->ward)->branch_id;
            $admission->updated_by = $actorId;
            $admission->save();

            if ($fromBed) {
                $fromBed->bed_status = 'vacant';
                $fromBed->save();
            }

            $toBed->bed_status = 'occupied';
            $toBed->save();

            $this->logAudit(
                $admission,
                IpdAdmissionActionEnum::BED_TRANSFER,
                null,
                null,
                $actorId,
                $reason ?? 'Bed transferred',
                [
                    'from_bed_id'  => $fromBedId,
                    'from_ward_id' => $fromWardId,
                    'to_bed_id'    => $toBed->id,
                    'to_ward_id'   => $toBed->ward_id,
                    'daily_rate'   => (string) $toBed->daily_rate,
                    'reason'       => $reason,
                ],
            );

            return $admission->fresh();
        });
    }

    /**
     * Low-level status-ending transition (discharge/dama/deceased). Does NOT
     * enforce the billing gate — that's layered on top by IpdAdmissionService
     * (which also needs IpdBillService), to keep this repository free of a
     * dependency on the billing domain.
     */
    public function transitionStatus(
        int $admissionId,
        string $toStatus,
        int $actorId,
        ?string $remarks = null,
        array $meta = [],
        string $action = IpdAdmissionActionEnum::DISCHARGE,
    ): IpdAdmission {
        return DB::transaction(function () use ($admissionId, $toStatus, $actorId, $remarks, $meta, $action) {
            $admission = $this->newQuery()->lockForUpdate()->findOrFail($admissionId);

            $from = (string) $admission->admission_status;

            if (!IpdAdmissionStatusEnum::canTransition($from, $toStatus)) {
                throw new ApiException(
                    "Illegal admission status transition: {$from} → {$toStatus}",
                    422,
                );
            }

            $admission->admission_status = $toStatus;
            $admission->discharge_date = now();
            $admission->discharged_by = $actorId;
            $admission->updated_by = $actorId;
            if (!empty($meta['discharge_override_reason'])) {
                $admission->discharge_override_reason = $meta['discharge_override_reason'];
            }
            $admission->save();

            $bed = Bed::query()->lockForUpdate()->find($admission->bed_id);
            if ($bed) {
                $bed->bed_status = 'vacant';
                $bed->save();
            }

            $this->logAudit($admission, $action, $from, $toStatus, $actorId, $remarks, $meta);

            return $admission->fresh();
        });
    }

    /* ---------- Admission number generation (per-day) ---------- */

    public function generateAdmissionNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'IPD_ADMISSION')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'IPD_ADMISSION',
                    'prefix'        => 'ADM',
                    'separator'     => '-',
                    'next_sequence' => 2,
                    'sequence_date' => $dateYmd,
                    'status'        => 1,
                    'sort_order'    => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')
                    ->where('id', $row->id)
                    ->update([
                        'next_sequence' => $seq + 1,
                        'updated_at'    => now(),
                    ]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "ADM-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    /* ---------- One-active-admission-per-bed guard ---------- */

    /**
     * Application-level guard. The Postgres partial unique index
     * (idx_ipd_admissions_one_active_per_bed) is the last-resort DB
     * guarantee; this gives a clean 409 instead of a raw constraint
     * violation. Caller must lock the target bed row BEFORE calling this
     * (see admit()/transferBed()) so two concurrent requests targeting the
     * same bed are serialized rather than both passing this check.
     */
    public function assertBedIsFree(int $bedId, ?int $exceptAdmissionId = null): void
    {
        $q = $this->newQuery()
            ->where('bed_id', $bedId)
            ->where('admission_status', IpdAdmissionStatusEnum::ADMITTED)
            ->whereNull('deleted_at');

        if ($exceptAdmissionId) {
            $q->where('id', '!=', $exceptAdmissionId);
        }

        if ($q->exists()) {
            throw new ApiException("Bed {$bedId} is already occupied by an active admission.", 409);
        }
    }

    /* ---------- Audit logging ---------- */

    public function logAudit(
        IpdAdmission $admission,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        int $actorId,
        ?string $remarks = null,
        array $meta = [],
    ): IpdAdmissionAuditLog {
        return IpdAdmissionAuditLog::query()->create([
            'organogram_id'    => $admission->organogram_id,
            'ipd_admission_id' => $admission->id,
            'actor_id'         => $actorId,
            'action'           => $action,
            'from_status'      => $fromStatus,
            'to_status'        => $toStatus,
            'remarks'          => $remarks,
            'payload'          => $meta,
            'occurred_at'      => now(),
        ]);
    }

    public function getAuditLogs(int $admissionId)
    {
        return IpdAdmissionAuditLog::query()
            ->where('ipd_admission_id', $admissionId)
            ->orderByDesc('created_at')
            ->get();
    }
}
