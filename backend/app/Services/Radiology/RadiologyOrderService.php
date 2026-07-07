<?php

namespace App\Services\Radiology;

use App\Enums\RadOrderItemStatusEnum;
use App\Enums\RadOrderStatusEnum;
use App\Exceptions\ApiException;
use App\Models\RadiologyOrder;
use App\Models\RadiologyOrderItem;
use App\Models\RadiologyTest;
use App\Repositories\RadiologyOrderRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RadiologyOrderService
{
    protected RadiologyOrderRepository $repository;

    public function __construct(RadiologyOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function placeOrder(array $data, int $actorId): RadiologyOrder
    {
        return DB::transaction(function () use ($data, $actorId) {
            $orderSource = 'walk_in';
            if (!empty($data['ipd_admission_id'])) {
                $orderSource = 'ipd';
            } elseif (!empty($data['opd_visit_id'])) {
                $orderSource = 'opd';
            }

            $orderedAt = now();
            $order = RadiologyOrder::query()->create([
                'rad_order_no'         => $this->generateOrderNo($orderedAt->toDateString()),
                'patient_id'           => $data['patient_id'],
                'opd_visit_id'         => $data['opd_visit_id'] ?? null,
                'ipd_admission_id'     => $data['ipd_admission_id'] ?? null,
                'order_source'         => $orderSource,
                'ordered_by'           => $actorId,
                'ordered_at'           => $orderedAt,
                'priority'             => $data['priority'] ?? 'routine',
                'clinical_indication'  => $data['clinical_indication'] ?? null,
            ]);

            foreach (($data['items'] ?? []) as $i => $item) {
                $radTest = RadiologyTest::query()->find($item['radiology_test_id']);
                if (!$radTest) {
                    continue;
                }

                RadiologyOrderItem::query()->create([
                    'organogram_id'       => $order->organogram_id,
                    'radiology_order_id'  => $order->id,
                    'radiology_test_id'   => $radTest->id,
                    'test_name_snapshot'  => $radTest->name,
                    'modality_snapshot'   => $radTest->modality,
                    'price_snapshot'      => $radTest->default_price,
                    'sequence'            => $i + 1,
                ]);
            }

            return $order->fresh(['items']);
        });
    }

    public function cancel(int $orderId): RadiologyOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = RadiologyOrder::query()->lockForUpdate()->findOrFail($orderId);

            if (RadOrderStatusEnum::isTerminal($order->order_status)) {
                throw new ApiException("Order is already {$order->order_status}.", 422);
            }

            $order->order_status = RadOrderStatusEnum::CANCELLED;
            $order->save();

            RadiologyOrderItem::query()
                ->where('radiology_order_id', $orderId)
                ->where('item_status', '!=', RadOrderItemStatusEnum::VERIFIED)
                ->update(['item_status' => RadOrderItemStatusEnum::CANCELLED]);

            return $order->fresh(['items']);
        });
    }

    public function renderReportPdf(int $orderId)
    {
        $order = $this->repository->withRelations($orderId);

        if (!in_array($order->order_status, [RadOrderStatusEnum::IN_PROGRESS, RadOrderStatusEnum::REPORTED], true)) {
            throw new ApiException('This report is not ready.', 422);
        }

        $pdf = Pdf::loadView('pdf.radiology_report', ['order' => $order]);
        return $pdf->stream("radiology-report-{$order->rad_order_no}.pdf");
    }

    public function markReported(int $orderId): RadiologyOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = RadiologyOrder::query()->lockForUpdate()->findOrFail($orderId);

            if (!RadOrderStatusEnum::canTransition($order->order_status, RadOrderStatusEnum::REPORTED)) {
                throw new ApiException("Cannot mark as reported from status '{$order->order_status}'.", 422);
            }

            $order->order_status = RadOrderStatusEnum::REPORTED;
            $order->save();

            return $order->fresh(['items']);
        });
    }

    protected function generateOrderNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'RAD_ORDER')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'RAD_ORDER',
                    'prefix'        => 'RAD',
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
            return "RAD-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
