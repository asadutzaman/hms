<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurgeryNoteResource;
use App\Services\Ot\SurgeryNoteService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

class SurgeryNoteController extends Controller
{
    use TraitRestResponse;

    /** GET /surgery-note/booking/{otBookingId} */
    public function show($otBookingId)
    {
        try {
            $note = app(SurgeryNoteService::class)->getOrCreate((int) $otBookingId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray(request()));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function savePreOp(Request $request, $otBookingId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->savePreOpNotes((int) $otBookingId, $request->input('pre_op_notes'), $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function signIn(Request $request, $otBookingId)
    {
        try {
            $request->validate(['checklist' => ['required', 'array']]);
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->signInChecklist((int) $otBookingId, $request->input('checklist'), $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function timeOut(Request $request, $otBookingId)
    {
        try {
            $request->validate(['checklist' => ['required', 'array']]);
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->timeOutChecklist((int) $otBookingId, $request->input('checklist'), $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function signOut(Request $request, $otBookingId)
    {
        try {
            $request->validate(['checklist' => ['required', 'array']]);
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->signOutChecklist((int) $otBookingId, $request->input('checklist'), $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function saveOpNotes(Request $request, $otBookingId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->recordOpNotes((int) $otBookingId, $request->all(), $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function surgeonSign($otBookingId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $note = app(SurgeryNoteService::class)->surgeonSign((int) $otBookingId, $actorId);
            return $this->successResponse((new SurgeryNoteResource($note))->toArray(request()));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
