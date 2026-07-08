<?php

namespace App\Http\Controllers;

use App\Validators\TheatreValidator;
use App\Repositories\TheatreRepository;
use App\Http\Resources\TheatreResource;
use App\Traits\Controller\RestControllerTrait;

class TheatreController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(TheatreRepository $repository, TheatreValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = TheatreResource::class;
    }

}
