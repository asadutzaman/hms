<?php

namespace App\Http\Resources;

use App\Enums\OtBookingStatusEnum;

class OtBookingResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $data = parent::toArray($request);
            $data['booking_status_label'] = OtBookingStatusEnum::label($this->booking_status ?? null);
            return $data;
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
