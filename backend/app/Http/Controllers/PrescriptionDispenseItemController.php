<?php

namespace App\Http\Controllers;

use App\Validators\PrescriptionDispenseItemValidator;
use App\Repositories\PrescriptionDispenseItemRepository;
use App\Http\Resources\PrescriptionDispenseItemResource;
use App\Traits\Controller\RestControllerTrait;

class PrescriptionDispenseItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PrescriptionDispenseItemRepository $repository, PrescriptionDispenseItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PrescriptionDispenseItemResource::class;
    }

}
