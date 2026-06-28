<?php

namespace App\Http\Controllers;

use App\Validators\LabTestValidator;
use App\Repositories\LabTestRepository;
use App\Http\Resources\LabTestResource;
use App\Traits\Controller\RestControllerTrait;

class LabTestController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(LabTestRepository $repository, LabTestValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = LabTestResource::class;
    }

}
