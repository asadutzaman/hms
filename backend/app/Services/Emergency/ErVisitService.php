<?php

namespace App\Services\Emergency;

use App\Enums\ErVisitStatusEnum;
use App\Exceptions\ApiException;
use App\Models\ErVisit;
use App\Repositories\ErVisitRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ErVisitService
{
    protected ErVisitRepository $repository;

    public function __construct(ErVisitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(array $data, int $actorId): ErVisit
    {
        return DB::transaction(function () use ($data, $actorId) {
            $arrivalAt = $data['arrival_at'] ?? now();
            $dateYmd = Carbon::parse($arrivalAt)->toDateString();

            return ErVisit::query()->create(array_merge($data, [
                'er_visit_no'   => $this->generateVisitNo($dateYmd),
                'arrival_at'    => $arrivalAt,
                'er_status'     => ErVisitStatusEnum::WAITING_TRIAGE,
                'registered_by' => $actorId,
            ]));
        });
    }

    /**
     * Move the ER visit to a terminal disposition (discharged/lwbs/deceased).
     * Admitting a patient is handled by IpdAdmissionRepository::admit()
     * itself, which calls linkAdmission() below rather than routing through
     * here — the admission is the source of truth for that transition.
     */
    public function dispose(int $erVisitId, string $disposition): ErVisit
    {
        return DB::transaction(function () use ($erVisitId, $disposition) {
            $visit = ErVisit::query()->lockForUpdate()->findOrFail($erVisitId);

            $statusMap = [
                'discharged' => ErVisitStatusEnum::DISCHARGED,
                'lwbs'       => ErVisitStatusEnum::LWBS,
                'deceased'   => ErVisitStatusEnum::DECEASED,
            ];
            $toStatus = $statusMap[$disposition] ?? null;
            if (!$toStatus) {
                throw new ApiException("Invalid disposition '{$disposition}'.", 422);
            }
            if (ErVisitStatusEnum::isTerminal($visit->er_status)) {
                throw new ApiException("ER visit is already at a terminal status ({$visit->er_status}).", 422);
            }

            $visit->er_status = $toStatus;
            $visit->disposition = $disposition;
            $visit->disposed_at = now();
            $visit->save();

            return $visit->fresh();
        });
    }

    /** Doctor picks up a triaged patient — moves the board status forward. */
    public function startTreatment(int $erVisitId): ErVisit
    {
        $visit = ErVisit::query()->lockForUpdate()->findOrFail($erVisitId);

        if (!ErVisitStatusEnum::canTransition($visit->er_status, ErVisitStatusEnum::IN_TREATMENT)) {
            throw new ApiException("Cannot start treatment from status '{$visit->er_status}'.", 422);
        }

        $visit->er_status = ErVisitStatusEnum::IN_TREATMENT;
        $visit->save();

        return $visit->fresh();
    }

    public function linkAdmission(int $erVisitId, int $admissionId): void
    {
        $visit = ErVisit::query()->find($erVisitId);
        if (!$visit || ErVisitStatusEnum::isTerminal($visit->er_status)) {
            return;
        }

        $visit->er_status = ErVisitStatusEnum::ADMITTED;
        $visit->disposition = 'admitted';
        $visit->linked_admission_id = $admissionId;
        $visit->disposed_at = now();
        $visit->save();
    }

    protected function generateVisitNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'ER_VISIT')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'ER_VISIT',
                    'prefix'        => 'ER',
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
            return "ER-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
