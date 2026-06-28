<?php

namespace App\Http\Controllers;

use App\Validators\OpdInvestigationOrderValidator;
use App\Repositories\OpdInvestigationOrderRepository;
use App\Http\Resources\OpdInvestigationOrderResource;
use App\Traits\Controller\RestControllerTrait;

class OpdInvestigationOrderController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdInvestigationOrderRepository $repository, OpdInvestigationOrderValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdInvestigationOrderResource::class;
    }

}
