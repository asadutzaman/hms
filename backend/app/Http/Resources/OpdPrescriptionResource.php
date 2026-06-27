<?php

namespace App\Http\Resources;

use App\Repositories\UserRepository;

class OpdPrescriptionResource extends BaseResource
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

            $prescriberName = '';
            if (!empty($resource->prescribed_by)) {
                $user = (new UserRepository())->getById($resource->prescribed_by);
                $prescriberName = $user->name ?? '';
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
                'prescribed_by'  => $this->prescribed_by,
                'notes'          => $this->notes,
                'is_printed'     => (bool) $this->is_printed,
                'printed_at'     => $this->printed_at,
                'item_count'     => $itemCount,
                'total_amount'   => round($totalAmount, 2),
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            $merged = array_merge($data, [
                'prescriber_name' => $prescriberName,
            ]);

            if ($resource->relationLoaded('items')) {
                $merged['items'] = OpdPrescriptionItemResource::collection($resource->items)->toArray($request);
            }

            return $merged;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
