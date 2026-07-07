<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\LabTestReferenceRange;
use App\Validators\LabTestValidator;
use App\Repositories\LabTestRepository;
use App\Http\Resources\LabTestResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabTestController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(LabTestRepository $repository, LabTestValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = LabTestResource::class;
    }

    /** Override show — eager-load parameters + reference ranges. */
    public function show($id)
    {
        try {
            $result = $this->repository->withParameters((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * PUT /lab-test/{id}/parameters — replace every parameter (and each
     * parameter's reference ranges) for a test in one call, same
     * replace-all-on-save convention as OPD prescriptions.
     * Body: { parameters: [{ parameter_name, unit, result_data_type, critical_low?, critical_high?,
     *   reference_ranges: [{ gender, age_min_years, age_max_years?, range_low?, range_high?, range_text? }] }] }
     */
    public function updateParameters(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $labTest = $this->repository->show($id);

            LabTestReferenceRange::query()
                ->whereIn('lab_test_parameter_id', LabTestParameter::query()->where('lab_test_id', $id)->pluck('id'))
                ->delete();
            LabTestParameter::query()->where('lab_test_id', $id)->delete();

            foreach (($request->input('parameters') ?? []) as $i => $param) {
                $parameter = LabTestParameter::query()->create([
                    'organogram_id'    => $labTest->organogram_id,
                    'lab_test_id'      => $id,
                    'parameter_name'   => $param['parameter_name'],
                    'unit'             => $param['unit'] ?? null,
                    'result_data_type' => $param['result_data_type'] ?? 'numeric',
                    'select_options'   => $param['select_options'] ?? null,
                    'critical_low'     => $param['critical_low'] ?? null,
                    'critical_high'    => $param['critical_high'] ?? null,
                    'sequence'         => $i + 1,
                ]);

                foreach (($param['reference_ranges'] ?? []) as $range) {
                    LabTestReferenceRange::query()->create([
                        'organogram_id'         => $labTest->organogram_id,
                        'lab_test_parameter_id' => $parameter->id,
                        'gender'                => $range['gender'] ?? 'all',
                        'age_min_years'         => $range['age_min_years'] ?? 0,
                        'age_max_years'         => $range['age_max_years'] ?? null,
                        'range_low'             => $range['range_low'] ?? null,
                        'range_high'            => $range['range_high'] ?? null,
                        'range_text'            => $range['range_text'] ?? null,
                    ]);
                }
            }

            DB::commit();
            $result = $this->repository->withParameters((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
