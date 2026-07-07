<?php

namespace App\Services\Radiology;

use App\Enums\RadOrderItemStatusEnum;
use App\Enums\RadOrderStatusEnum;
use App\Enums\RadReportStatusEnum;
use App\Exceptions\ApiException;
use App\Models\RadiologyOrderItem;
use App\Models\RadiologyReport;
use Illuminate\Support\Facades\DB;

class RadiologyReportService
{
    /**
     * Save (create or update) a report as a draft — pre-fillable from a
     * RadiologyReportTemplate on the frontend, free-text editable here.
     * Bumps the order/item into 'in_progress' the first time a draft is
     * saved (mirrors the LIS sample-received transition, minus the sample
     * step radiology doesn't have).
     */
    public function saveDraft(int $orderItemId, array $data, int $actorId): RadiologyOrderItem
    {
        return DB::transaction(function () use ($orderItemId, $data, $actorId) {
            $item = RadiologyOrderItem::query()->lockForUpdate()->findOrFail($orderItemId);

            RadiologyReport::query()->updateOrCreate(
                ['radiology_order_item_id' => $orderItemId],
                [
                    'organogram_id'                => $item->organogram_id,
                    'radiology_report_template_id' => $data['radiology_report_template_id'] ?? null,
                    'findings'                     => $data['findings'] ?? null,
                    'impression'                   => $data['impression'] ?? null,
                    'report_status'                => RadReportStatusEnum::DRAFT,
                ]
            );

            if ($item->item_status === RadOrderItemStatusEnum::ORDERED) {
                $item->item_status = RadOrderItemStatusEnum::IN_PROGRESS;
                $item->save();
            }

            $order = $item->order;
            if ($order && $order->order_status === RadOrderStatusEnum::ORDERED) {
                $order->order_status = RadOrderStatusEnum::IN_PROGRESS;
                $order->save();
            }

            return $item->fresh(['report']);
        });
    }

    /** Finalize the report — locks it in as the released, official version. */
    public function finalize(int $orderItemId, int $actorId): RadiologyOrderItem
    {
        return DB::transaction(function () use ($orderItemId, $actorId) {
            $item = RadiologyOrderItem::query()->lockForUpdate()->findOrFail($orderItemId);
            $report = RadiologyReport::query()->where('radiology_order_item_id', $orderItemId)->first();

            if (!$report || empty($report->findings)) {
                throw new ApiException('Enter findings before finalizing the report.', 422);
            }

            $report->report_status = $report->report_status === RadReportStatusEnum::FINAL
                ? RadReportStatusEnum::AMENDED
                : RadReportStatusEnum::FINAL;
            $report->reported_by = $actorId;
            $report->reported_at = now();
            $report->save();

            $item->item_status = RadOrderItemStatusEnum::REPORTED;
            $item->save();

            $this->maybeAdvanceOrder($item->radiology_order_id);

            return $item->fresh(['report']);
        });
    }

    /** Optional secondary sign-off (e.g. senior radiologist / referring physician). */
    public function verify(int $orderItemId, int $actorId): RadiologyOrderItem
    {
        return DB::transaction(function () use ($orderItemId, $actorId) {
            $item = RadiologyOrderItem::query()->lockForUpdate()->findOrFail($orderItemId);

            if ($item->item_status !== RadOrderItemStatusEnum::REPORTED) {
                throw new ApiException('Report must be finalized before it can be verified.', 422);
            }

            $report = RadiologyReport::query()->where('radiology_order_item_id', $orderItemId)->first();
            if ($report) {
                $report->verified_by = $actorId;
                $report->verified_at = now();
                $report->save();
            }

            $item->item_status = RadOrderItemStatusEnum::VERIFIED;
            $item->save();

            return $item->fresh(['report']);
        });
    }

    protected function maybeAdvanceOrder(int $orderId): void
    {
        $items = RadiologyOrderItem::query()->where('radiology_order_id', $orderId)->get();
        $active = $items->where('item_status', '!=', RadOrderItemStatusEnum::CANCELLED);

        if ($active->isEmpty()) {
            return;
        }

        $allReported = $active->every(fn ($i) => in_array($i->item_status, [RadOrderItemStatusEnum::REPORTED, RadOrderItemStatusEnum::VERIFIED], true));

        if ($allReported) {
            $order = $items->first()->order;
            if ($order && RadOrderStatusEnum::canTransition($order->order_status, RadOrderStatusEnum::REPORTED)) {
                $order->order_status = RadOrderStatusEnum::REPORTED;
                $order->save();
            }
        }
    }
}
