<?php

namespace App\Http\Controllers;

use App\Validators\ReferralValidator;
use App\Repositories\ReferralRepository;
use App\Http\Resources\ReferralResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(ReferralRepository $repository, ReferralValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = ReferralResource::class;
    }

    /**
     * PATCH /referral/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $this->validate($request, ['referral_status' => ['required', 'string', 'in:pending,accepted,completed,cancelled']]);
            $result = $this->repository->update(['referral_status' => $request->input('referral_status')], $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
