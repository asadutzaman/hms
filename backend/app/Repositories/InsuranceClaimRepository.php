<?php

namespace App\Repositories;

use App\Models\InsuranceClaim;
use App\Services\ODataService;

class InsuranceClaimRepository extends BaseRepository
{
    /**
    * @var InsuranceClaim
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['claim_no', 'policy_number'];

    public function __construct()
    {
        $this->model = new InsuranceClaim();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function withRelations(int $id): InsuranceClaim
    {
        return $this->newQuery()->with(['patient', 'insuranceCompany', 'insuranceScheme', 'preAuthorization'])->findOrFail($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['insuranceCompany'])
            ->where('patient_id', $patientId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function forBillable(string $billableType, int $billableId)
    {
        return $this->newQuery()
            ->with(['insuranceCompany'])
            ->where('billable_type', $billableType)
            ->where('billable_id', $billableId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * F-20-04 Claim Tracking & Status — status-bucket counts/amounts plus a
     * "days in current status" aging list for anything not yet settled or
     * rejected (terminal statuses). Status transitions here are always
     * manual entry (InsuranceClaimService::updateStatus) — no real insurer
     * portal to sync from exists, so "or manual entry" is the only half of
     * the acceptance criteria this app can actually satisfy.
     */
    public function trackingSummary(): array
    {
        $counts = $this->newQuery()
            ->selectRaw('claim_status, COUNT(*) as claim_count, COALESCE(SUM(claimed_amount), 0) as total_claimed, COALESCE(SUM(approved_amount), 0) as total_approved')
            ->groupBy('claim_status')
            ->get();

        $pending = $this->newQuery()
            ->with(['patient', 'insuranceCompany'])
            ->whereNotIn('claim_status', ['settled', 'rejected'])
            ->orderBy('submitted_at')
            ->get()
            ->map(function ($claim) {
                $referenceDate = $claim->submitted_at ?? $claim->created_at;
                $claim->days_in_status = $referenceDate ? now()->diffInDays($referenceDate) : null;
                return $claim;
            });

        return [
            'status_counts' => $counts,
            'pending_claims' => $pending,
        ];
    }
}
