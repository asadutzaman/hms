<?php

namespace App\Http\Resources;

use App\Enums\OpdPaymentMethodEnum;
use App\Repositories\UserRepository;

class OpdBillPaymentResource extends BaseResource
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

            $method = $resource->payment_method ?? '';
            $methodLabel = $method !== '' ? OpdPaymentMethodEnum::label($method) : null;

            $paidByName = '';
            if (!empty($resource->paid_by)) {
                $user = (new UserRepository())->getById($resource->paid_by);
                $paidByName = $user->name ?? '';
            }

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'opd_bill_id'    => $this->opd_bill_id,
                'payment_method' => $this->payment_method,
                'payment_method_label' => $methodLabel,
                'amount'         => $this->amount,
                'reference_no'   => $this->reference_no,
                'paid_at'        => $this->paid_at,
                'notes'          => $this->notes,
                'paid_by'        => $this->paid_by,
                'paid_by_name'   => $paidByName,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
