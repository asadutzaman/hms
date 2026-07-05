<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Resources\AppointmentResource;
use App\Models\Employee;
use App\Repositories\AppointmentRepository;
use App\Repositories\OpdVisitRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;
use Illuminate\Http\Request;

/**
 * Thin read-mostly controller for the Doctor Portal — every action resolves
 * the logged-in user's own Employee row and delegates to the existing
 * Appointment/OpdVisit repositories filtered by that doctor_id, rather than
 * duplicating their query logic.
 */
class DoctorPortalController extends Controller
{
    use RestControllerTrait;

    protected $appointmentRepository;
    protected $opdVisitRepository;

    public function __construct(AppointmentRepository $appointmentRepository, OpdVisitRepository $opdVisitRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->opdVisitRepository = $opdVisitRepository;
    }

    protected function currentDoctorId(): int
    {
        $userId = (new SessionService())->init()->getUserId();
        $employee = Employee::query()->where('user_id', $userId)->first();

        if (!$employee) {
            throw new ApiException('No employee/doctor profile is linked to the current user.', 422);
        }

        return $employee->id;
    }

    /**
     * GET /doctor-portal/dashboard
     */
    public function dashboard()
    {
        try {
            $doctorId = $this->currentDoctorId();

            $todayAppointments = $this->appointmentRepository->todayForDoctor($doctorId);
            $queue = $this->opdVisitRepository->todayQueue(null, $doctorId);

            return $this->successResponse([
                'today_appointment_count' => $todayAppointments->count(),
                'queue_count'             => $queue->count(),
                'queue'                   => $queue->values(),
            ]);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /doctor-portal/appointments?date=YYYY-MM-DD
     */
    public function appointments(Request $request)
    {
        try {
            $doctorId = $this->currentDoctorId();
            $date = $request->query('date', now()->toDateString());

            $appointments = $this->appointmentRepository->queueForDoctorAndDate($doctorId, $date);

            return $this->successResourceResponse(
                AppointmentResource::collection($appointments)
            );
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /doctor-portal/patient-history/{patientId}
     */
    public function patientHistory($patientId)
    {
        try {
            $this->currentDoctorId();

            $visits = $this->opdVisitRepository->visitsForPatient((int) $patientId, 50);
            $visits->load(['diagnoses', 'prescription.items']);

            return $this->successResponse(['visits' => $visits]);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
