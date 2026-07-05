<?php

namespace App\Http\Controllers;

use App\Models\Icd10Code;
use App\Validators\Icd10CodeValidator;
use App\Repositories\Icd10CodeRepository;
use App\Http\Resources\Icd10CodeResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;

class Icd10CodeController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(Icd10CodeRepository $repository, Icd10CodeValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = Icd10CodeResource::class;
    }

    /**
     * GET /icd10-code/search?q=fever
     */
    public function search(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));

            $results = Icd10Code::query()
                ->where('status', 1)
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($sub) use ($q) {
                        $sub->where('code', 'ILIKE', "%{$q}%")
                            ->orWhere('description', 'ILIKE', "%{$q}%");
                    });
                })
                ->orderBy('code')
                ->limit(50)
                ->get();

            return $this->successResourceResponse(Icd10CodeResource::collection($results));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
