<?php

namespace App\Http\Controllers;

use App\Validators\OpdVisitValidator;
use App\Repositories\OpdVisitRepository;
use App\Http\Resources\OpdVisitResource;
use App\Traits\Controller\RestControllerTrait;

class OpdVisitController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdVisitRepository $repository, OpdVisitValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdVisitResource::class;
    }

}
