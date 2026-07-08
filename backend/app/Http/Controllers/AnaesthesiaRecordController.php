<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnaesthesiaRecordResource;
use App\Repositories\AnaesthesiaRecordRepository;
use App\Services\Ot\AnaesthesiaRecordService;
use App\Services\SessionService;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

class AnaesthesiaRecordController extends Controller
{
    use TraitRestResponse;

    public function __construct(private AnaesthesiaRecordRepository $repository)
    {
    }

    /** GET /anaesthesia-record/booking/{otBookingId} */
    public function show($otBookingId)
    {
        try {
            $record = $this->repository->forBooking((int) $otBookingId);
            if (!$record) {
                return $this->successResponse(null);
            }
            return $this->successResponse((new AnaesthesiaRecordResource($record))->toArray(request()));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /anaesthesia-record/booking/{otBookingId} — start/get the record header */
    public function store(Request $request, $otBookingId)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $record = app(AnaesthesiaRecordService::class)->getOrCreate((int) $otBookingId, $request->all(), $actorId);
            return $this->successResponse((new AnaesthesiaRecordResource($record))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** POST /anaesthesia-record/{id}/entry — add a periodic chart entry */
    public function addEntry(Request $request, $id)
    {
        try {
            $request->validate([
                'heart_rate'       => ['nullable', 'integer', 'min:0', 'max:300'],
                'bp_systolic'      => ['nullable', 'integer', 'min:0', 'max:300'],
                'bp_diastolic'     => ['nullable', 'integer', 'min:0', 'max:300'],
                'spo2_pct'         => ['nullable', 'integer', 'min:0', 'max:100'],
                'respiratory_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            ]);
            $actorId = (new SessionService())->init()->getUserId();
            $entry = app(AnaesthesiaRecordService::class)->addEntry((int) $id, $request->all(), $actorId);
            return $this->successResponse($entry);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function end(Request $request, $id)
    {
        try {
            $actorId = (new SessionService())->init()->getUserId();
            $record = app(AnaesthesiaRecordService::class)->endRecord((int) $id, $request->input('recovery_notes'), $actorId);
            return $this->successResponse((new AnaesthesiaRecordResource($record))->toArray($request));
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}
