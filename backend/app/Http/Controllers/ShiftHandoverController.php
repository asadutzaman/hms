<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShiftHandoverResource;
use App\Repositories\ShiftHandoverRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\ShiftHandoverValidator;

class ShiftHandoverController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'state'];

    use RestControllerTrait;

    public function __construct(ShiftHandoverRepository $repository, ShiftHandoverValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ShiftHandoverResource::class;
    }
}
