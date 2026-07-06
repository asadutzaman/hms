<?php

namespace App\Http\Resources;

use App\Enums\IpdPaymentMethodEnum;
use App\Repositories\UserRepository;

class IpdBillPaymentResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $method = $resource->payment_method ?? '';

            $paidByName = '';
            if (!empty($resource->paid_by)) {
                $user = (new UserRepository())->getById($resource->paid_by);
                $paidByName = $user->name ?? '';
            }

            $data = [
                'id'                    => $this->id,
                'uuid'                  => $this->uuid,
                'ipd_bill_id'           => $this->ipd_bill_id,
                'payment_method'        => $method,
                'payment_method_label'  => $method !== '' ? IpdPaymentMethodEnum::label($method) : null,
                'amount'                => $this->amount,
                'reference_no'          => $this->reference_no,
                'notes'                 => $this->notes,
                'paid_by'               => $this->paid_by,
                'paid_by_name'          => $paidByName,
                'paid_at'               => $this->paid_at,
                'created_by_name'       => $baseData['created_by_name'] ?? null,
                'updated_by_name'       => $baseData['updated_by_name'] ?? null,
                'created_at'            => $baseData['created_at'] ?? null,
                'updated_at'            => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
