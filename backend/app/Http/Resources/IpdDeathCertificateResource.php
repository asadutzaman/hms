<?php

namespace App\Http\Resources;

use App\Repositories\UserRepository;

class IpdDeathCertificateResource extends BaseResource
{
    public function toArray($request)
    {
        try {
            $baseData = parent::toArray($request);

            $certifiedByName = '';
            if (!empty($this->certified_by)) {
                $user = (new UserRepository())->getById($this->certified_by);
                $certifiedByName = $user->name ?? '';
            }

            return [
                'id'                           => $this->id,
                'uuid'                         => $this->uuid,
                'admission_id'                 => $this->admission_id,
                'certificate_no'               => $this->certificate_no,
                'date_of_death'                => $this->date_of_death,
                'time_of_death'                => $this->time_of_death,
                'immediate_cause'              => $this->immediate_cause,
                'antecedent_cause'              => $this->antecedent_cause,
                'underlying_cause'              => $this->underlying_cause,
                'other_significant_conditions'  => $this->other_significant_conditions,
                'manner_of_death'               => $this->manner_of_death,
                'is_finalized'                  => (bool) $this->is_finalized,
                'certified_by'                  => $this->certified_by,
                'certified_by_name'             => $certifiedByName,
                'certified_at'                  => $this->certified_at,
                'created_by_name'               => $baseData['created_by_name'] ?? null,
                'updated_by_name'               => $baseData['updated_by_name'] ?? null,
                'created_at'                    => $baseData['created_at'] ?? null,
                'updated_at'                    => $baseData['updated_at'] ?? null,
            ];
        } catch (\Throwable $e) {
            return parent::toArray($request);
        }
    }
}
