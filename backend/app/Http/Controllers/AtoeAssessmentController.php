<?php

namespace App\Http\Controllers;

use App\Http\Resources\AtoeAssessmentResource;
use App\Repositories\AtoeAssessmentRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\AtoeAssessmentValidator;

class AtoeAssessmentController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(AtoeAssessmentRepository $repository, AtoeAssessmentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = AtoeAssessmentResource::class;
    }
}
