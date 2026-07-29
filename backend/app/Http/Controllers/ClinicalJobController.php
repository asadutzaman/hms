<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicalJobResource;
use App\Repositories\ClinicalJobRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\ClinicalJobValidator;

class ClinicalJobController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'state'];

    use RestControllerTrait;

    public function __construct(ClinicalJobRepository $repository, ClinicalJobValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ClinicalJobResource::class;
    }
}
