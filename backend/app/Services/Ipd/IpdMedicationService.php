<?php

namespace App\Services\Ipd;

use App\Enums\IpdMedicationAdministrationStatusEnum;
use App\Enums\IpdMedicationOrderStatusEnum;
use App\Exceptions\ApiException;
use App\Models\IpdMedicationAdministration;
use App\Models\IpdMedicationOrder;
use App\Repositories\IpdMedicationOrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IpdMedicationService
{
    /**
     * Times-of-day (24h) generated per frequency code. STAT/SOS/PRN are
     * handled separately (no pre-generated recurring slots — see order()).
     */
    protected const FREQUENCY_TIMES = [
        'OD'  => ['08:00'],
        'BD'  => ['08:00', '20:00'],
        'TID' => ['08:00', '14:00', '20:00'],
        'QID' => ['06:00', '12:00', '18:00', '00:00'],
        'HS'  => ['22:00'],
    ];

    /** Cap schedule generation for open-ended orders so it can't run away. */
    protected const MAX_SCHEDULE_DAYS = 14;

    protected IpdMedicationOrderRepository $orderRepository;

    public function __construct(IpdMedicationOrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Place a medication order and — unless it's STAT/SOS/PRN — generate its
     * recurring administration slots up front (capped at MAX_SCHEDULE_DAYS
     * for open-ended orders; no cron/job infra exists in this app to extend
     * the schedule later, so re-ordering is how a nurse/doctor extends a
     * long-running order past the cap).
     */
    public function order(array $data, int $actorId): IpdMedicationOrder
    {
        return DB::transaction(function () use ($data, $actorId) {
            $isPrn = !empty($data['is_prn']) || in_array($data['frequency'] ?? null, ['SOS', 'PRN'], true);

            $order = IpdMedicationOrder::query()->create(array_merge($data, [
                'is_prn'     => $isPrn,
                'ordered_by' => $actorId,
            ]));

            if (!$isPrn) {
                $this->generateSchedule($order);
            }

            return $order->fresh('administrations');
        });
    }

    protected function generateSchedule(IpdMedicationOrder $order): void
    {
        $frequency = $order->frequency;
        $start = Carbon::parse($order->start_date)->startOfDay();

        if ($frequency === 'STAT') {
            IpdMedicationAdministration::query()->create([
                'organogram_id' => $order->organogram_id,
                'order_id'      => $order->id,
                'scheduled_at'  => now(),
            ]);
            return;
        }

        $times = self::FREQUENCY_TIMES[$frequency] ?? ['08:00'];

        $end = $order->end_date
            ? Carbon::parse($order->end_date)->endOfDay()
            : (clone $start)->addDays(self::MAX_SCHEDULE_DAYS);
        $cappedEnd = (clone $start)->addDays(self::MAX_SCHEDULE_DAYS);
        if ($end->greaterThan($cappedEnd)) {
            $end = $cappedEnd;
        }

        $day = clone $start;
        while ($day->lessThanOrEqualTo($end)) {
            foreach ($times as $time) {
                [$hour, $minute] = explode(':', $time);
                $slot = (clone $day)->setTime((int) $hour, (int) $minute);
                if ($slot->greaterThanOrEqualTo($start)) {
                    IpdMedicationAdministration::query()->create([
                        'organogram_id' => $order->organogram_id,
                        'order_id'      => $order->id,
                        'scheduled_at'  => $slot,
                    ]);
                }
            }
            $day->addDay();
        }
    }

    /**
     * Record an ad-hoc PRN/SOS administration — no pre-generated slot to
     * attach to, so create the administration row directly, already given.
     */
    public function recordPrnAdministration(int $orderId, array $data, int $actorId): IpdMedicationOrder
    {
        return DB::transaction(function () use ($orderId, $data, $actorId) {
            $order = $this->orderRepository->show($orderId);

            if (!$order->is_prn) {
                throw new ApiException('This order is not PRN/SOS — administer via its scheduled slots instead.', 422);
            }
            if ($order->order_status !== IpdMedicationOrderStatusEnum::ACTIVE) {
                throw new ApiException('Cannot record administration against a discontinued/completed order.', 422);
            }

            IpdMedicationAdministration::query()->create([
                'organogram_id'         => $order->organogram_id,
                'order_id'              => $order->id,
                'scheduled_at'          => null,
                'administered_at'       => now(),
                'administration_status' => IpdMedicationAdministrationStatusEnum::GIVEN,
                'administered_by'       => $actorId,
                'witnessed_by'          => $data['witnessed_by'] ?? null,
                'notes'                 => $data['notes'] ?? null,
            ]);

            return $this->orderRepository->withAdministrations($orderId);
        });
    }

    /**
     * Act on a scheduled slot: given / held / refused / missed.
     */
    public function recordAdministration(int $administrationId, array $data, int $actorId): IpdMedicationOrder
    {
        return DB::transaction(function () use ($administrationId, $data, $actorId) {
            $administration = IpdMedicationAdministration::query()->lockForUpdate()->findOrFail($administrationId);

            $administration->administration_status = $data['administration_status'];
            $administration->reason = $data['reason'] ?? null;
            $administration->notes = $data['notes'] ?? $administration->notes;
            $administration->witnessed_by = $data['witnessed_by'] ?? null;

            if ($data['administration_status'] === IpdMedicationAdministrationStatusEnum::GIVEN) {
                $administration->administered_at = now();
                $administration->administered_by = $actorId;
            }

            $administration->save();

            return $this->orderRepository->withAdministrations($administration->order_id);
        });
    }

    public function discontinue(int $orderId, int $actorId, ?string $reason): IpdMedicationOrder
    {
        return DB::transaction(function () use ($orderId, $actorId, $reason) {
            $order = IpdMedicationOrder::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->order_status !== IpdMedicationOrderStatusEnum::ACTIVE) {
                throw new ApiException("Order is already {$order->order_status}.", 422);
            }

            $order->order_status = IpdMedicationOrderStatusEnum::DISCONTINUED;
            $order->discontinued_by = $actorId;
            $order->discontinued_at = now();
            $order->discontinue_reason = $reason;
            $order->save();

            // Cancel any not-yet-acted-upon future slots so they don't show as
            // overdue on the MAR worklist after discontinuation.
            IpdMedicationAdministration::query()
                ->where('order_id', $order->id)
                ->where('administration_status', IpdMedicationAdministrationStatusEnum::SCHEDULED)
                ->where('scheduled_at', '>', now())
                ->update(['administration_status' => IpdMedicationAdministrationStatusEnum::MISSED]);

            return $order->fresh('administrations');
        });
    }
}
