<?php

namespace App\Http\Controllers;

use App\Http\Resources\DailyReviewResource;
use App\Repositories\DailyReviewRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\DailyReviewValidator;

class DailyReviewController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(DailyReviewRepository $repository, DailyReviewValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DailyReviewResource::class;
    }
}
