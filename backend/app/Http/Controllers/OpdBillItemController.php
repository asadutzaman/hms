<?php

namespace App\Http\Controllers;

use App\Validators\OpdBillItemValidator;
use App\Repositories\OpdBillItemRepository;
use App\Http\Resources\OpdBillItemResource;
use App\Traits\Controller\RestControllerTrait;

class OpdBillItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdBillItemRepository $repository, OpdBillItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdBillItemResource::class;
    }

}
