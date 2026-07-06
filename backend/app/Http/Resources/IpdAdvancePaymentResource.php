<?php

namespace App\Http\Resources;

use App\Enums\IpdAdvancePaymentStatusEnum;
use App\Enums\IpdPaymentMethodEnum;
use App\Repositories\UserRepository;

class IpdAdvancePaymentResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $resource = $this->resource;

            $method = $resource->payment_method ?? '';
            $status = $resource->advance_status ?? '';

            $receivedByName = '';
            if (!empty($resource->received_by)) {
                $user = (new UserRepository())->getById($resource->received_by);
                $receivedByName = $user->name ?? '';
            }

            $data = [
                'id'                   => $this->id,
                'uuid'                 => $this->uuid,
                'admission_id'         => $this->admission_id,
                'amount'               => $this->amount,
                'applied_amount'       => $this->applied_amount,
                'unapplied_amount'     => round((float) $this->amount - (float) $this->applied_amount, 2),
                'payment_method'       => $method,
                'payment_method_label' => $method !== '' ? IpdPaymentMethodEnum::label($method) : null,
                'reference_no'         => $this->reference_no,
                'notes'                => $this->notes,
                'advance_status'       => $status,
                'advance_status_label' => $status !== '' ? IpdAdvancePaymentStatusEnum::label($status) : null,
                'received_by'          => $this->received_by,
                'received_by_name'     => $receivedByName,
                'received_at'          => $this->received_at,
                'created_by_name'      => $baseData['created_by_name'] ?? null,
                'updated_by_name'      => $baseData['updated_by_name'] ?? null,
                'created_at'           => $baseData['created_at'] ?? null,
                'updated_at'           => $baseData['updated_at'] ?? null,
            ];

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
