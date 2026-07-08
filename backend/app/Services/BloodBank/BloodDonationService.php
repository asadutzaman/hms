<?php

namespace App\Services\BloodBank;

use App\Exceptions\ApiException;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BloodDonationService
{
    protected BloodDonorService $donorService;
    protected BloodUnitService $unitService;

    public function __construct(BloodDonorService $donorService, BloodUnitService $unitService)
    {
        $this->donorService = $donorService;
        $this->unitService = $unitService;
    }

    /**
     * Records the donation event and immediately creates the default
     * whole-blood unit from it (a donation is, at minimum, one whole-blood
     * bag — further component separation, if needed, uses
     * BloodUnitService::createUnit() directly against the same
     * donation_id). Also flags the donor deferred for the standard window.
     */
    public function recordDonation(array $data, int $actorId): BloodDonation
    {
        return DB::transaction(function () use ($data, $actorId) {
            $donor = BloodDonor::query()->lockForUpdate()->findOrFail($data['donor_id']);

            if ($donor->is_deferred && $donor->deferral_until_date && Carbon::parse($donor->deferral_until_date)->isFuture()) {
                throw new ApiException("This donor is deferred until {$donor->deferral_until_date->toDateString()}.", 422);
            }

            $donationDate = $data['donation_date'] ?? now()->toDateString();

            $donation = BloodDonation::query()->create([
                'donation_no'      => $this->generateDonationNo(now()->toDateString()),
                'donor_id'         => $donor->id,
                'donation_date'    => $donationDate,
                'volume_ml'        => $data['volume_ml'] ?? 450,
                'hemoglobin_g_dl'  => $data['hemoglobin_g_dl'] ?? null,
                'collected_by'     => $data['collected_by'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $actorId,
            ]);

            $this->unitService->createUnit($donation->id, ['component_type' => 'whole_blood'], $actorId);
            $this->donorService->markDonated($donor->id, $donationDate);

            return $donation->fresh(['donor', 'units']);
        });
    }

    protected function generateDonationNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')->where('label', 'BLOOD_DONATION')->where('sequence_date', $dateYmd)->lockForUpdate()->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid' => (string) Str::uuid(), 'label' => 'BLOOD_DONATION', 'prefix' => 'DON', 'separator' => '-',
                    'next_sequence' => 2, 'sequence_date' => $dateYmd, 'status' => 1, 'sort_order' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "DON-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
