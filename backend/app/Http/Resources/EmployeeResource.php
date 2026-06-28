<?php

namespace App\Http\Resources;

use App\Repositories\DesignationRepository;
use App\Repositories\OrganizationRepository;
use App\Services\ApiHelperService;
use App\Services\ArrayService;
use GuzzleHttp\Promise;

class EmployeeResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $includesData = [];

        $organizationRepository = new OrganizationRepository();

        if (isset($data['organization_id'])) {
            $organizationInfo = $organizationRepository->getById($data['organization_id']);
            $includesData['organization_en'] = $organizationInfo->name_en ?? '';
            $includesData['organization_bn'] = $organizationInfo->name_bn ?? '';
        }

        if (!empty($data['designation_id'])) {
            $designationRepository = new DesignationRepository();
            $designation = $designationRepository->getById($data['designation_id']);
            $includesData['designation_name'] = $designation->title ?? '';
        }

        return array_merge($data, $includesData);
    }

    public static function withApiRelationalData($resource)
    {
        return $resource;
    }
}
