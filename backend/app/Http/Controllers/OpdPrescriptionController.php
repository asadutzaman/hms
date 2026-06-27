<?php

namespace App\Http\Controllers;

use App\Validators\OpdPrescriptionValidator;
use App\Repositories\OpdPrescriptionRepository;
use App\Http\Resources\OpdPrescriptionResource;
use App\Traits\Controller\RestControllerTrait;

class OpdPrescriptionController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdPrescriptionRepository $repository, OpdPrescriptionValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdPrescriptionResource::class;
    }

}
