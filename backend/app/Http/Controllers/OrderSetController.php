<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderSetResource;
use App\Repositories\OrderSetRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\OrderSetValidator;

class OrderSetController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OrderSetRepository $repository, OrderSetValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OrderSetResource::class;
    }
}
