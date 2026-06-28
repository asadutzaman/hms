<?php

namespace App\Http\Controllers;

use App\Validators\OpdVitalValidator;
use App\Repositories\OpdVitalRepository;
use App\Http\Resources\OpdVitalResource;
use App\Traits\Controller\RestControllerTrait;

class OpdVitalController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdVitalRepository $repository, OpdVitalValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdVitalResource::class;
    }

}
