<?php

namespace App\Repositories;

use App\Models\OpdPrescriptionItem;
use App\Services\ODataService;

class OpdPrescriptionItemRepository extends BaseRepository
{
    /**
     * @var OpdPrescriptionItem
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'drug_name',
        'generic_name',
        'frequency',
        'route',
    ];

    public function __construct()
    {
        $this->model = new OpdPrescriptionItem();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forPrescription(int $prescriptionId)
    {
        return $this->newQuery()
            ->where('opd_prescription_id', $prescriptionId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }
}
