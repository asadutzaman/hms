<?php

namespace App\Repositories;

use App\Models\Drug;
use App\Services\ODataService;

class DrugRepository extends BaseRepository
{
    /**
    * @var Drug
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['generic_name', 'brand_name', 'hsn_code'];

    public function __construct()
    {
        $this->model = new Drug();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Drugs that are themselves generics (no parent mapping) — used to
     * populate the "map to generic" picker in the Drug form.
     */
    public function generics()
    {
        return $this->newQuery()
            ->whereNull('generic_drug_id')
            ->with('item')
            ->get();
    }

    /**
     * All drugs sharing a generic with the given drug — the generic itself
     * plus every branded product mapped to it. If the given drug IS the
     * generic, returns its brands; if it's a brand, returns its siblings.
     */
    public function substitutesFor(int $drugId)
    {
        $drug = $this->newQuery()->with('item')->findOrFail($drugId);
        $genericId = $drug->generic_drug_id ?? $drug->id;

        return $this->newQuery()
            ->with('item')
            ->where('id', '!=', $drugId)
            ->where(function ($q) use ($genericId) {
                $q->where('id', $genericId)
                    ->orWhere('generic_drug_id', $genericId);
            })
            ->get();
    }
}
