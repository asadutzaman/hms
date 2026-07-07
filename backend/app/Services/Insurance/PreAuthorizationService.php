<?php

namespace App\Services\Insurance;

use App\Enums\PreAuthorizationStatusEnum;
use App\Exceptions\ApiException;
use App\Models\PreAuthorization;
use App\Repositories\PreAuthorizationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PreAuthorizationService
{
    protected PreAuthorizationRepository $repository;

    public function __construct(PreAuthorizationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function submit(array $data, int $actorId): PreAuthorization
    {
        return DB::transaction(function () use ($data, $actorId) {
            $pa = PreAuthorization::query()->create([
                'pa_no'                 => $this->generatePaNo(now()->toDateString()),
                'patient_id'            => $data['patient_id'],
                'ipd_admission_id'      => $data['ipd_admission_id'] ?? null,
                'opd_visit_id'          => $data['opd_visit_id'] ?? null,
                'insurance_company_id'  => $data['insurance_company_id'],
                'insurance_scheme_id'   => $data['insurance_scheme_id'] ?? null,
                'policy_number'         => $data['policy_number'] ?? null,
                'estimated_amount'      => $data['estimated_amount'],
                'diagnosis'             => $data['diagnosis'] ?? null,
                'treatment_plan'        => $data['treatment_plan'] ?? null,
                'requested_by'          => $actorId,
                'requested_at'          => now(),
            ]);

            return $pa->fresh(['patient', 'insuranceCompany', 'insuranceScheme']);
        });
    }

    public function markUnderReview(int $id, int $actorId): PreAuthorization
    {
        return $this->transition($id, PreAuthorizationStatusEnum::UNDER_REVIEW, $actorId);
    }

    public function approve(int $id, int $actorId, float $approvedAmount, ?string $notes): PreAuthorization
    {
        return DB::transaction(function () use ($id, $actorId, $approvedAmount, $notes) {
            $pa = PreAuthorization::query()->lockForUpdate()->findOrFail($id);

            if (!PreAuthorizationStatusEnum::canTransition($pa->pa_status, PreAuthorizationStatusEnum::APPROVED)) {
                throw new ApiException("Cannot approve from status '{$pa->pa_status}'.", 422);
            }

            $pa->pa_status = PreAuthorizationStatusEnum::APPROVED;
            $pa->approved_amount = round($approvedAmount, 2);
            $pa->response_notes = $notes;
            $pa->responded_at = now();
            $pa->responded_by = $actorId;
            $pa->save();

            return $pa->fresh(['patient', 'insuranceCompany', 'insuranceScheme']);
        });
    }

    public function reject(int $id, int $actorId, string $notes): PreAuthorization
    {
        return DB::transaction(function () use ($id, $actorId, $notes) {
            $pa = PreAuthorization::query()->lockForUpdate()->findOrFail($id);

            if (!PreAuthorizationStatusEnum::canTransition($pa->pa_status, PreAuthorizationStatusEnum::REJECTED)) {
                throw new ApiException("Cannot reject from status '{$pa->pa_status}'.", 422);
            }

            $pa->pa_status = PreAuthorizationStatusEnum::REJECTED;
            $pa->response_notes = $notes;
            $pa->responded_at = now();
            $pa->responded_by = $actorId;
            $pa->save();

            return $pa->fresh(['patient', 'insuranceCompany', 'insuranceScheme']);
        });
    }

    protected function transition(int $id, string $to, int $actorId): PreAuthorization
    {
        return DB::transaction(function () use ($id, $to, $actorId) {
            $pa = PreAuthorization::query()->lockForUpdate()->findOrFail($id);

            if (!PreAuthorizationStatusEnum::canTransition($pa->pa_status, $to)) {
                throw new ApiException("Cannot transition from '{$pa->pa_status}' to '{$to}'.", 422);
            }

            $pa->pa_status = $to;
            $pa->save();

            return $pa->fresh(['patient', 'insuranceCompany', 'insuranceScheme']);
        });
    }

    protected function generatePaNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'PRE_AUTH')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'PRE_AUTH',
                    'prefix'        => 'PA',
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
                    ->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "PA-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
