<?php

namespace App\Http\Controllers;

use App\Validators\PurchaseOrderItemValidator;
use App\Repositories\PurchaseOrderItemRepository;
use App\Http\Resources\PurchaseOrderItemResource;
use App\Traits\Controller\RestControllerTrait;

class PurchaseOrderItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PurchaseOrderItemRepository $repository, PurchaseOrderItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PurchaseOrderItemResource::class;
    }

}
