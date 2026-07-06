<?php

namespace App\Http\Resources;

use App\Enums\IpdBillStatusEnum;
use App\Repositories\UserRepository;

class IpdBillResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $status = $resource->bill_status ?? null;

            $billedByName = '';
            if (!empty($resource->billed_by)) {
                $user = (new UserRepository())->getById($resource->billed_by);
                $billedByName = $user->name ?? '';
            }

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'admission_id'         => $this->admission_id,
                'bill_no'              => $this->bill_no,
                'billed_at'            => $this->billed_at,
                'subtotal'             => $this->subtotal,
                'discount'             => $this->discount,
                'discount_reason'      => $this->discount_reason,
                'discount_type'        => $this->discount_type,
                'discount_status'      => $this->discount_status,
                'discount_approved_by' => $this->discount_approved_by,
                'discount_approved_at' => $this->discount_approved_at,
                'pending_discount'     => $this->pending_discount,
                'tax'                  => $this->tax,
                'total'                => $this->total,
                'paid'                 => $this->paid,
                'balance'              => $this->balance,
                'bill_status'          => $status,
                'bill_status_label'    => IpdBillStatusEnum::label($status),
                'is_paid'              => $status === IpdBillStatusEnum::PAID,
                'is_partial'           => $status === IpdBillStatusEnum::PARTIAL,
                'is_unpaid'            => $status === IpdBillStatusEnum::UNPAID || $status === null,
                'is_waived'            => $status === IpdBillStatusEnum::WAIVED,
                'is_finalized'         => (bool) $this->is_finalized,
                'billed_by'            => $this->billed_by,
                'billed_by_name'       => $billedByName,
                'created_by_name'      => $baseData['created_by_name'] ?? null,
                'updated_by_name'      => $baseData['updated_by_name'] ?? null,
                'created_at'           => $baseData['created_at'] ?? null,
                'updated_at'           => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('items')) {
                $data['items'] = IpdBillItemResource::collection($resource->items)->toArray($request);
                $data['item_count'] = $resource->items->count();
            }
            if ($resource->relationLoaded('payments')) {
                $data['payments'] = IpdBillPaymentResource::collection($resource->payments)->toArray($request);
                $data['payment_count'] = $resource->payments->count();
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
