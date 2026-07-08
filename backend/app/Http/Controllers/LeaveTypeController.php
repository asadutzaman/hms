<?php

namespace App\Http\Controllers;

use App\Validators\LeaveTypeValidator;
use App\Repositories\LeaveTypeRepository;
use App\Http\Resources\LeaveTypeResource;
use App\Traits\Controller\RestControllerTrait;

class LeaveTypeController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(LeaveTypeRepository $repository, LeaveTypeValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = LeaveTypeResource::class;
    }

}
