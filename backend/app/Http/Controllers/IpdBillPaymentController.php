<?php

namespace App\Http\Controllers;

use App\Validators\IpdBillPaymentValidator;
use App\Repositories\IpdBillPaymentRepository;
use App\Http\Resources\IpdBillPaymentResource;
use App\Traits\Controller\RestControllerTrait;

class IpdBillPaymentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(IpdBillPaymentRepository $repository, IpdBillPaymentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = IpdBillPaymentResource::class;
    }

}
