<?php

namespace App\Http\Controllers;

use App\Validators\OpdBillValidator;
use App\Repositories\OpdBillRepository;
use App\Http\Resources\OpdBillResource;
use App\Traits\Controller\RestControllerTrait;

class OpdBillController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdBillRepository $repository, OpdBillValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdBillResource::class;
    }

}
