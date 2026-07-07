<?php

namespace App\Http\Resources;

use App\Enums\LabResultFlagEnum;
use App\Repositories\UserRepository;

class LabResultResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $flag = $this->result_flag;

            $enteredByName = '';
            if (!empty($this->entered_by)) {
                $user = (new UserRepository())->getById($this->entered_by);
                $enteredByName = $user->name ?? '';
            }
            $verifiedByName = '';
            if (!empty($this->verified_by)) {
                $user = (new UserRepository())->getById($this->verified_by);
                $verifiedByName = $user->name ?? '';
            }

            return [
                'id'                       => $this->id,
                'uuid'                     => $this->uuid,
                'lab_order_item_id'        => $this->lab_order_item_id,
                'lab_test_parameter_id'    => $this->lab_test_parameter_id,
                'parameter_name_snapshot'  => $this->parameter_name_snapshot,
                'unit_snapshot'            => $this->unit_snapshot,
                'result_value'             => $this->result_value,
                'result_flag'              => $flag,
                'result_flag_label'        => $flag ? LabResultFlagEnum::label($flag) : null,
                'is_critical'              => $flag ? LabResultFlagEnum::isCritical($flag) : false,
                'reference_range_display'  => $this->reference_range_display,
                'verification_status'      => $this->verification_status,
                'entered_by'               => $this->entered_by,
                'entered_by_name'          => $enteredByName,
                'entered_at'               => $this->entered_at,
                'verified_by'              => $this->verified_by,
                'verified_by_name'         => $verifiedByName,
                'verified_at'              => $this->verified_at,
                'remarks'                  => $this->remarks,
                'created_at'               => $baseData['created_at'] ?? null,
                'updated_at'               => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
