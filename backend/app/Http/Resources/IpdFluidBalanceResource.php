<?php

namespace App\Http\Resources;

use App\Enums\IpdFluidBalanceTypeEnum;

class IpdFluidBalanceResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $type = $this->balance_type;

            return [
                'id'                => $this->id,
                'uuid'              => $this->uuid,
                'admission_id'      => $this->admission_id,
                'balance_type'      => $type,
                'balance_type_label' => $type ? IpdFluidBalanceTypeEnum::label($type) : null,
                'category'          => $this->category,
                'amount_ml'         => $this->amount_ml,
                'shift'             => $this->shift,
                'recorded_at'       => $this->recorded_at,
                'notes'             => $this->notes,
                'created_by_name'   => $baseData['created_by_name'] ?? null,
                'updated_by_name'   => $baseData['updated_by_name'] ?? null,
                'created_at'        => $baseData['created_at'] ?? null,
                'updated_at'        => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
