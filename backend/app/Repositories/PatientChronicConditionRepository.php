<?php

namespace App\Repositories;

use App\Models\PatientChronicCondition;

class PatientChronicConditionRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['condition_name', 'condition_status'];

    public function __construct(PatientChronicCondition $model)
    {
        $this->model = $model;
    }

    public function withRelations(int $id)
    {
        return $this->newQuery()->with(['icd10Code', 'readings'])->find($id);
    }

    public function forPatient(int $patientId)
    {
        return $this->newQuery()
            ->with(['icd10Code', 'readings'])
            ->where('patient_id', $patientId)
            ->orderByDesc('created_at')
            ->get();
    }
}
