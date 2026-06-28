<?php

namespace App\Http\Controllers;

use App\Validators\OpdInvestigationOrderItemValidator;
use App\Repositories\OpdInvestigationOrderItemRepository;
use App\Http\Resources\OpdInvestigationOrderItemResource;
use App\Traits\Controller\RestControllerTrait;

class OpdInvestigationOrderItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdInvestigationOrderItemRepository $repository, OpdInvestigationOrderItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdInvestigationOrderItemResource::class;
    }

}
