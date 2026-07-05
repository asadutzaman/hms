<?php

namespace App\Http\Controllers;

use App\Validators\DiagnosisTemplateItemValidator;
use App\Repositories\DiagnosisTemplateItemRepository;
use App\Http\Resources\DiagnosisTemplateItemResource;
use App\Traits\Controller\RestControllerTrait;

class DiagnosisTemplateItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(DiagnosisTemplateItemRepository $repository, DiagnosisTemplateItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DiagnosisTemplateItemResource::class;
    }

}
