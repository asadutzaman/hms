<?php

namespace App\Http\Controllers;

use App\Http\Resources\CodeBlueEventResource;
use App\Repositories\CodeBlueEventRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\CodeBlueEventValidator;

class CodeBlueEventController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'state'];

    use RestControllerTrait;

    public function __construct(CodeBlueEventRepository $repository, CodeBlueEventValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = CodeBlueEventResource::class;
    }
}
