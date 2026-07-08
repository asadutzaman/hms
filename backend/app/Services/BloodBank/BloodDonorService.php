<?php

namespace App\Services\BloodBank;

use App\Models\BloodDonor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BloodDonorService
{
    /** Deferral window after a standard whole-blood donation (simplified — real policy varies by donation type). */
    protected const STANDARD_DEFERRAL_DAYS = 90;

    public function register(array $data, int $actorId): BloodDonor
    {
        return BloodDonor::query()->create([
            'donor_no'     => $this->generateDonorNo(now()->toDateString()),
            'name'         => $data['name'],
            'gender'       => $data['gender'] ?? null,
            'dob'          => $data['dob'] ?? null,
            'blood_group'  => $data['blood_group'],
            'phone'        => $data['phone'] ?? null,
            'address'      => $data['address'] ?? null,
            'created_by'   => $actorId,
        ]);
    }

    public function setDeferral(int $donorId, ?string $reason, ?string $untilDate, int $actorId): BloodDonor
    {
        $donor = BloodDonor::query()->findOrFail($donorId);
        $donor->is_deferred = !empty($reason);
        $donor->deferral_reason = $reason;
        $donor->deferral_until_date = $untilDate;
        $donor->updated_by = $actorId;
        $donor->save();
        return $donor->fresh();
    }

    /** Called after a successful donation — updates history + applies the standard deferral window. */
    public function markDonated(int $donorId, string $donationDate): void
    {
        DB::transaction(function () use ($donorId, $donationDate) {
            $donor = BloodDonor::query()->lockForUpdate()->findOrFail($donorId);
            $donor->last_donation_date = $donationDate;
            $donor->total_donations = $donor->total_donations + 1;
            $donor->is_deferred = true;
            $donor->deferral_reason = 'Standard post-donation deferral';
            $donor->deferral_until_date = Carbon::parse($donationDate)->addDays(self::STANDARD_DEFERRAL_DAYS)->toDateString();
            $donor->save();
        });
    }

    protected function generateDonorNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')->where('label', 'BLOOD_DONOR')->where('sequence_date', $dateYmd)->lockForUpdate()->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid' => (string) Str::uuid(), 'label' => 'BLOOD_DONOR', 'prefix' => 'DNR', 'separator' => '-',
                    'next_sequence' => 2, 'sequence_date' => $dateYmd, 'status' => 1, 'sort_order' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $seq = 1;
            } else {
                $seq = (int) $row->next_sequence;
                DB::table('code_sequences')->where('id', $row->id)->update(['next_sequence' => $seq + 1, 'updated_at' => now()]);
            }

            $datePart = Carbon::createFromFormat('Y-m-d', $dateYmd)->format('Ymd');
            return "DNR-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
