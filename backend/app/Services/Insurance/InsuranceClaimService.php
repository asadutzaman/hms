<?php

namespace App\Services\Insurance;

use App\Enums\InsuranceClaimStatusEnum;
use App\Exceptions\ApiException;
use App\Models\InsuranceClaim;
use App\Models\IpdBill;
use App\Models\OpdBill;
use App\Repositories\InsuranceClaimRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsuranceClaimService
{
    protected InsuranceClaimRepository $repository;

    public function __construct(InsuranceClaimRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Raise a TPA/insurance claim (draft) against an existing OPD or IPD
     * bill — the "TPA bill formatted per insurer" acceptance criterion is
     * satisfied by rendering the bill + claim as a PDF (see
     * InsuranceClaimController::formPdf) rather than a per-insurer template
     * engine, which would be significant additional scope for this sprint.
     */
    public function createFromBill(array $data, int $actorId): InsuranceClaim
    {
        return DB::transaction(function () use ($data, $actorId) {
            $bill = $this->findBill($data['billable_type'], $data['billable_id']);

            $claim = InsuranceClaim::query()->create([
                'organogram_id'         => $bill->organogram_id,
                'claim_no'              => $this->generateClaimNo(now()->toDateString()),
                'patient_id'            => $data['patient_id'],
                'insurance_company_id'  => $data['insurance_company_id'],
                'insurance_scheme_id'   => $data['insurance_scheme_id'] ?? null,
                'pre_authorization_id'  => $data['pre_authorization_id'] ?? null,
                'policy_number'         => $data['policy_number'] ?? null,
                'billable_type'         => $data['billable_type'],
                'billable_id'           => $data['billable_id'],
                'claimed_amount'        => $data['claimed_amount'] ?? $bill->total,
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => $actorId,
            ]);

            return $claim->fresh(['patient', 'insuranceCompany', 'insuranceScheme']);
        });
    }

    public function submit(int $id, int $actorId): InsuranceClaim
    {
        return DB::transaction(function () use ($id, $actorId) {
            $claim = InsuranceClaim::query()->lockForUpdate()->findOrFail($id);

            if (!InsuranceClaimStatusEnum::canTransition($claim->claim_status, InsuranceClaimStatusEnum::SUBMITTED)) {
                throw new ApiException("Cannot submit from status '{$claim->claim_status}'.", 422);
            }

            $claim->claim_status = InsuranceClaimStatusEnum::SUBMITTED;
            $claim->submitted_by = $actorId;
            $claim->submitted_at = now();
            $claim->save();

            return $claim->fresh(['patient', 'insuranceCompany']);
        });
    }

    public function updateStatus(int $id, string $status, ?float $approvedAmount, ?string $notes): InsuranceClaim
    {
        return DB::transaction(function () use ($id, $status, $approvedAmount, $notes) {
            $claim = InsuranceClaim::query()->lockForUpdate()->findOrFail($id);

            if (!InsuranceClaimStatusEnum::canTransition($claim->claim_status, $status)) {
                throw new ApiException("Cannot transition from '{$claim->claim_status}' to '{$status}'.", 422);
            }

            $claim->claim_status = $status;
            if ($approvedAmount !== null) {
                $claim->approved_amount = round($approvedAmount, 2);
            }
            if ($notes !== null) {
                $claim->notes = $notes;
            }
            if ($status === InsuranceClaimStatusEnum::SETTLED) {
                $claim->settled_at = now();
            }
            $claim->save();

            return $claim->fresh(['patient', 'insuranceCompany']);
        });
    }

    protected function findBill(string $billableType, int $billableId)
    {
        return match ($billableType) {
            'opd_bill' => OpdBill::query()->findOrFail($billableId),
            'ipd_bill' => IpdBill::query()->findOrFail($billableId),
            default => throw new ApiException('Unknown billable type.', 422),
        };
    }

    /**
     * F-20-03 "Claim document bundle created; submitted per insurer format"
     * — satisfied by a single unified claim + bill PDF (not a per-insurer
     * template engine, out of scope for this sprint's SP budget — same
     * scope decision as Sprint 7's TPA billing PDF).
     */
    public function renderClaimPdf(int $claimId)
    {
        $claim = InsuranceClaim::query()
            ->with(['patient', 'insuranceCompany', 'insuranceScheme', 'preAuthorization'])
            ->find($claimId);

        if (!$claim) {
            throw new ApiException('Claim not found.', 404);
        }

        $bill = match ($claim->billable_type) {
            'opd_bill' => OpdBill::query()->with('items')->find($claim->billable_id),
            'ipd_bill' => IpdBill::query()->with('items')->find($claim->billable_id),
            default => null,
        };

        $pdf = Pdf::loadView('pdf.insurance_claim', ['claim' => $claim, 'bill' => $bill]);
        return $pdf->stream("claim-{$claim->claim_no}.pdf");
    }

    protected function generateClaimNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'INSURANCE_CLAIM')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'INSURANCE_CLAIM',
                    'prefix'        => 'CLM',
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
            return "CLM-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
