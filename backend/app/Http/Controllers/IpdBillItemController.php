<?php

namespace App\Http\Controllers;

use App\Validators\IpdBillItemValidator;
use App\Repositories\IpdBillItemRepository;
use App\Http\Resources\IpdBillItemResource;
use App\Traits\Controller\RestControllerTrait;

class IpdBillItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdBillItemRepository $repository, IpdBillItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdBillItemResource::class;
    }

}
