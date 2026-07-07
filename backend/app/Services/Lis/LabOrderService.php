<?php

namespace App\Services\Lis;

use App\Enums\LabOrderItemStatusEnum;
use App\Enums\LabOrderStatusEnum;
use App\Exceptions\ApiException;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Repositories\LabOrderRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabOrderService
{
    protected LabOrderRepository $repository;

    public function __construct(LabOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Place a lab order for a patient — from an OPD visit, an IPD admission,
     * or neither (walk-in). Each requested test is snapshotted from the
     * LabTest catalog (name/sample type/price) at order time, same
     * traceability pattern as OPD prescription items.
     */
    public function placeOrder(array $data, int $actorId): LabOrder
    {
        return DB::transaction(function () use ($data, $actorId) {
            $orderSource = 'walk_in';
            if (!empty($data['ipd_admission_id'])) {
                $orderSource = 'ipd';
            } elseif (!empty($data['opd_visit_id'])) {
                $orderSource = 'opd';
            }

            $orderedAt = now();
            $order = LabOrder::query()->create([
                'lab_order_no'         => $this->generateOrderNo($orderedAt->toDateString()),
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
                $labTest = LabTest::query()->find($item['lab_test_id']);
                if (!$labTest) {
                    continue;
                }

                LabOrderItem::query()->create([
                    'organogram_id'        => $order->organogram_id,
                    'lab_order_id'         => $order->id,
                    'lab_test_id'          => $labTest->id,
                    'test_name_snapshot'   => $labTest->name,
                    'sample_type_snapshot' => $labTest->sample_type,
                    'price_snapshot'       => $labTest->default_price,
                    'sequence'             => $i + 1,
                ]);
            }

            return $order->fresh(['items', 'samples']);
        });
    }

    public function cancel(int $orderId, int $actorId, ?string $reason): LabOrder
    {
        return DB::transaction(function () use ($orderId, $reason) {
            $order = LabOrder::query()->lockForUpdate()->findOrFail($orderId);

            if (LabOrderStatusEnum::isTerminal($order->order_status)) {
                throw new ApiException("Order is already {$order->order_status}.", 422);
            }

            $order->order_status = LabOrderStatusEnum::CANCELLED;
            $order->save();

            LabOrderItem::query()
                ->where('lab_order_id', $orderId)
                ->where('item_status', '!=', LabOrderItemStatusEnum::VERIFIED)
                ->update(['item_status' => LabOrderItemStatusEnum::CANCELLED]);

            return $order->fresh(['items', 'samples']);
        });
    }

    /**
     * Stream the branded PDF report — available once the order has at
     * least reached 'verified' (every item dual-signed-off). Streaming
     * doesn't mutate state; call markReported() separately to record that
     * the report was released (e.g. handed to the patient / portal).
     */
    public function renderReportPdf(int $orderId)
    {
        $order = $this->repository->withRelations($orderId);

        if (!in_array($order->order_status, [LabOrderStatusEnum::VERIFIED, LabOrderStatusEnum::REPORTED], true)) {
            throw new ApiException('This report is not ready — every test must be verified first.', 422);
        }

        $pdf = Pdf::loadView('pdf.lab_report', ['order' => $order]);
        return $pdf->stream("lab-report-{$order->lab_order_no}.pdf");
    }

    public function markReported(int $orderId): LabOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = LabOrder::query()->lockForUpdate()->findOrFail($orderId);

            if (!LabOrderStatusEnum::canTransition($order->order_status, LabOrderStatusEnum::REPORTED)) {
                throw new ApiException("Cannot mark as reported from status '{$order->order_status}'.", 422);
            }

            $order->order_status = LabOrderStatusEnum::REPORTED;
            $order->save();

            return $order->fresh(['items', 'samples']);
        });
    }

    protected function generateOrderNo(string $dateYmd): string
    {
        return DB::transaction(function () use ($dateYmd) {
            $row = DB::table('code_sequences')
                ->where('label', 'LAB_ORDER')
                ->where('sequence_date', $dateYmd)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('code_sequences')->insert([
                    'uuid'          => (string) Str::uuid(),
                    'label'         => 'LAB_ORDER',
                    'prefix'        => 'LAB',
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
            return "LAB-{$datePart}-" . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
