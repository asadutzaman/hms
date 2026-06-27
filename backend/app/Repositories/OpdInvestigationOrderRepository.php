<?php

namespace App\Repositories;

use App\Enums\OpdInvestigationOrderStatusEnum;
use App\Enums\OpdVisitActionEnum;
use App\Models\LabTest;
use App\Models\OpdInvestigationOrder;
use App\Models\OpdInvestigationOrderItem;
use App\Models\OpdVisit;
use App\Services\ODataService;
use Illuminate\Support\Facades\DB;

class OpdInvestigationOrderRepository extends BaseRepository
{
    /**
     * @var OpdInvestigationOrder
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'order_no',
        'priority',
        'clinical_indication',
        'notes',
    ];

    public function __construct()
    {
        $this->model = new OpdInvestigationOrder();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forVisit(int $visitId)
    {
        return $this->newQuery()
            ->with('items')
            ->where('opd_visit_id', $visitId)
            ->orderByDesc('ordered_at')
            ->get();
    }

    /**
     * Create an investigation order for a visit with a list of lab_test_ids.
     * Snapshots test_code/name/sample/tat/price from the lab_tests catalogue.
     */
    public function createForVisit(int $visitId, int $actorId, array $labTestIds, array $header = []): OpdInvestigationOrder
    {
        return DB::transaction(function () use ($visitId, $actorId, $labTestIds, $header) {
            $visit = OpdVisit::query()->findOrFail($visitId);

            $orderNo = 'INV-' . now()->format('Ymd-His') . '-' . random_int(100, 999);

            $order = OpdInvestigationOrder::query()->create([
                'organogram_id'       => $visit->organogram_id,
                'opd_visit_id'        => $visitId,
                'patient_id'          => $visit->patient_id,
                'order_no'            => $orderNo,
                'priority'            => $header['priority']            ?? 'routine',
                'ordered_by'          => $actorId,
                'ordered_at'          => $header['ordered_at']          ?? now(),
                'clinical_indication' => $header['clinical_indication'] ?? null,
                'notes'               => $header['notes']               ?? null,
                'status'              => OpdInvestigationOrderStatusEnum::ORDERED,
            ]);

            foreach ($labTestIds as $i => $labTestId) {
                $test = LabTest::query()->find($labTestId);
                if (!$test) {
                    continue;
                }
                OpdInvestigationOrderItem::query()->create([
                    'organogram_id'              => $visit->organogram_id,
                    'opd_investigation_order_id' => $order->id,
                    'opd_visit_id'               => $visitId,
                    'lab_test_id'                => $test->id,
                    'test_code'                  => $test->code         ?? null,
                    'test_name'                  => $test->name         ?? null,
                    'department'                 => $test->department   ?? null,
                    'sample_type'                => $test->sample_type  ?? null,
                    'tat_minutes'                => $test->tat_minutes  ?? null,
                    'unit_price'                 => $test->default_price ?? 0,
                    'amount'                     => $test->default_price ?? 0,
                    'result_status'              => 'pending',
                    'sequence'                   => $i + 1,
                    'status'                     => 1,
                ]);
            }

            app(OpdVisitRepository::class)->logAudit(
                $visit,
                OpdVisitActionEnum::ORDER_SAVED,
                null,
                null,
                $actorId,
                'Investigation order placed',
                ['order_id' => $order->id, 'order_no' => $orderNo, 'item_count' => count($labTestIds)],
            );

            return $order->fresh('items');
        });
    }

    public function setStatus(int $orderId, string $toStatus): OpdInvestigationOrder
    {
        $order = $this->newQuery()->findOrFail($orderId);
        if (!OpdInvestigationOrderStatusEnum::canTransition((string) $order->status, $toStatus)) {
            throw new \App\Exceptions\ApiException(
                "Illegal investigation-order transition: {$order->status} → {$toStatus}",
                422,
            );
        }
        $order->status = $toStatus;
        $order->save();
        return $order;
    }
}
