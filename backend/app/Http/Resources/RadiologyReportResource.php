<?php

namespace App\Http\Resources;

use App\Enums\RadReportStatusEnum;
use App\Repositories\UserRepository;

class RadiologyReportResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);
            $status = $this->report_status;

            $reportedByName = '';
            if (!empty($this->reported_by)) {
                $user = (new UserRepository())->getById($this->reported_by);
                $reportedByName = $user->name ?? '';
            }
            $verifiedByName = '';
            if (!empty($this->verified_by)) {
                $user = (new UserRepository())->getById($this->verified_by);
                $verifiedByName = $user->name ?? '';
            }

            return [
                'id'                            => $this->id,
                'uuid'                          => $this->uuid,
                'radiology_order_item_id'       => $this->radiology_order_item_id,
                'radiology_report_template_id'  => $this->radiology_report_template_id,
                'findings'                      => $this->findings,
                'impression'                    => $this->impression,
                'report_status'                 => $status,
                'report_status_label'           => RadReportStatusEnum::label($status),
                'reported_by'                   => $this->reported_by,
                'reported_by_name'              => $reportedByName,
                'reported_at'                   => $this->reported_at,
                'verified_by'                   => $this->verified_by,
                'verified_by_name'              => $verifiedByName,
                'verified_at'                   => $this->verified_at,
                'created_at'                    => $baseData['created_at'] ?? null,
                'updated_at'                    => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
