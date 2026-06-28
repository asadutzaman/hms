<?php

namespace App\Repositories;

use App\Enums\OpdVisitActionEnum;
use App\Enums\OpdVisitStatusEnum;
use App\Exceptions\ApiException;
use App\Models\OpdVisit;
use App\Models\OpdVisitAuditLog;
use App\Services\ODataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OpdVisitRepository extends BaseRepository
{
    /**
     * @var OpdVisit
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'opd_no',
        'token_number',
        'visit_type',
        'chief_complaint',
        'status',
    ];

    public function __construct()
    {
        $this->model = new OpdVisit();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /* ---------- Queries used by the workflow ---------- */

    public function todayQueue(?int $departmentId = null, ?int $doctorId = null)
    {
        $q = $this->newQuery()
            ->with(['patient', 'doctor', 'department'])
            ->whereDate('visit_date', now()->toDateString())
            ->whereIn('status', [
                OpdVisitStatusEnum::WAITING,
                OpdVisitStatusEnum::VITALS_TAKEN,
                OpdVisitStatusEnum::IN_CONSULTATION,
            ])
            ->orderBy('token_number')
            ->orderBy('id');

        if ($departmentId) {
            $q->where('department_id', $departmentId);
        }
        if ($doctorId) {
            $q->where('doctor_id', $doctorId);
        }

        return $q->get();
    }

    public function doctorActiveVisit(int $doctorId): ?OpdVisit
    {
        return $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->where('status', OpdVisitStatusEnum::IN_CONSULTATION)
            ->whereNull('deleted_at')
            ->first();
    }

    public function visitsForPatient(int $patientId, int $limit = 20)
    {
        return $this->newQuery()
            ->with(['doctor', 'department'])
            ->where('patient_id', $patientId)
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function withRelations(int $id): OpdVisit
    {
        return $this->newQuery()
            ->with([
                'patient',
                'appointment',
                'doctor',
                'department',
                'vitals',
                'diagnoses',
                'prescription.items',
                'investigationOrders.items',
                'bill.items',
                'bill.payments',
                'auditLogs.actor',
            ])
            ->findOrFail($id);
    }

    /* ---------- OPD No generation (per-day) ---------- */

    /**
     * Atomic per-day counter. Returns formatted OPD-YYYYMMDD-#### string.
     * Uses (label, sequence_date) unique row + SELECT FOR UPDATE to be safe under concurrency.
     */
    public function generateOpdNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'OPD_VISIT')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'label'         => 'OPD_VISIT',
                    'prefix'        => 'OPD',
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
            return "OPD-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public function nextTokenFor(string $dateYmd, int $departmentId): int
    {
        $max = $this->newQuery()
            ->whereDate('visit_date', $dateYmd)
            ->where('department_id', $departmentId)
            ->max('token_number');

        return ((int) $max) + 1;
    }

    /* ---------- State machine ---------- */

    /**
     * Transition a visit's status atomically + write an audit log row in the same transaction.
     * Enforces legal transitions via OpdVisitStatusEnum::canTransition().
     * Enforces "one active per doctor" via Postgres partial unique index (added in D0 migration).
     */
    public function transitionStatus(
        int $visitId,
        string $toStatus,
        int $actorId,
        ?string $remarks = null,
        array $meta = [],
        string $action = OpdVisitActionEnum::STATUS_CHANGE,
    ): OpdVisit {
        return DB::transaction(function () use ($visitId, $toStatus, $actorId, $remarks, $meta, $action) {
            $visit = $this->newQuery()->lockForUpdate()->findOrFail($visitId);

            $from = (string) $visit->status;

            if (!OpdVisitStatusEnum::canTransition($from, $toStatus)) {
                throw new ApiException(
                    "Illegal status transition: {$from} → {$toStatus}",
                    422,
                );
            }

            $visit->status = $toStatus;

            // Side-effect timestamps per status.
            switch ($toStatus) {
                case OpdVisitStatusEnum::IN_CONSULTATION:
                    if (empty($visit->consultation_start_at)) {
                        $visit->consultation_start_at = now();
                    }
                    break;
                case OpdVisitStatusEnum::COMPLETED:
                    $visit->consultation_end_at = now();
                    break;
                case OpdVisitStatusEnum::CLOSED:
                    $visit->closed_at = now();
                    $visit->closed_by = $actorId;
                    break;
                case OpdVisitStatusEnum::CANCELLED:
                    $visit->cancelled_at = now();
                    break;
            }

            $visit->save();

            $this->logAudit($visit, $action, $from, $toStatus, $actorId, $remarks, $meta);

            return $visit->fresh();
        });
    }

    public function cancelVisit(int $visitId, int $actorId, string $reason): OpdVisit
    {
        $visit = $this->newQuery()->findOrFail($visitId);
        $visit->cancellation_reason = $reason;
        $visit->save();

        return $this->transitionStatus(
            $visitId,
            OpdVisitStatusEnum::CANCELLED,
            $actorId,
            $reason,
            ['reason' => $reason],
            OpdVisitActionEnum::CANCEL,
        );
    }

    /* ---------- Audit logging ---------- */

    public function logAudit(
        OpdVisit $visit,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        int $actorId,
        ?string $remarks = null,
        array $meta = [],
    ): OpdVisitAuditLog {
        return OpdVisitAuditLog::query()->create([
            'organogram_id' => $visit->organogram_id,
            'opd_visit_id'  => $visit->id,
            'actor_id'      => $actorId,
            'action'        => $action,
            'from_status'   => $fromStatus,
            'to_status'     => $toStatus,
            'remarks'       => $remarks,
            'payload'       => $meta, // migration column is `payload`; model param is `meta`
            'occurred_at'   => now(),
        ]);
    }

    /* ---------- One-active-per-doctor guard ---------- */

    /**
     * Application-level guard. The Postgres partial unique index in D0 migration is the
     * primary enforcement; this is the fallback for nicer 409 messages.
     */
    public function assertDoctorIsFree(int $doctorId, ?int $exceptVisitId = null): void
    {
        $q = $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->where('status', OpdVisitStatusEnum::IN_CONSULTATION)
            ->whereNull('deleted_at');

        if ($exceptVisitId) {
            $q->where('id', '!=', $exceptVisitId);
        }

        if ($q->exists()) {
            throw new ApiException(
                "Doctor {$doctorId} already has an in_consultation visit.",
                409,
            );
        }
    }

    /* ---------- Reports ---------- */

    public function dailyCount(string $dateYmd): int
    {
        return $this->newQuery()
            ->whereDate('visit_date', $dateYmd)
            ->whereNull('deleted_at')
            ->count();
    }

    public function doctorLoadForDate(string $dateYmd): array
    {
        return $this->newQuery()
            ->selectRaw('doctor_id, COUNT(*) as total')
            ->whereDate('visit_date', $dateYmd)
            ->whereNull('deleted_at')
            ->groupBy('doctor_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['doctor_id' => (int) $r->doctor_id, 'total' => (int) $r->total])
            ->all();
    }

    public function pendingBills()
    {
        return $this->newQuery()
            ->with(['patient', 'doctor', 'department', 'bill'])
            ->where('status', OpdVisitStatusEnum::COMPLETED)
            ->whereDoesntHave('bill', function ($q) {
                $q->where('status', 'paid');
            })
            ->whereNull('deleted_at')
            ->orderByDesc('consultation_end_at')
            ->limit(100)
            ->get();
    }

    public function bootLifecycle(int $visitId, int $actorId): OpdVisit
    {
        $visit = $this->newQuery()->findOrFail($visitId);

        if ($visit->status !== OpdVisitStatusEnum::WAITING) {
            throw new RuntimeException(
                "Cannot start consultation: visit status is {$visit->status}, expected waiting.",
            );
        }

        return $this->transitionStatus(
            $visitId,
            OpdVisitStatusEnum::IN_CONSULTATION,
            $actorId,
            null,
            [],
            OpdVisitActionEnum::STATUS_CHANGE,
        );
    }

    public function getNextTokenNumber(int $doctorId, string $dateYmd): int
    {
        $max = $this->newQuery()
            ->whereDate('visit_date', $dateYmd)
            ->where('doctor_id', $doctorId)
            ->max('token_number');

        return ((int) $max) + 1;
    }

    public function getByDate(string $dateYmd)
    {
        return $this->newQuery()
            ->with(['patient', 'doctor', 'department'])
            ->whereDate('visit_date', $dateYmd)
            ->orderBy('token_number')
            ->get();
    }

    public function getAuditLogs(int $visitId)
    {
        return OpdVisitAuditLog::query()
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('created_at')
            ->get();
    }
}
