<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Resources\AppointmentResource;
use App\Models\Employee;
use App\Models\OpdPrescriptionItem;
use App\Repositories\AppointmentRepository;
use App\Repositories\OpdPrescriptionRepository;
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
    protected $opdPrescriptionRepository;

    public function __construct(
        AppointmentRepository $appointmentRepository,
        OpdVisitRepository $opdVisitRepository,
        OpdPrescriptionRepository $opdPrescriptionRepository
    ) {
        $this->appointmentRepository = $appointmentRepository;
        $this->opdVisitRepository = $opdVisitRepository;
        $this->opdPrescriptionRepository = $opdPrescriptionRepository;
    }

    protected function currentUserId(): int
    {
        return (new SessionService())->init()->getUserId();
    }

    protected function currentDoctorId(): int
    {
        $employee = Employee::query()->where('user_id', $this->currentUserId())->first();

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
                'stats'                   => $this->opdVisitRepository->todayStatsForDoctor($doctorId),
                'week_trend'              => $this->opdVisitRepository->weeklyVisitTrend($doctorId),
                'appointments'            => $todayAppointments->values(),
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

            $visitsByAppointment = $this->opdVisitRepository->byAppointmentIds(
                $appointments->pluck('id')->all()
            );

            $rows = AppointmentResource::collection($appointments)->resolve();
            foreach ($rows as &$row) {
                $visit = $visitsByAppointment->get($row['id'] ?? null);
                $row['opd_visit_id']     = $visit?->id;
                $row['opd_visit_status'] = $visit?->status;
            }
            unset($row);

            return $this->successResponse($rows);
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

    /**
     * GET /doctor-portal/latest-prescription/{patientId}?exclude_visit_id=
     *
     * The patient's most recent prescription, items shaped exactly like
     * PrescriptionTemplateService::apply() so the prescription form can
     * inject them directly ("Copy Previous Rx").
     */
    public function latestPrescription($patientId, Request $request)
    {
        try {
            $this->currentDoctorId();

            $excludeVisitId = $request->query('exclude_visit_id');
            $rx = $this->opdPrescriptionRepository->latestForPatient(
                (int) $patientId,
                $excludeVisitId ? (int) $excludeVisitId : null
            );

            if (!$rx) {
                return $this->successResponse(['prescription' => null, 'items' => []]);
            }

            $items = $rx->items->map(function (OpdPrescriptionItem $item) {
                return [
                    'drug_id'        => $item->drug_id,
                    'drug_name'      => $item->drug_name,
                    'generic_name'   => $item->generic_name,
                    'strength'       => $item->strength,
                    'dosage_form'    => $item->dosage_form,
                    'dose_value'     => $item->dose_value,
                    'dose_unit'      => $item->dose_unit,
                    'frequency'      => $item->frequency,
                    'duration_value' => $item->duration_value,
                    'duration_unit'  => $item->duration_unit,
                    'route'          => $item->route,
                    'instruction'    => $item->instruction,
                    'is_prn'         => $item->is_prn,
                ];
            })->values()->toArray();

            return $this->successResponse([
                'prescription' => [
                    'opd_visit_id'   => $rx->opd_visit_id,
                    'advice'         => $rx->advice,
                    'follow_up_date' => $rx->follow_up_date,
                    'prescribed_at'  => $rx->prescribed_at,
                ],
                'items' => $items,
            ]);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * GET /doctor-portal/recent-drugs
     *
     * Catalog drugs this doctor prescribed most recently — derived from
     * their own prescription items, no favorites table.
     */
    public function recentDrugs()
    {
        try {
            $userId = $this->currentUserId();
            $this->currentDoctorId();

            $drugs = $this->opdPrescriptionRepository->recentDrugsForPrescriber($userId, 10);

            return $this->successResponse(['drugs' => $drugs->values()]);
        } catch (ApiException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
