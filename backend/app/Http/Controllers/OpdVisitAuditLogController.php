<?php

namespace App\Http\Controllers;

use App\Validators\OpdVisitAuditLogValidator;
use App\Repositories\OpdVisitAuditLogRepository;
use App\Http\Resources\OpdVisitAuditLogResource;
use App\Traits\Controller\RestControllerTrait;

class OpdVisitAuditLogController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(OpdVisitAuditLogRepository $repository, OpdVisitAuditLogValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = OpdVisitAuditLogResource::class;
    }

}
