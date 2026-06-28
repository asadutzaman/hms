<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Models\AppointmentAuditLog;
use App\Services\ODataService;

class AppointmentRepository extends BaseRepository
{
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['appointment_no', 'reason_for_visit', 'symptoms'];

    public function __construct()
    {
        $this->model = new Appointment();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function todayForDoctor(int $doctorId)
    {
        return $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', now()->toDateString())
            ->whereIn('status', ['confirmed', 'checked_in', 'in_consultation'])
            ->orderBy('appointment_at')
            ->get();
    }

    public function queueForDoctorAndDate(int $doctorId, string $date)
    {
        return $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['confirmed', 'checked_in', 'in_consultation'])
            ->orderBy('token_number')
            ->orderBy('appointment_at')
            ->get();
    }

    public function upcomingForPatient(int $patientId, int $limit = 5)
    {
        return $this->newQuery()
            ->where('patient_id', $patientId)
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['confirmed', 'pending', 'checked_in', 'waitlisted'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function logAudit(
        int $appointmentId,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $payload = [],
        ?string $remarks = null,
        string $actorType = 'user',
        ?int $actorId = null,
    ): AppointmentAuditLog {
        return AppointmentAuditLog::query()->create([
            'appointment_id' => $appointmentId,
            'patient_id'     => optional(Appointment::find($appointmentId))->patient_id,
            'doctor_id'      => optional(Appointment::find($appointmentId))->doctor_id,
            'action'         => $action,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'payload'        => $payload,
            'remarks'        => $remarks,
            'actor_type'     => $actorType,
            'actor_id'       => $actorId,
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
            'occurred_at'    => now(),
        ]);
    }
}
