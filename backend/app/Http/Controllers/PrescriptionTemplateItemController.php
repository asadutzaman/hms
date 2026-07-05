<?php

namespace App\Http\Controllers;

use App\Validators\PrescriptionTemplateItemValidator;
use App\Repositories\PrescriptionTemplateItemRepository;
use App\Http\Resources\PrescriptionTemplateItemResource;
use App\Traits\Controller\RestControllerTrait;

class PrescriptionTemplateItemController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PrescriptionTemplateItemRepository $repository, PrescriptionTemplateItemValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PrescriptionTemplateItemResource::class;
    }

}
