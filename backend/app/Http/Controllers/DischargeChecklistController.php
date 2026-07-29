<?php

namespace App\Http\Controllers;

use App\Http\Resources\DischargeChecklistResource;
use App\Repositories\DischargeChecklistRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\DischargeChecklistValidator;

class DischargeChecklistController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'state'];

    use RestControllerTrait;

    public function __construct(DischargeChecklistRepository $repository, DischargeChecklistValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DischargeChecklistResource::class;
    }
}
