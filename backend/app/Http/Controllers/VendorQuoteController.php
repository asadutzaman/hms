<?php

namespace App\Http\Controllers;

use App\Repositories\RateContractRepository;
use App\Validators\VendorQuoteValidator;
use App\Repositories\VendorQuoteRepository;
use App\Http\Resources\VendorQuoteResource;
use App\Http\Resources\RateContractResource;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorQuoteController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(VendorQuoteRepository $repository, VendorQuoteValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = VendorQuoteResource::class;
    }

    /**
     * GET /vendor-quote/compare?item_ids[]=1&item_ids[]=2
     * Groups quotes by item so the frontend can render an item x supplier grid.
     */
    public function compare(Request $request)
    {
        try {
            $itemIds = $request->query('item_ids', []);
            if (empty($itemIds)) {
                return $this->successResponse(['comparison' => []]);
            }

            $grouped = $this->repository->getComparison(array_map('intval', $itemIds));
            $comparison = $grouped->map(function ($quotes, $itemId) {
                return [
                    'item_id'   => (int) $itemId,
                    'item_name' => optional($quotes->first()->itemInfo)->name_en,
                    'item_code' => optional($quotes->first()->itemInfo)->code,
                    'quotes'    => VendorQuoteResource::collection($quotes),
                ];
            })->values();

            return $this->successResponse(['comparison' => $comparison]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /vendor-quote/{id}/select-winner
     * Marks this quote as the winner for its item (unselecting siblings),
     * and optionally creates a Rate Contract from it.
     */
    public function selectWinner(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $quote = $this->repository->findById($id);
            if (!$quote) {
                $this->notFoundResponse();
            }

            $this->repository->newQuery()
                ->where('item_id', $quote->item_id)
                ->update(['is_selected' => false]);
            $this->repository->update(['is_selected' => true], $id);

            $rateContract = null;
            if ($request->boolean('create_rate_contract')) {
                $request->validate([
                    'valid_from' => ['required', 'date'],
                    'valid_to'   => ['required', 'date', 'after:valid_from'],
                ]);

                $rateContractRepository = new RateContractRepository();
                $rateContract = $rateContractRepository->create([
                    'supplier_id'     => $quote->supplier_id,
                    'item_id'         => $quote->item_id,
                    'vendor_quote_id' => $quote->id,
                    'contract_price'  => $quote->quoted_unit_price,
                    'valid_from'      => $request->valid_from,
                    'valid_to'        => $request->valid_to,
                    'contract_status' => 'pending_approval',
                    'process_status'  => 'DRAFT',
                ]);
            }

            DB::commit();
            return $this->successResponse([
                'quote'          => new VendorQuoteResource($this->repository->findById($id), false),
                'rate_contract'  => $rateContract ? new RateContractResource($rateContract, false) : null,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
