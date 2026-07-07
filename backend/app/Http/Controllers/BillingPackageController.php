<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillingPackageResource;
use App\Models\BillingPackageItem;
use App\Repositories\BillingPackageRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\BillingPackageValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingPackageController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'is_active'];

    use RestControllerTrait;

    public function __construct(BillingPackageRepository $repository, BillingPackageValidator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->resource = BillingPackageResource::class;
    }

    /** Override show — eager-load package inclusions. */
    public function show($id)
    {
        try {
            $result = $this->repository->withItems((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * PUT /billing-package/{id}/items — replace-all-on-save the package's
     * inclusion list, same convention as LabTestController::updateParameters().
     * Body: { items: [{ item_type, description, default_quantity?, notional_unit_price? }] }
     */
    public function updateItems(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $package = $this->repository->show($id);

            BillingPackageItem::query()->where('billing_package_id', $id)->delete();

            foreach (($request->input('items') ?? []) as $i => $item) {
                BillingPackageItem::query()->create([
                    'organogram_id'       => $package->organogram_id,
                    'billing_package_id'  => $id,
                    'item_type'           => $item['item_type'] ?? 'other',
                    'description'         => $item['description'],
                    'default_quantity'    => $item['default_quantity'] ?? 1,
                    'notional_unit_price' => $item['notional_unit_price'] ?? null,
                    'sequence'            => $i + 1,
                ]);
            }

            DB::commit();
            $result = $this->repository->withItems((int) $id);
            $response = new $this->resource($result, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}
