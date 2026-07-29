<?php

namespace App\Http\Controllers;

use App\Http\Resources\SoapNoteResource;
use App\Repositories\SoapNoteRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\SoapNoteValidator;

class SoapNoteController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(SoapNoteRepository $repository, SoapNoteValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = SoapNoteResource::class;
    }
}
