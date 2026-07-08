<?php

namespace App\Services\BloodBank;

use App\Exceptions\ApiException;
use App\Models\BloodDonation;
use App\Models\BloodUnit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * F-11-02 Blood Inventory & Screening. Shelf life is a simplified,
 * component-wise fixed number of days (real blood banking references vary
 * by anticoagulant/storage — this is a reasonable MVP default, not a
 * clinical protocol).
 */
class BloodUnitService
{
    protected const SHELF_LIFE_DAYS = [
        'whole_blood'    => 35,
        'prbc'           => 42,
        'ffp'            => 365,
        'platelets'      => 5,
        'cryoprecipitate' => 365,
    ];

    public function createUnit(int $donationId, array $data, int $actorId): BloodUnit
    {
        return DB::transaction(function () use ($donationId, $data, $actorId) {
            $donation = BloodDonation::query()->with('donor')->findOrFail($donationId);
            $componentType = $data['component_type'] ?? 'whole_blood';
            $shelfDays = self::SHELF_LIFE_DAYS[$componentType] ?? 35;

            return BloodUnit::query()->create([
                'bag_no'           => $this->generateBagNo(now()->toDateString()),
                'donation_id'      => $donationId,
                'component_type'   => $componentType,
                'blood_group'      => $donation->donor->blood_group,
                'collection_date'  => $donation->donation_date,
                'expiry_date'      => Carbon::parse($donation->donation_date)->addDays($shelfDays)->toDateString(),
                'created_by'       => $actorId,
            ]);
        });
    }

    public function recordScreening(int $unitId, array $screeningResults, int $actorId): BloodUnit
    {
        return DB::transaction(function () use ($unitId, $screeningResults, $actorId) {
            $unit = BloodUnit::query()->lockForUpdate()->findOrFail($unitId);

            $allNegative = collect($screeningResults)->every(fn ($v) => strtolower((string) $v) === 'negative');

            $unit->screening_results = $screeningResults;
            $unit->screening_status = $allNegative ? 'passed' : 'failed';
            $unit->unit_status = $allNegative ? 'available' : 'discarded';
            $unit->updated_by = $actorId;
            $unit->save();

            return $unit->fresh();
        });
    }

    public function reserve(int $unitId, int $actorId): BloodUnit
    {
        return DB::transaction(function () use ($unitId, $actorId) {
            $unit = BloodUnit::query()->lockForUpdate()->findOrFail($unitId);
            if ($unit->unit_status !== 'available') {
                throw new ApiException("Unit is not available (current status: {$unit->unit_status}).", 422);
            }
            $unit->unit_status = 'reserved';
            $unit->updated_by = $actorId;
            $unit->save();
            return $unit->fresh();
        });
    }

    protected function generateBagNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')->where('label', 'BLOOD_UNIT')->where('sequence_date', $dateYmd)->lockForUpdate()->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid' => (string) Str::uuid(), 'label' => 'BLOOD_UNIT', 'prefix' => 'BAG', 'separator' => '-',
                    'next_sequence' => 2, 'sequence_date' => $dateYmd, 'status' => 1, 'sort_order' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "BAG-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
