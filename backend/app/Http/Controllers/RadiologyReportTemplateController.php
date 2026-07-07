<?php

namespace App\Http\Controllers;

use App\Http\Resources\RadiologyReportTemplateResource;
use App\Repositories\RadiologyReportTemplateRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\RadiologyReportTemplateValidator;

class RadiologyReportTemplateController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(RadiologyReportTemplateRepository $repository, RadiologyReportTemplateValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = RadiologyReportTemplateResource::class;
    }
}
