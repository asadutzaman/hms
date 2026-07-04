<?php

namespace App\Repositories;

use App\Models\VendorQuote;
use App\Services\ODataService;

class VendorQuoteRepository extends BaseRepository
{
    /**
    * @var VendorQuote
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['notes'];

    public function __construct()
    {
        $this->model = new VendorQuote();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Quotes for a set of items, grouped by item, each with its supplier
     * quotes attached — the shape the comparison grid needs.
     */
    public function getComparison(array $itemIds)
    {
        return $this->newQuery()
            ->with(['supplier:id,supplier_name', 'itemInfo:id,name_en,name_bn,code'])
            ->whereIn('item_id', $itemIds)
            ->orderBy('item_id')
            ->orderBy('quoted_unit_price', 'asc')
            ->get()
            ->groupBy('item_id');
    }
}
