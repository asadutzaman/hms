<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Repositories\PatientRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\PatientValidator;

class PatientController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PatientRepository $repository, PatientValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PatientResource::class;
    }
}
