<?php

namespace App\Services\Billing;

use App\Exceptions\ApiException;
use App\Models\BillingPackage;
use App\Models\IpdBill;
use App\Models\IpdBillItem;
use App\Models\OpdBill;
use App\Models\OpdBillItem;
use Illuminate\Support\Facades\DB;

class PackageBillingService
{
    /**
     * Apply a fixed-price package to an existing OPD/IPD bill — adds a
     * single 'package' bill-item line at the package's fixed_price (the
     * package's inclusions are informational only, shown from the
     * BillingPackage->items relation on the frontend; they are NOT
     * individually billed, since the package price is fixed regardless of
     * what's inside — avoids double-charging or reconciling per-inclusion
     * notional prices against the fixed total).
     */
    public function applyToOpdBill(int $opdBillId, int $packageId): OpdBill
    {
        return DB::transaction(function () use ($opdBillId, $packageId) {
            $bill = OpdBill::query()->lockForUpdate()->findOrFail($opdBillId);
            $package = BillingPackage::query()->findOrFail($packageId);

            if ($bill->billing_package_id) {
                throw new ApiException('A package has already been applied to this bill.', 422);
            }
            if (!in_array($package->package_type, ['opd', 'both'], true)) {
                throw new ApiException('This package is not available for OPD billing.', 422);
            }

            $nextSequence = (int) OpdBillItem::query()->where('opd_bill_id', $bill->id)->max('sequence') + 1;

            OpdBillItem::query()->forceCreate([
                'organogram_id' => $bill->organogram_id,
                'opd_bill_id'   => $bill->id,
                'item_type'     => 'package',
                'description'   => "Package: {$package->name}",
                'source_type'   => BillingPackage::class,
                'source_id'     => $package->id,
                'quantity'      => 1,
                'unit_price'    => $package->fixed_price,
                'line_total'    => $package->fixed_price,
                'sequence'      => $nextSequence,
            ]);

            $bill->billing_package_id = $package->id;
            $bill->subtotal = round($bill->subtotal + $package->fixed_price, 2);
            $bill->total = max(0.0, round($bill->subtotal - $bill->discount + $bill->tax, 2));
            $bill->balance = max(0.0, round($bill->total - $bill->paid, 2));
            $bill->save();

            return $bill->fresh(['items', 'billingPackage']);
        });
    }

    public function applyToIpdBill(int $ipdBillId, int $packageId): IpdBill
    {
        return DB::transaction(function () use ($ipdBillId, $packageId) {
            $bill = IpdBill::query()->lockForUpdate()->findOrFail($ipdBillId);
            $package = BillingPackage::query()->findOrFail($packageId);

            if ($bill->billing_package_id) {
                throw new ApiException('A package has already been applied to this bill.', 422);
            }
            if (!in_array($package->package_type, ['ipd', 'both'], true)) {
                throw new ApiException('This package is not available for IPD billing.', 422);
            }

            $nextSequence = (int) IpdBillItem::query()->where('ipd_bill_id', $bill->id)->max('sequence') + 1;

            IpdBillItem::query()->create([
                'organogram_id' => $bill->organogram_id,
                'ipd_bill_id'   => $bill->id,
                'item_type'     => 'package',
                'description'   => "Package: {$package->name}",
                'source_type'   => BillingPackage::class,
                'source_id'     => $package->id,
                'quantity'      => 1,
                'unit_price'    => $package->fixed_price,
                'line_total'    => $package->fixed_price,
                'sequence'      => $nextSequence,
            ]);

            $bill->billing_package_id = $package->id;
            $bill->subtotal = round($bill->subtotal + $package->fixed_price, 2);
            $bill->total = max(0.0, round($bill->subtotal - $bill->discount + $bill->tax, 2));
            $bill->balance = max(0.0, round($bill->total - $bill->paid, 2));
            $bill->save();

            return $bill->fresh(['items', 'billingPackage']);
        });
    }
}
