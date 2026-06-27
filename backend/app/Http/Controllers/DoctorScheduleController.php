<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\DoctorScheduleSlot;
use App\Repositories\DoctorScheduleRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\DoctorScheduleValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DoctorScheduleController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'remarks'];

    use RestControllerTrait;

    public function __construct(DoctorScheduleRepository $repository, DoctorScheduleValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = DoctorScheduleResource::class;
    }

    /**
     * Override store to also persist nested slots in one transaction.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validator->rules(), $this->validator->messages(), $this->validator->attributes());

            $data = $request->all();
            $slots = $data['slots'] ?? [];
            unset($data['slots']);

            $schedule = $this->repository->create($data);
            if (empty($schedule)) {
                $this->errorResponse('Failed to create doctor schedule.');
            }

            // Persist nested slots if provided
            if (!empty($slots) && is_array($slots)) {
                foreach ($slots as $slot) {
                    DoctorScheduleSlot::create([
                        'schedule_id'            => $schedule->id,
                        'day_of_week'            => $slot['day_of_week'],
                        'start_time'             => $slot['start_time'],
                        'end_time'               => $slot['end_time'],
                        'slot_duration_minutes'  => $slot['slot_duration_minutes'] ?? ($schedule->slot_duration_minutes ?? 15),
                        'max_patients_per_slot'  => $slot['max_patients_per_slot'] ?? ($schedule->max_patients_per_slot ?? 1),
                        'consultation_fee'       => $slot['consultation_fee'] ?? ($schedule->consultation_fee ?? 0),
                    ]);
                }
            }

            DB::commit();
            $schedule->load('slots');
            $response = new $this->resource($schedule, false);
            return $this->successResourceResponse($response);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Custom action: generate materialized slots for a given date range.
     * POST /doctor-schedule/{id}/generate-slots
     * Body: { from_date: 'YYYY-MM-DD', to_date: 'YYYY-MM-DD' }
     */
    public function generateSlots(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'from_date' => ['required', 'date'],
                'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
            ]);

            $schedule = $this->repository->getById($id);
            if (!$schedule) {
                $this->errorResponse('Schedule not found.');
            }

            $generated = $this->repository->generateSlotsForDateRange(
                $id,
                $request->input('from_date'),
                $request->input('to_date')
            );

            DB::commit();
            return $this->successResponse([
                'schedule_id'    => $id,
                'from_date'      => $request->input('from_date'),
                'to_date'        => $request->input('to_date'),
                'slots_generated'=> $generated,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new ValidatorException($e);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Custom action: get available slots for a doctor on a given date.
     * GET /doctor-schedule/available-slots?doctor_id=&date=
     */
    public function availableSlots(Request $request)
    {
        try {
            $request->validate([
                'doctor_id' => ['required', 'integer'],
                'date'      => ['required', 'date'],
            ]);

            $slots = $this->repository->getAvailableSlots(
                $request->input('doctor_id'),
                $request->input('date')
            );

            return $this->successResponse($slots);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}