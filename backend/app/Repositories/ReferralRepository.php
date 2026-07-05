<?php

namespace App\Repositories;

use App\Models\Referral;
use App\Services\ODataService;

class ReferralRepository extends BaseRepository
{
    /**
    * @var Referral
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
       $this->model         = new Referral();
        }
 protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }
}
