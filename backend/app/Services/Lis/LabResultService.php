<?php

namespace App\Services\Lis;

use App\Enums\LabOrderItemStatusEnum;
use App\Enums\LabOrderStatusEnum;
use App\Enums\LabResultFlagEnum;
use App\Exceptions\ApiException;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabResult;
use App\Models\LabTestReferenceRange;
use App\Services\Notification\NotificationService;
use App\Repositories\LabOrderItemRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LabResultService
{
    protected LabOrderItemRepository $orderItemRepository;

    public function __construct(LabOrderItemRepository $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Technician entry — one call saves every parameter's result for the
     * test (e.g. all 5 CBC analytes at once). Each value is flagged against
     * the reference range matching the patient's current age/gender. Any
     * critical flag fires an immediate alert to the ordering doctor —
     * deliberately at entry time, not verification time, since patient
     * safety shouldn't wait on the pathologist's sign-off (F-05-08).
     */
    public function enterResults(int $orderItemId, array $results, int $actorId): LabOrderItem
    {
        return DB::transaction(function () use ($orderItemId, $results, $actorId) {
            $item = LabOrderItem::query()->with(['order.patient', 'labTest.parameters.referenceRanges'])->lockForUpdate()->findOrFail($orderItemId);

            if ($item->item_status === LabOrderItemStatusEnum::CANCELLED) {
                throw new ApiException('Cannot enter results for a cancelled test.', 422);
            }
            if ($item->item_status === LabOrderItemStatusEnum::VERIFIED) {
                throw new ApiException('This result is already verified — amendments are not supported yet.', 422);
            }

            $patient = $item->order->patient;
            $age = $patient && $patient->date_of_birth ? Carbon::parse($patient->date_of_birth)->age : null;
            $gender = $patient->gender ?? null;

            $anyCritical = false;

            foreach ($results as $row) {
                $parameter = $item->labTest
                    ? $item->labTest->parameters->firstWhere('id', (int) $row['lab_test_parameter_id'])
                    : null;

                $range = $parameter ? $this->matchReferenceRange($parameter->referenceRanges, $age, $gender) : null;
                $flag = $this->computeFlag($row['result_value'] ?? null, $parameter, $range);
                if (LabResultFlagEnum::isCritical($flag)) {
                    $anyCritical = true;
                }

                LabResult::query()->updateOrCreate(
                    ['lab_order_item_id' => $orderItemId, 'lab_test_parameter_id' => $row['lab_test_parameter_id'] ?? null],
                    [
                        'organogram_id'           => $item->organogram_id,
                        'parameter_name_snapshot' => $parameter->parameter_name ?? ($row['parameter_name'] ?? 'Result'),
                        'unit_snapshot'           => $parameter->unit ?? null,
                        'result_value'            => $row['result_value'] ?? null,
                        'result_flag'             => $flag,
                        'reference_range_display' => $this->rangeDisplay($range),
                        'verification_status'     => 'pending',
                        'entered_by'              => $actorId,
                        'entered_at'              => now(),
                        'remarks'                 => $row['remarks'] ?? null,
                    ],
                );
            }

            $item->item_status = LabOrderItemStatusEnum::ENTERED;
            $item->save();

            if ($anyCritical) {
                $this->alertCriticalValue($item);
            }

            return $this->orderItemRepository->withResults($orderItemId);
        });
    }

    /** Pathologist dual sign-off — verifies every entered result on the item. */
    public function verifyResults(int $orderItemId, int $actorId): LabOrderItem
    {
        return DB::transaction(function () use ($orderItemId, $actorId) {
            $item = LabOrderItem::query()->with('results')->lockForUpdate()->findOrFail($orderItemId);

            if ($item->item_status !== LabOrderItemStatusEnum::ENTERED) {
                throw new ApiException('Only entered (unverified) results can be verified.', 422);
            }
            if ($item->results->isEmpty()) {
                throw new ApiException('No results have been entered for this test yet.', 422);
            }

            LabResult::query()->where('lab_order_item_id', $orderItemId)->update([
                'verification_status' => 'verified',
                'verified_by'         => $actorId,
                'verified_at'         => now(),
            ]);

            $item->item_status = LabOrderItemStatusEnum::VERIFIED;
            $item->save();

            $this->maybeAdvanceOrder($item->lab_order_id);

            return $this->orderItemRepository->withResults($orderItemId);
        });
    }

    /** Once every item on the order is verified, the order itself is verified (ready to report). */
    protected function maybeAdvanceOrder(int $orderId): void
    {
        $order = LabOrder::query()->with('items')->lockForUpdate()->find($orderId);
        if (!$order) {
            return;
        }

        $relevant = $order->items->whereNotIn('item_status', [LabOrderItemStatusEnum::CANCELLED]);
        if ($relevant->isNotEmpty() && $relevant->every(fn ($i) => $i->item_status === LabOrderItemStatusEnum::VERIFIED)) {
            if (LabOrderStatusEnum::canTransition($order->order_status, LabOrderStatusEnum::VERIFIED)) {
                $order->order_status = LabOrderStatusEnum::VERIFIED;
                $order->save();
            }
        }
    }

    protected function matchReferenceRange($ranges, ?int $age, ?string $gender): ?LabTestReferenceRange
    {
        if (!$ranges || $ranges->isEmpty()) {
            return null;
        }

        $candidates = $ranges->filter(function ($r) use ($age) {
            if ($age === null) {
                return true;
            }
            return $age >= $r->age_min_years && ($r->age_max_years === null || $age <= $r->age_max_years);
        });

        if ($gender) {
            $genderMatch = $candidates->firstWhere('gender', $gender);
            if ($genderMatch) {
                return $genderMatch;
            }
        }

        return $candidates->firstWhere('gender', 'all') ?? $candidates->first();
    }

    protected function computeFlag(?string $value, $parameter, ?LabTestReferenceRange $range): ?string
    {
        if ($value === null || $value === '' || !$parameter || $parameter->result_data_type !== 'numeric' || !is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;

        if ($parameter->critical_low !== null && $numeric < (float) $parameter->critical_low) {
            return LabResultFlagEnum::CRITICAL_LOW;
        }
        if ($parameter->critical_high !== null && $numeric > (float) $parameter->critical_high) {
            return LabResultFlagEnum::CRITICAL_HIGH;
        }
        if (!$range) {
            return null;
        }
        if ($range->range_low !== null && $numeric < (float) $range->range_low) {
            return LabResultFlagEnum::LOW;
        }
        if ($range->range_high !== null && $numeric > (float) $range->range_high) {
            return LabResultFlagEnum::HIGH;
        }
        return LabResultFlagEnum::NORMAL;
    }

    protected function rangeDisplay(?LabTestReferenceRange $range): ?string
    {
        if (!$range) {
            return null;
        }
        if ($range->range_text) {
            return $range->range_text;
        }
        if ($range->range_low !== null && $range->range_high !== null) {
            return "{$range->range_low} - {$range->range_high}";
        }
        return null;
    }

    protected function alertCriticalValue(LabOrderItem $item): void
    {
        $order = $item->order;
        if (!$order || !$order->ordered_by) {
            return;
        }

        app(NotificationService::class)->sendEvent('critical_lab_value', (int) $order->ordered_by, [
            'patient_name' => trim(($order->patient->first_name ?? '') . ' ' . ($order->patient->last_name ?? '')),
            'test_name'    => $item->test_name_snapshot,
            'lab_order_no' => $order->lab_order_no,
        ], ['in_app', 'email']);
    }
}
