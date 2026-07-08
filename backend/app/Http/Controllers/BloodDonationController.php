<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\BloodDonationResource;
use App\Models\BloodDonation;
use App\Services\BloodBank\BloodDonationService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BloodDonationController extends Controller
{
    use TraitRestResponse;

    /** GET /blood-donation?donor_id= */
    public function index(Request $request)
    {
        try {
            $query = BloodDonation::query()->with(['donor', 'units'])->orderByDesc('donation_date');
            if ($request->filled('donor_id')) {
                $query->where('donor_id', $request->input('donor_id'));
            }
            $rows = $query->limit(200)->get();
            $response = BloodDonationResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /blood-donation — Body: { donor_id, donation_date?, volume_ml?, hemoglobin_g_dl?, collected_by?, notes? } */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'donor_id'          => ['required', 'integer', 'exists:blood_donors,id'],
                'donation_date'     => ['nullable', 'date'],
                'volume_ml'         => ['nullable', 'integer', 'min:1'],
                'hemoglobin_g_dl'   => ['nullable', 'numeric'],
                'collected_by'      => ['nullable', 'integer', 'exists:employees,id'],
                'notes'             => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $result = app(BloodDonationService::class)->recordDonation($request->all(), $actorId);

            return $this->successResponse((new BloodDonationResource($result))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
