<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\LabQcLotResource;
use App\Http\Resources\LabQcRunResource;
use App\Models\LabQcLot;
use App\Services\Lis\LabQcService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LabQcController extends Controller
{
    use TraitRestResponse;

    /** GET /lab-qc/lots?lab_test_parameter_id= */
    public function lots(Request $request)
    {
        try {
            $query = LabQcLot::query()->with('parameter');
            if ($request->filled('lab_test_parameter_id')) {
                $query->where('lab_test_parameter_id', $request->input('lab_test_parameter_id'));
            }
            $rows = $query->orderByDesc('created_at')->get();
            $response = LabQcLotResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-qc/lots — Body: { lab_test_parameter_id, lot_number, level?, target_mean, target_sd, expiry_date? } */
    public function createLot(Request $request)
    {
        try {
            $request->validate([
                'lab_test_parameter_id' => ['required', 'integer', 'exists:lab_test_parameters,id'],
                'lot_number'            => ['required', 'string', 'max:255'],
                'level'                 => ['nullable', 'string', 'max:32'],
                'target_mean'           => ['required', 'numeric'],
                'target_sd'             => ['required', 'numeric', 'min:0'],
                'expiry_date'           => ['nullable', 'date'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $lot = app(LabQcService::class)->createLot($request->all(), $actorId);

            return $this->successResponse((new LabQcLotResource($lot))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /lab-qc/lots/{id}/levey-jennings */
    public function leveyJennings($id)
    {
        try {
            $data = app(LabQcService::class)->levelJenningsData((int) $id);
            return $this->successResponse([
                'lot'     => (new LabQcLotResource($data['lot']))->toArray(request()),
                'runs'    => LabQcRunResource::collection($data['runs'])->toArray(request()),
                'summary' => $data['summary'],
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /lab-qc/lots/{id}/runs — Body: { measured_value, run_date?, remarks? } */
    public function recordRun(Request $request, $id)
    {
        try {
            $request->validate([
                'measured_value' => ['required', 'numeric'],
                'run_date'       => ['nullable', 'date'],
                'remarks'        => ['nullable', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $run = app(LabQcService::class)->recordRun((int) $id, $request->all(), $actorId);

            return $this->successResponse((new LabQcRunResource($run))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
