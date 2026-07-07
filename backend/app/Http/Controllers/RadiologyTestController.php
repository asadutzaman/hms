<?php

namespace App\Http\Controllers;

use App\Http\Resources\RadiologyTestResource;
use App\Repositories\RadiologyTestRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\RadiologyTestValidator;

class RadiologyTestController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(RadiologyTestRepository $repository, RadiologyTestValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = RadiologyTestResource::class;
    }
}
