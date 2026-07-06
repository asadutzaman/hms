<?php

namespace App\Http\Controllers;

use App\Validators\WardValidator;
use App\Repositories\WardRepository;
use App\Http\Resources\WardResource;
use App\Traits\Controller\RestControllerTrait;

class WardController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(WardRepository $repository, WardValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = WardResource::class;
    }

}
