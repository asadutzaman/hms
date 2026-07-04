<?php

namespace App\Services\Inventory;

use App\Enums\ItemTypeEnum;
use App\Exceptions\ApiException;
use App\Models\Drug;
use App\Models\Item;
use App\Repositories\CodeSequenceRepository;
use App\Repositories\DrugRepository;
use App\Repositories\ItemRepository;
use Illuminate\Support\Facades\DB;

class DrugService
{
    protected $drugRepo;
    protected $itemRepo;

    public function __construct(DrugRepository $drugRepo, ItemRepository $itemRepo)
    {
        $this->drugRepo = $drugRepo;
        $this->itemRepo = $itemRepo;
    }

    /**
     * Create the underlying Item (product master record) and its paired
     * Drug (pharma-specific) row in one transaction, so every drug is also
     * a first-class Item usable by GRN/Requisition/Stock Adjustment/Transfer
     * without any changes to those flows.
     */
    public function create(array $data, int $actorId): Drug
    {
        return DB::transaction(function () use ($data, $actorId) {
            $latestCode = (new CodeSequenceRepository())->getLatestCodeByLabel('ITEM');
            if ($latestCode === null) {
                throw new ApiException('Item code sequence not configured. Please seed the ITEM code sequence.', 422);
            }

            if ($this->itemRepo->checkItemCodeUnique($latestCode) > 0) {
                throw new ApiException("{$latestCode} - This code sequence already exists.", 422);
            }
            if ($this->itemRepo->checkItemNameCodeUnique($data['name_en']) > 0) {
                throw new ApiException("{$data['name_en']} - This name already exists.", 422);
            }

            $item = $this->itemRepo->create([
                'type'             => ItemTypeEnum::CONSUMABLE->value,
                'logistic_id'      => $data['logistic_id'],
                'item_category_id' => $data['item_category_id'],
                'brand_id'         => $data['brand_id'],
                'base_unit_id'     => $data['base_unit_id'],
                'code'             => $latestCode,
                'name_en'          => $data['name_en'],
                'name_bn'          => $data['name_bn'],
                'description'      => $data['description'] ?? null,
                'reorder_qty'      => $data['reorder_qty'],
                'created_by'       => $actorId,
                'updated_by'       => $actorId,
            ]);

            $drug = $this->drugRepo->create([
                'item_id'             => $item->id,
                'generic_name'        => $data['generic_name'],
                'brand_name'          => $data['brand_name'] ?? null,
                'strength'            => $data['strength'] ?? null,
                'dosage_form'         => $data['dosage_form'],
                'hsn_code'            => $data['hsn_code'] ?? null,
                'is_controlled'       => (bool) ($data['is_controlled'] ?? false),
                'controlled_schedule' => $data['controlled_schedule'] ?? null,
                'generic_drug_id'     => $data['generic_drug_id'] ?? null,
                'created_by'          => $actorId,
                'updated_by'          => $actorId,
            ]);

            (new CodeSequenceRepository())->updateNextSequenceByLabel('ITEM');

            return $drug->fresh(['item']);
        });
    }

    public function update(int $drugId, array $data, int $actorId): Drug
    {
        return DB::transaction(function () use ($drugId, $data, $actorId) {
            $drug = $this->drugRepo->newQuery()->lockForUpdate()->find($drugId);
            if (!$drug) {
                throw new ApiException('Drug not found.', 404);
            }

            if (!empty($data['generic_drug_id']) && (int) $data['generic_drug_id'] === $drugId) {
                throw new ApiException('A drug cannot be mapped to itself as a generic.', 422);
            }

            if ($this->itemRepo->checkItemNameCodeUnique($data['name_en'], $drug->item_id) > 0) {
                throw new ApiException("{$data['name_en']} - This name already exists.", 422);
            }

            $this->itemRepo->update([
                'logistic_id'      => $data['logistic_id'],
                'item_category_id' => $data['item_category_id'],
                'brand_id'         => $data['brand_id'],
                'base_unit_id'     => $data['base_unit_id'],
                'name_en'          => $data['name_en'],
                'name_bn'          => $data['name_bn'],
                'description'      => $data['description'] ?? null,
                'reorder_qty'      => $data['reorder_qty'],
                'updated_by'       => $actorId,
            ], $drug->item_id);

            $this->drugRepo->update([
                'generic_name'        => $data['generic_name'],
                'brand_name'          => $data['brand_name'] ?? null,
                'strength'            => $data['strength'] ?? null,
                'dosage_form'         => $data['dosage_form'],
                'hsn_code'            => $data['hsn_code'] ?? null,
                'is_controlled'       => (bool) ($data['is_controlled'] ?? false),
                'controlled_schedule' => $data['controlled_schedule'] ?? null,
                'generic_drug_id'     => $data['generic_drug_id'] ?? null,
                'updated_by'          => $actorId,
            ], $drugId);

            return $this->drugRepo->newQuery()->with('item')->findOrFail($drugId);
        });
    }
}
