<?php

namespace App\Http\Controllers;

use App\Validators\NotificationTemplateValidator;
use App\Repositories\NotificationTemplateRepository;
use App\Http\Resources\NotificationTemplateResource;
use App\Traits\Controller\RestControllerTrait;

class NotificationTemplateController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(NotificationTemplateRepository $repository, NotificationTemplateValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = NotificationTemplateResource::class;
    }
}
