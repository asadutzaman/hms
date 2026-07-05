<?php

namespace App\Http\Controllers;

use App\Validators\OpdProcedureValidator;
use App\Repositories\OpdProcedureRepository;
use App\Http\Resources\OpdProcedureResource;
use App\Traits\Controller\RestControllerTrait;

class OpdProcedureController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdProcedureRepository $repository, OpdProcedureValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdProcedureResource::class;
    }

}
