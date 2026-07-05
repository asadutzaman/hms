<?php

namespace App\Repositories;

use App\Models\DrugInteraction;
use App\Services\ODataService;

class DrugInteractionRepository extends BaseRepository
{
    /**
    * @var DrugInteraction
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['description'];

    public function __construct()
    {
        $this->model = new DrugInteraction();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Return every interaction pair among the given drug ids (checked both
     * orderings, since a pair is stored once with drug_a_id < drug_b_id).
     */
    public function checkForDrugs(array $drugIds): array
    {
        $drugIds = array_values(array_unique(array_map('intval', $drugIds)));
        if (count($drugIds) < 2) {
            return [];
        }

        return $this->newQuery()
            ->with(['drugA.item', 'drugB.item'])
            ->where(function ($query) use ($drugIds) {
                $query->whereIn('drug_a_id', $drugIds)
                    ->whereIn('drug_b_id', $drugIds);
            })
            ->get()
            ->toArray();
    }
}
