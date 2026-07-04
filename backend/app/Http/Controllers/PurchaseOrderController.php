<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\PurchaseOrderResource;
use App\Repositories\CodeSequenceRepository;
use App\Repositories\PurchaseOrderItemRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\PurchaseOrderValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SessionService;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    private $repository;

    private $purchaseOrderItemRepository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status'];

    use RestControllerTrait;

    public function __construct(PurchaseOrderRepository $repository, PurchaseOrderValidator $validator, PurchaseOrderItemRepository $purchaseOrderItemRepository)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = PurchaseOrderResource::class;
        $this->purchaseOrderItemRepository = $purchaseOrderItemRepository;
    }

    public function store(Request $request)
    {
        /*
         * Step-1: Get PO Latest Code
         * Step-2: Store PO Header
         * Step-3: Store PO Item Data
         * Step-4: Update Code Sequence
        */

        DB::beginTransaction();
        try {
            $formData = $request->all();

            if (isset($this->validator)) {
                $this->validate($request, $this->validator->rules(), $this->validator->messages());
            }

            $latestCodeSequence = (new CodeSequenceRepository())->getLatestCodeByLabel('PURCHASE_ORDER');
            if ($latestCodeSequence == null) {
                $this->errorResponse("Number Sequence not found!");
            }

            $checkDuplicatePoNumber = $this->repository->checkPoNumberUnique($latestCodeSequence);
            if ($checkDuplicatePoNumber > 0) {
                $this->errorResponse("{$latestCodeSequence} - This Number Sequence is already exist!");
            }

            $sessionService = (new SessionService())->init();
            $userData = $sessionService->getUserData();
            if (empty($userData['branch_id'])) {
                $this->errorResponse("User branch not found!");
            }

            $poValueResult = $this->repository->create([
                'po_number'               => $latestCodeSequence,
                'supplier_id'             => $formData['supplier_id'],
                'branch_id'               => $userData['branch_id'],
                'order_date'              => $formData['order_date'],
                'expected_delivery_date'  => $formData['expected_delivery_date'] ?? null,
                'notes'                   => $formData['notes'] ?? null,
                'requisition_id'          => $formData['requisition_id'] ?? null,
                'process_status'          => $formData['process_status'] ?? 'DRAFT',
                'po_status'               => ($formData['process_status'] ?? 'DRAFT') === 'SUBMITTED' ? 'pending_approval' : 'draft',
            ]);

            if (empty($poValueResult)) {
                $this->errorResponse("Purchase Order save fail!");
            }

            $poItemList = !empty($formData['poItemsList']) ? $formData['poItemsList'] : null;
            if ($poItemList) {
                foreach ($poItemList as $item) {
                    $this->purchaseOrderItemRepository->create([
                        'purchase_order_id' => $poValueResult['id'],
                        'item_id'           => $item['item_id'],
                        'unit_price'        => $item['unit_price'] ?? 0,
                        'quantity'          => $item['quantity'],
                        'line_total'        => ($item['unit_price'] ?? 0) * $item['quantity'],
                        'remarks'           => $item['remarks'] ?? null,
                    ]);
                }
            }

            (new CodeSequenceRepository())->updateNextSequenceByLabel('PURCHASE_ORDER');

            DB::commit();
            $response = isset($this->resource) ? new $this->resource($poValueResult) : $poValueResult;
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $formData = $request->all();

            if (isset($this->validator)) {
                $this->validate($request, $this->validator->rules(), $this->validator->messages());
            }

            $this->repository->update([
                'supplier_id'             => $formData['supplier_id'],
                'order_date'              => $formData['order_date'],
                'expected_delivery_date'  => $formData['expected_delivery_date'] ?? null,
                'notes'                   => $formData['notes'] ?? null,
                'process_status'          => $formData['process_status'] ?? 'DRAFT',
                'po_status'               => ($formData['process_status'] ?? 'DRAFT') === 'SUBMITTED' ? 'pending_approval' : 'draft',
            ], $id);

            $poItemListData = !empty($formData['poItemsList']) ? $formData['poItemsList'] : null;
            if (!empty($poItemListData)) {
                $poItemIds = array_column($poItemListData, 'id');
                if (count($poItemIds) > 0) {
                    $this->purchaseOrderItemRepository->deletePoItemByIds($id, $poItemIds);
                }
                foreach ($poItemListData as $item) {
                    $poItemData = [
                        'purchase_order_id' => $id,
                        'item_id'           => $item['item_id'],
                        'unit_price'        => $item['unit_price'] ?? 0,
                        'quantity'          => $item['quantity'],
                        'line_total'        => ($item['unit_price'] ?? 0) * $item['quantity'],
                        'remarks'           => $item['remarks'] ?? null,
                    ];

                    if (!empty($item['id'])) {
                        $this->purchaseOrderItemRepository->update($poItemData, $item['id']);
                    } else {
                        $this->purchaseOrderItemRepository->create($poItemData);
                    }
                }
            }

            DB::commit();
            $result = $this->repository->show($id);
            $response = isset($this->resource) ? new $this->resource($result) : $result;
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /purchase-order/{id}/items-for-grn — approved PO lines with
     * outstanding (not-yet-received) quantity, for pre-filling a GRN raised
     * against this PO.
     */
    public function itemsForGrn($id)
    {
        try {
            $purchaseOrder = $this->repository->findById($id);
            if (!$purchaseOrder) {
                $this->notFoundResponse();
            }

            $items = $this->purchaseOrderItemRepository->newQuery()
                ->with(['itemInfo:id,name_en,name_bn,code'])
                ->where('purchase_order_id', $id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'                => $item->id,
                        'item_id'           => $item->item_id,
                        'name'              => optional($item->itemInfo)->name_en ?? optional($item->itemInfo)->name_bn,
                        'unit_price'        => $item->unit_price,
                        'ordered_quantity'  => $item->quantity,
                        'received_quantity' => $item->received_quantity,
                        'outstanding_quantity' => max(0, (float) $item->quantity - (float) $item->received_quantity),
                    ];
                });

            return $this->successResponse([
                'purchase_order_id' => $purchaseOrder->id,
                'po_number'         => $purchaseOrder->po_number,
                'supplier_id'       => $purchaseOrder->supplier_id,
                'items'             => $items,
            ]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
