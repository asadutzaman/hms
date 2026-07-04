<?php

namespace App\Repositories;

use App\Models\PatientAuditLog;

class PatientAuditLogRepository extends BaseRepository
{
    public function __construct(PatientAuditLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all audit logs for a patient, newest first.
     */
    public function getByPatientId(int $patientId)
    {
        return $this->model
            ->where('patient_id', $patientId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }
}
