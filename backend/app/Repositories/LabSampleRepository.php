<?php

namespace App\Repositories;

use App\Models\LabSample;
use App\Services\ODataService;

class LabSampleRepository extends BaseRepository
{
    /**
    * @var LabSample
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['barcode', 'sample_type'];

    public function __construct()
    {
        $this->model = new LabSample();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function findByBarcode(string $barcode): ?LabSample
    {
        return $this->newQuery()->with('order.items')->where('barcode', $barcode)->first();
    }

    public function forOrder(int $orderId)
    {
        return $this->newQuery()->where('lab_order_id', $orderId)->get();
    }
}
