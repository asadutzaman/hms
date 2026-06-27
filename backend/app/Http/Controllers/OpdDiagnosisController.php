<?php

namespace App\Http\Controllers;

use App\Validators\OpdDiagnosisValidator;
use App\Repositories\OpdDiagnosisRepository;
use App\Http\Resources\OpdDiagnosisResource;
use App\Traits\Controller\RestControllerTrait;

class OpdDiagnosisController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdDiagnosisRepository $repository, OpdDiagnosisValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdDiagnosisResource::class;
    }

}
