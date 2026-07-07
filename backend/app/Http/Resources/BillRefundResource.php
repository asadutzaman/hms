<?php

namespace App\Http\Resources;

use App\Enums\BillRefundStatusEnum;

class BillRefundResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $status = $this->refund_status;

            return [
                'id'                      => $this->id,
                'uuid'                    => $this->uuid,
                'refund_no'               => $this->refund_no,
                'billable_type'           => $this->billable_type,
                'billable_id'             => $this->billable_id,
                'amount'                  => $this->amount,
                'reason'                  => $this->reason,
                'payment_method_reversed' => $this->payment_method_reversed,
                'refund_status'           => $status,
                'refund_status_label'     => BillRefundStatusEnum::label($status),
                'requested_by'            => $this->requested_by,
                'requested_at'            => $this->requested_at,
                'approved_by'             => $this->approved_by,
                'approved_at'             => $this->approved_at,
                'notes'                   => $this->notes,
                'created_by_name'         => $baseData['created_by_name'] ?? null,
                'updated_by_name'         => $baseData['updated_by_name'] ?? null,
                'created_at'              => $baseData['created_at'] ?? null,
                'updated_at'              => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
