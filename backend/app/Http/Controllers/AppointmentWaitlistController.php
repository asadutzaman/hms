<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistStatusEnum;
use App\Exceptions\ValidatorException;
use App\Http\Resources\AppointmentWaitlistResource;
use App\Repositories\AppointmentWaitlistRepository;
use App\Traits\Controller\RestControllerTrait;
use App\Validators\AppointmentWaitlistValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentWaitlistController extends Controller
{
    private $repository;

    private $validator;

    private $resource;

    private $partialUpdateFields = ['status', 'priority', 'notes'];

    use RestControllerTrait;

    public function __construct(AppointmentWaitlistRepository $repository, AppointmentWaitlistValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->resource   = AppointmentWaitlistResource::class;
    }

    /**
     * POST /appointment-waitlist/{id}/notify — mark a waitlist as notified when an opening is offered.
     */
    public function notify(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $waitlist = $this->repository->getById($id);
            if (!$waitlist) {
                $this->errorResponse('Waitlist entry not found.');
            }
            if ($waitlist->status !== WaitlistStatusEnum::WAITING) {
                $this->errorResponse('Only waiting entries can be notified.');
            }

            $oldValues = $waitlist->only(['status', 'notified_at']);
            $waitlist->status      = WaitlistStatusEnum::NOTIFIED;
            $waitlist->notified_at = Carbon::now();
            $waitlist->save();

            DB::commit();
            $response = new $this->resource($waitlist->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }

    /**
     * POST /appointment-waitlist/{id}/convert — mark as converted once patient books a slot.
     */
    public function convert(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'converted_appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            ]);

            $waitlist = $this->repository->getById($id);
            if (!$waitlist) {
                $this->errorResponse('Waitlist entry not found.');
            }

            $oldValues = $waitlist->only(['status', 'converted_appointment_id']);
            $waitlist->status                   = WaitlistStatusEnum::CONVERTED;
            $waitlist->converted_appointment_id = $request->input('converted_appointment_id');
            $waitlist->save();

            DB::commit();
            $response = new $this->resource($waitlist->fresh(), false);
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
     * POST /appointment-waitlist/{id}/expire
     */
    public function expire(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $waitlist = $this->repository->getById($id);
            if (!$waitlist) {
                $this->errorResponse('Waitlist entry not found.');
            }
            if (!in_array($waitlist->status, [WaitlistStatusEnum::WAITING, WaitlistStatusEnum::NOTIFIED], true)) {
                $this->errorResponse('Only waiting/notified entries can expire.');
            }

            $oldValues = $waitlist->only(['status', 'expired_at']);
            $waitlist->status     = WaitlistStatusEnum::EXPIRED;
            $waitlist->expired_at = Carbon::now();
            $waitlist->save();

            DB::commit();
            $response = new $this->resource($waitlist->fresh(), false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse($e->getMessage());
        }
    }
}