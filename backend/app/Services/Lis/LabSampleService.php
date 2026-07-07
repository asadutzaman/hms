<?php

namespace App\Services\Lis;

use App\Enums\LabOrderItemStatusEnum;
use App\Enums\LabOrderStatusEnum;
use App\Enums\LabSampleStatusEnum;
use App\Exceptions\ApiException;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabSample;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabSampleService
{
    /**
     * Collect a sample for one sample-type within an order — prints (mints)
     * a barcode label and moves the order/its matching items forward. A
     * single lab order can need more than one sample (e.g. blood + urine),
     * so this is called once per distinct sample_type present in the order.
     */
    public function collect(int $orderId, string $sampleType, int $actorId): LabSample
    {
        return DB::transaction(function () use ($orderId, $sampleType, $actorId) {
            $order = LabOrder::query()->lockForUpdate()->findOrFail($orderId);

            if (LabOrderStatusEnum::isTerminal($order->order_status)) {
                throw new ApiException("Cannot collect a sample for an order that is already {$order->order_status}.", 422);
            }

            $existing = LabSample::query()
                ->where('lab_order_id', $orderId)
                ->where('sample_type', $sampleType)
                ->first();
            if ($existing) {
                throw new ApiException("A {$sampleType} sample has already been collected for this order.", 422);
            }

            $sample = LabSample::query()->create([
                'organogram_id'  => $order->organogram_id,
                'lab_order_id'   => $orderId,
                'barcode'        => $this->generateBarcode(now()->toDateString()),
                'sample_type'    => $sampleType,
                'sample_status'  => LabSampleStatusEnum::COLLECTED,
                'collected_by'   => $actorId,
                'collected_at'   => now(),
            ]);

            LabOrderItem::query()
                ->where('lab_order_id', $orderId)
                ->where('sample_type_snapshot', $sampleType)
                ->where('item_status', LabOrderItemStatusEnum::ORDERED)
                ->update(['item_status' => LabOrderItemStatusEnum::SAMPLE_COLLECTED]);

            if ($order->order_status === LabOrderStatusEnum::ORDERED) {
                $order->order_status = LabOrderStatusEnum::SAMPLE_COLLECTED;
                $order->save();
            }

            return $sample->fresh();
        });
    }

    /** Lab reception scans the barcode to confirm receipt of the specimen. */
    public function receive(string $barcode, int $actorId): LabSample
    {
        return DB::transaction(function () use ($barcode, $actorId) {
            $sample = LabSample::query()->where('barcode', $barcode)->lockForUpdate()->first();
            if (!$sample) {
                throw new ApiException("No sample found for barcode {$barcode}.", 404);
            }
            if (!LabSampleStatusEnum::canTransition($sample->sample_status, LabSampleStatusEnum::RECEIVED)) {
                throw new ApiException("Cannot receive a sample in status '{$sample->sample_status}'.", 422);
            }

            $sample->sample_status = LabSampleStatusEnum::RECEIVED;
            $sample->received_by = $actorId;
            $sample->received_at = now();
            $sample->save();

            LabOrderItem::query()
                ->where('lab_order_id', $sample->lab_order_id)
                ->where('sample_type_snapshot', $sample->sample_type)
                ->where('item_status', LabOrderItemStatusEnum::SAMPLE_COLLECTED)
                ->update(['item_status' => LabOrderItemStatusEnum::IN_PROGRESS]);

            $order = LabOrder::query()->lockForUpdate()->find($sample->lab_order_id);
            if ($order && $order->order_status === LabOrderStatusEnum::SAMPLE_COLLECTED) {
                $order->order_status = LabOrderStatusEnum::IN_PROGRESS;
                $order->save();
            }

            return $sample->fresh();
        });
    }

    public function reject(int $sampleId, int $actorId, string $reason): LabSample
    {
        return DB::transaction(function () use ($sampleId, $reason) {
            $sample = LabSample::query()->lockForUpdate()->findOrFail($sampleId);

            if (!LabSampleStatusEnum::canTransition($sample->sample_status, LabSampleStatusEnum::REJECTED)) {
                throw new ApiException("Cannot reject a sample in status '{$sample->sample_status}'.", 422);
            }

            $sample->sample_status = LabSampleStatusEnum::REJECTED;
            $sample->rejection_reason = $reason;
            $sample->save();

            return $sample->fresh();
        });
    }

    protected function generateBarcode(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'LAB_SAMPLE')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'LAB_SAMPLE',
                    'prefix'        => 'SPC',
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
            return "SPC-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
