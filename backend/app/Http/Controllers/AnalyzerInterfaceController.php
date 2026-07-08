<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\LabAnalyzerMessageResource;
use App\Models\LabAnalyzerMessage;
use App\Services\Lis\AnalyzerInterfaceService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyzerInterfaceController extends Controller
{
    use TraitRestResponse;

    /** GET /analyzer-interface/messages — recent inbound analyzer messages (audit log). */
    public function index(Request $request)
    {
        try {
            $rows = LabAnalyzerMessage::query()->orderByDesc('received_at')->limit(100)->get();
            $response = LabAnalyzerMessageResource::collection($rows)->toArray($request);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /analyzer-interface/import — Body: { analyzer_name?, barcode,
     * results: [{ lab_test_parameter_id? or parameter_name, result_value }] }
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'barcode'                    => ['required', 'string'],
                'results'                    => ['required', 'array', 'min:1'],
                'results.*.result_value'     => ['required', 'string'],
            ]);

            $actorId = (new SessionService())->init()->getUserId();
            $message = app(AnalyzerInterfaceService::class)->import($request->all(), $actorId);

            return $this->successResponse((new LabAnalyzerMessageResource($message))->toArray($request));
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
