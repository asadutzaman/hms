<?php

namespace App\Http\Resources;

use App\Enums\OpdInvestigationOrderStatusEnum;
use App\Repositories\UserRepository;

class OpdInvestigationOrderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $priorityLabel = ucfirst((string) ($resource->priority ?? ''));

            $statusValue = $resource->status ?? null;
            $statusLabel = $statusValue !== null
                ? OpdInvestigationOrderStatusEnum::getValue($statusValue)
                : null;

            $isTerminal = in_array($statusValue, ['completed', 'cancelled'], true);

            $ordererName = '';
            if (!empty($resource->ordered_by)) {
                $user = (new UserRepository())->getById($resource->ordered_by);
                $ordererName = $user->name ?? '';
            }

            $itemCount = 0;
            $totalAmount = 0.0;
            if ($resource->relationLoaded('items')) {
                $items = $resource->items;
                $itemCount = $items->count();
                $totalAmount = (float) $items->where('status_flag', 1)->sum('amount');
            }

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'visit_id'       => $this->visit_id,
                'order_no'       => $this->order_no,
                'priority'       => $this->priority,
                'priority_label' => $priorityLabel,
                'status'         => $this->status,
                'status_label'   => $statusLabel,
                'is_terminal'    => $isTerminal,
                'notes'          => $this->notes,
                'ordered_by'     => $this->ordered_by,
                'orderer_name'   => $ordererName,
                'ordered_at'     => $this->ordered_at,
                'completed_at'   => $this->completed_at,
                'item_count'     => $itemCount,
                'total_amount'   => round($totalAmount, 2),
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('items')) {
                $data['items'] = OpdInvestigationOrderItemResource::collection($resource->items)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
