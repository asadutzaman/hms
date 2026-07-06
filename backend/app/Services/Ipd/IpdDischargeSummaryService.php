<?php

namespace App\Services\Ipd;

use App\Enums\IpdMedicationOrderStatusEnum;
use App\Exceptions\ApiException;
use App\Models\IpdAdmission;
use App\Models\IpdDischargeSummary;
use App\Models\IpdMedicationOrder;
use App\Repositories\IpdDischargeSummaryRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IpdDischargeSummaryService
{
    protected IpdDischargeSummaryRepository $repository;

    public function __construct(IpdDischargeSummaryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate (or return the existing) draft, auto-filled from the
     * admission's diagnosis and its currently-active medication orders —
     * the doctor edits/completes the remaining fields and signs it off.
     */
    public function generateDraft(int $admissionId): IpdDischargeSummary
    {
        return DB::transaction(function () use ($admissionId) {
            $existing = $this->repository->forAdmission($admissionId);
            if ($existing) {
                return $existing;
            }

            $admission = IpdAdmission::query()->findOrFail($admissionId);

            $activeMeds = IpdMedicationOrder::query()
                ->where('admission_id', $admissionId)
                ->where('order_status', IpdMedicationOrderStatusEnum::ACTIVE)
                ->get(['drug_name', 'strength', 'dose_value', 'dose_unit', 'frequency', 'route'])
                ->toArray();

            return IpdDischargeSummary::query()->create([
                'organogram_id'       => $admission->organogram_id,
                'admission_id'        => $admissionId,
                'summary_no'          => $this->generateSummaryNo(Carbon::now()->toDateString()),
                'admission_diagnosis' => $admission->diagnosis_at_admission,
                'discharge_medications' => $activeMeds,
            ]);
        });
    }

    public function update(int $id, array $data): IpdDischargeSummary
    {
        $summary = $this->repository->show($id);
        $this->assertNotFinalized($summary);

        $summary->fill($data);
        $summary->save();

        return $summary->fresh();
    }

    public function sign(int $id, int $actorId): IpdDischargeSummary
    {
        $summary = $this->repository->show($id);
        $this->assertNotFinalized($summary);

        if (empty($summary->discharge_diagnosis)) {
            throw new ApiException('Discharge diagnosis is required before signing.', 422);
        }

        $summary->is_finalized = true;
        $summary->signed_by = $actorId;
        $summary->signed_at = now();
        $summary->save();

        return $summary->fresh();
    }

    protected function assertNotFinalized(IpdDischargeSummary $summary): void
    {
        if ($summary->is_finalized) {
            throw new ApiException("Discharge summary {$summary->summary_no} is already signed and cannot be edited.", 422);
        }
    }

    protected function generateSummaryNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'IPD_DISCHARGE_SUMMARY')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'IPD_DISCHARGE_SUMMARY',
                    'prefix'        => 'DS',
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
            return "DS-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
