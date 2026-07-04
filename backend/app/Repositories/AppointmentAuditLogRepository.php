<?php

namespace App\Repositories;

use App\Models\AppointmentAuditLog;

class AppointmentAuditLogRepository extends BaseRepository
{
    public function __construct(AppointmentAuditLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all audit logs for an appointment, newest first.
     */
    public function getByAppointmentId(int $appointmentId)
    {
        return $this->model
            ->where('appointment_id', $appointmentId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }
}