<?php

namespace App\Http\Controllers;

use App\Validators\OpdBillPaymentValidator;
use App\Repositories\OpdBillPaymentRepository;
use App\Http\Resources\OpdBillPaymentResource;
use App\Traits\Controller\RestControllerTrait;

class OpdBillPaymentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdBillPaymentRepository $repository, OpdBillPaymentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdBillPaymentResource::class;
    }

}
