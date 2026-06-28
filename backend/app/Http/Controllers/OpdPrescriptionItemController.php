<?php

namespace App\Http\Controllers;

use App\Validators\OpdPrescriptionItemValidator;
use App\Repositories\OpdPrescriptionItemRepository;
use App\Http\Resources\OpdPrescriptionItemResource;
use App\Traits\Controller\RestControllerTrait;

class OpdPrescriptionItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdPrescriptionItemRepository $repository, OpdPrescriptionItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdPrescriptionItemResource::class;
    }

}
