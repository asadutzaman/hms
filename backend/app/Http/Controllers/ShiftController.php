<?php

namespace App\Http\Controllers;

use App\Validators\ShiftValidator;
use App\Repositories\ShiftRepository;
use App\Http\Resources\ShiftResource;
use App\Traits\Controller\RestControllerTrait;

class ShiftController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(ShiftRepository $repository, ShiftValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ShiftResource::class;
    }

}
