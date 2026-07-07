<?php

namespace App\Repositories;

use App\Models\PatientAttachment;
use App\Services\ODataService;

class PatientAttachmentRepository extends BaseRepository
{
    /**
    * @var PatientAttachment
    */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['title', 'description'];

    public function __construct()
    {
        $this->model = new PatientAttachment();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forPatient(int $patientId, ?string $category = null)
    {
        $query = $this->newQuery()->with('file')->where('patient_id', $patientId);
        if ($category) {
            $query->where('category', $category);
        }
        return $query->orderByDesc('uploaded_at')->get();
    }
}
