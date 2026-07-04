<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\DrugResource;
use App\Repositories\DrugRepository;
use App\Repositories\GoodsReceiveNoteItemRepository;
use App\Repositories\ItemStockRepository;
use App\Services\Inventory\DrugService;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\DrugValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DrugController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(DrugRepository $repository, DrugValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = DrugResource::class;
    }

    public function show($id)
    {
        try {
            $result = $this->repository->newQuery()->with(['item', 'generic'])->findOrFail($id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $result = app(DrugService::class)->create($request->all(), Auth::id());

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $result = app(DrugService::class)->update((int) $id, $request->all(), Auth::id());

            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            throw new ValidatorException($e);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $drug = $this->repository->findById($id);
            if (!$drug) {
                $this->notFoundResponse();
            }

            if ((new GoodsReceiveNoteItemRepository())->exists(['item_id' => $drug->item_id])) {
                $this->errorResponse('This drug is used in a Goods Receive Note and cannot be deleted.');
            }
            if ((new ItemStockRepository())->exists(['item_id' => $drug->item_id])) {
                $this->errorResponse('This drug has stock records and cannot be deleted.');
            }
            if ((new DrugRepository())->newQuery()->where('generic_drug_id', $id)->exists()) {
                $this->errorResponse('This drug is mapped as a generic for other brands and cannot be deleted.');
            }

            $response = $this->repository->delete($id);
            if (!$response) {
                $this->errorResponse();
            }
            DB::commit();
            return $this->deleteResponse();
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /drug/generics — drugs with no generic mapping, for the
     * "map to generic" picker.
     */
    public function generics()
    {
        try {
            $result = $this->repository->generics()->map(function ($drug) {
                return [
                    'id'           => $drug->id,
                    'generic_name' => $drug->generic_name,
                    'name_en'      => $drug->item->name_en ?? null,
                ];
            });
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /drug/{id}/substitutes — sibling brands/generic for this drug.
     */
    public function substitutes($id)
    {
        try {
            $result = $this->repository->substitutesFor((int) $id);
            $response = $this->resource::collection($result);
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /drug/{id}/stock — batch-level stock (quantity, expiry, shelve)
     * for this drug's underlying item, across all branches.
     */
    public function stock($id)
    {
        try {
            $drug = $this->repository->findById($id);
            if (!$drug) {
                $this->notFoundResponse();
            }

            $batches = (new ItemStockRepository())->newQuery()
                ->with(['branchInfo', 'shelveInfo'])
                ->where('item_id', $drug->item_id)
                ->where('balance_quantity', '>', 0)
                ->orderByRaw('expire_date ASC NULLS LAST')
                ->get()
                ->map(function ($stock) {
                    return [
                        'id'               => $stock->id,
                        'branch_name'      => $stock->branchInfo->name ?? null,
                        'shelve_name'      => $stock->shelveInfo->name ?? null,
                        'unit_price'       => $stock->unit_price,
                        'balance_quantity' => $stock->balance_quantity,
                        'expire_date'      => $stock->expire_date,
                    ];
                });

            return $this->successResponse([
                'total_balance' => $batches->sum('balance_quantity'),
                'batches'       => $batches,
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
