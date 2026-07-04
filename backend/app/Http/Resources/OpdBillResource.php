<?php

namespace App\Http\Resources;

use App\Enums\OpdBillStatusEnum;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;

class OpdBillResource extends BaseResource
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

            $status = $resource->status ?? null;
            $isPaid    = ($status === 'paid');
            $isPartial = ($status === 'partial');
            $isUnpaid  = ($status === 'unpaid' || $status === null);
            $isWaived  = ($status === 'waived' || $status === 'cancelled');

            $billedByName = '';
            if (!empty($resource->billed_by)) {
                $user = (new UserRepository())->getById($resource->billed_by);
                $billedByName = $user->name ?? '';
            }

            $patientName = '';
            if ($resource->relationLoaded('visit') && $resource->visit) {
                $visit = $resource->visit;
                if (!empty($visit->patient_id)) {
                    $patient = (new PatientRepository())->getById($visit->patient_id);
                    $patientName = $patient
                        ? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''))
                        : '';
                }
            }

            $itemCount = 0;
            $paymentCount = 0;
            if ($resource->relationLoaded('items')) {
                $itemCount = $resource->items->count();
            }
            if ($resource->relationLoaded('payments')) {
                $paymentCount = $resource->payments->count();
            }

            $data = [
                'id'             => $this->id,
                'uuid'           => $this->uuid,
                'opd_visit_id'   => $this->opd_visit_id,
                'bill_no'        => $this->bill_no,
                'billed_at'      => $this->billed_at,
                'subtotal'       => $this->subtotal,
                'discount'       => $this->discount,
                'discount_reason'      => $this->discount_reason,
                'discount_type'        => $this->discount_type,
                'discount_status'      => $this->discount_status,
                'discount_approved_by' => $this->discount_approved_by,
                'discount_approved_at' => $this->discount_approved_at,
                'pending_discount'     => $this->pending_discount,
                'tax'            => $this->tax,
                'total'          => $this->total,
                'paid'           => $this->paid,
                'balance'        => $this->balance,
                'status'         => $this->status,
                'status_label'   => OpdBillStatusEnum::label($status),
                'is_paid'        => $isPaid,
                'is_partial'     => $isPartial,
                'is_unpaid'      => $isUnpaid,
                'is_waived'      => $isWaived,
                'item_count'     => $itemCount,
                'payment_count'  => $paymentCount,
                'billed_by_name' => $billedByName,
                'patient_name'   => $patientName,
                'created_by_name' => $baseData['created_by_name'] ?? null,
                'updated_by_name' => $baseData['updated_by_name'] ?? null,
                'created_at'     => $baseData['created_at'] ?? null,
                'updated_at'     => $baseData['updated_at'] ?? null,
            ];

            if ($resource->relationLoaded('items')) {
                $data['items'] = OpdBillItemResource::collection($resource->items)->toArray($request);
            }
            if ($resource->relationLoaded('payments')) {
                $data['payments'] = OpdBillPaymentResource::collection($resource->payments)->toArray($request);
            }

            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
