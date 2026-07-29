<?php

namespace App\Http\Controllers;

use App\Http\Resources\BleepResource;
use App\Repositories\BleepRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\BleepValidator;

class BleepController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'state'];

    use RestControllerTrait;

    public function __construct(BleepRepository $repository, BleepValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = BleepResource::class;
    }
}
