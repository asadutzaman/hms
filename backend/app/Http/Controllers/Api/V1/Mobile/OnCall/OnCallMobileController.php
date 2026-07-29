<?php

namespace App\Http\Controllers\Api\V1\Mobile\OnCall;

use App\Http\Controllers\Api\V1\Mobile\BaseMobileController;
use App\Http\Controllers\ErTriageController;
use App\Http\Controllers\ErVisitController;
use App\Http\Controllers\LabOrderController;
use App\Http\Controllers\RadiologyOrderController;
use App\Models\AtoeAssessment;
use App\Models\Bleep;
use App\Models\ClinicalJob;
use App\Models\OrderSet;
use App\Models\ShiftHandover;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * On-call / duty-doctor app BFF  —  prefix /api/v1/mobile/oncall
 * (authVerify + mobile.role:Doctor). Mostly net-new: job queue (DD2), bleeps
 * (DD3), A-to-E (DD4), order sets (DD5); ED admission reuses the ER controllers.
 */
class OnCallMobileController extends BaseMobileController
{
    private function payload(JsonResponse $response)
    {
        return $response->getData(true);
    }

    // ---- DD1 On-call console (aggregation) ---------------------------------

    public function console()
    {
        $uid = $this->currentUserId();
        return $this->mobileSuccess([
            'open_jobs'      => ClinicalJob::query()->where('status', 1)->where('role_type', 'doctor')->whereIn('state', ['open', 'claimed'])->count(),
            'unread_bleeps'  => Bleep::query()->where('status', 1)->where('state', 'sent')->where(fn ($q) => $q->whereNull('to_user_id')->orWhere('to_user_id', $uid))->count(),
            'jobs'           => ClinicalJob::query()->where('status', 1)->where('role_type', 'doctor')->whereIn('state', ['open', 'claimed'])
                                    ->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'urgent' THEN 1 ELSE 2 END")->orderBy('due_at')->limit(20)->get(),
            'bleeps'         => Bleep::query()->where('status', 1)->whereIn('state', ['sent', 'escalated'])->where(fn ($q) => $q->whereNull('to_user_id')->orWhere('to_user_id', $uid))
                                    ->orderByDesc('id')->limit(20)->get(),
        ]);
    }

    // ---- DD2 Job queue ------------------------------------------------------

    public function jobs(Request $request)
    {
        $rows = ClinicalJob::query()->where('status', 1)->where('role_type', 'doctor')
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->input('state')))
            ->with(['patient:id,first_name,last_name,mrn', 'ward:id,name'])
            ->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'urgent' THEN 1 ELSE 2 END")->orderBy('due_at')->get();
        return $this->mobileSuccess($rows);
    }

    public function createJob(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'job_type'    => ['nullable', 'string', 'max:40'],
            'priority'    => ['nullable', 'in:routine,urgent,critical'],
            'patient_id'  => ['nullable', 'integer'],
            'ward_id'     => ['nullable', 'integer'],
            'bed_id'      => ['nullable', 'integer'],
            'due_at'      => ['nullable', 'date'],
        ]);
        $data['role_type']    = 'doctor';
        $data['requested_by'] = $this->currentUserId();
        $data['state']        = 'open';
        $data['created_by']   = $this->currentUserId();
        $job = ClinicalJob::create($data);
        return $this->mobileSuccess($job->fresh(), 'Job added.', [], 201);
    }

    public function claimJob(Request $request, $id)
    {
        $job = ClinicalJob::query()->where('role_type', 'doctor')->find($id);
        if (!$job) {
            return $this->mobileError('Job not found.', 404);
        }
        $job->update(['state' => 'claimed', 'assigned_to' => $this->currentUserId(), 'updated_by' => $this->currentUserId()]);
        return $this->mobileSuccess($job->fresh(), 'Job claimed.');
    }

    public function completeJob(Request $request, $id)
    {
        $job = ClinicalJob::query()->where('role_type', 'doctor')->find($id);
        if (!$job) {
            return $this->mobileError('Job not found.', 404);
        }
        $job->update(['state' => 'done', 'completed_at' => now(), 'updated_by' => $this->currentUserId()]);
        return $this->mobileSuccess($job->fresh(), 'Job completed.');
    }

    // ---- DD3 Bleeps ---------------------------------------------------------

    public function bleeps(Request $request)
    {
        $uid = $this->currentUserId();
        $rows = Bleep::query()->where('status', 1)
            ->where(fn ($q) => $q->whereNull('to_user_id')->orWhere('to_user_id', $uid))
            ->with('patient:id,first_name,last_name,mrn')->orderByDesc('id')->limit(50)->get();
        return $this->mobileSuccess($rows);
    }

    public function raiseBleep(Request $request)
    {
        $data = $request->validate([
            'to_user_id' => ['nullable', 'integer'],
            'patient_id' => ['nullable', 'integer'],
            'ward_id'    => ['nullable', 'integer'],
            'callback'   => ['nullable', 'string', 'max:60'],
            'priority'   => ['nullable', 'in:routine,urgent,crash'],
            'message'    => ['required', 'string'],
        ]);
        $data['from_user_id'] = $this->currentUserId();
        $data['state']        = 'sent';
        $data['created_by']   = $this->currentUserId();
        $bleep = Bleep::create($data);
        return $this->mobileSuccess($bleep->fresh(), 'Bleep sent.', [], 201);
    }

    public function acknowledgeBleep(Request $request, $id)
    {
        $bleep = Bleep::query()->find($id);
        if (!$bleep) {
            return $this->mobileError('Bleep not found.', 404);
        }
        $bleep->update(['state' => 'acknowledged', 'acknowledged_at' => now(), 'updated_by' => $this->currentUserId()]);
        return $this->mobileSuccess($bleep->fresh(), 'Acknowledged.');
    }

    public function escalateBleep(Request $request, $id)
    {
        $bleep = Bleep::query()->find($id);
        if (!$bleep) {
            return $this->mobileError('Bleep not found.', 404);
        }
        $bleep->update([
            'state'        => 'escalated',
            'escalated_at' => now(),
            'to_user_id'   => $request->input('to_user_id', $bleep->to_user_id),
            'updated_by'   => $this->currentUserId(),
        ]);
        return $this->mobileSuccess($bleep->fresh(), 'Escalated.');
    }

    // ---- DD4 A-to-E assessment ---------------------------------------------

    public function assessments(Request $request)
    {
        $rows = AtoeAssessment::query()->where('status', 1)
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->integer('patient_id')))
            ->orderByDesc('assessed_at')->orderByDesc('id')->get();
        return $this->mobileSuccess($rows);
    }

    public function storeAssessment(Request $request)
    {
        $data = $request->validate([
            'patient_id'       => ['nullable', 'integer'],
            'ipd_admission_id' => ['nullable', 'integer'],
            'airway'           => ['nullable', 'string'],
            'breathing'        => ['nullable', 'string'],
            'circulation'      => ['nullable', 'string'],
            'disability'       => ['nullable', 'string'],
            'exposure'         => ['nullable', 'string'],
            'news2_score'      => ['nullable', 'integer'],
            'impression'       => ['nullable', 'string'],
            'plan'             => ['nullable', 'string'],
        ]);
        $data['assessed_by'] = $this->currentUserId();
        $data['assessed_at'] = now();
        $data['created_by']  = $this->currentUserId();
        $a = AtoeAssessment::create($data);
        return $this->mobileSuccess($a->fresh(), 'Assessment saved.', [], 201);
    }

    // ---- DD5 Order sets -----------------------------------------------------

    public function orderSets(Request $request)
    {
        $uid = $this->currentUserId();
        $rows = OrderSet::query()->where('status', 1)
            ->where(fn ($q) => $q->where('is_global', true)->orWhere('owner_user_id', $uid))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->orderBy('name')->get();
        return $this->mobileSuccess($rows);
    }

    public function createOrderSet(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'category'    => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string'],
            'items'       => ['required', 'array'],
            'is_global'   => ['nullable', 'boolean'],
        ]);
        $data['owner_user_id'] = $this->currentUserId();
        $data['created_by']    = $this->currentUserId();
        $set = OrderSet::create($data);
        return $this->mobileSuccess($set->fresh(), 'Order set saved.', [], 201);
    }

    /**
     * POST /order-sets/{id}/apply — fan out the set's items to the existing
     * order controllers for a given patient/encounter. Each item is dispatched
     * independently and its outcome reported, so a bad item never aborts the rest.
     */
    public function applyOrderSet(Request $request, $id)
    {
        $set = OrderSet::query()->where('status', 1)->find($id);
        if (!$set) {
            return $this->mobileError('Order set not found.', 404);
        }
        $context = $request->validate([
            'patient_id'       => ['nullable', 'integer'],
            'opd_visit_id'     => ['nullable', 'integer'],
            'ipd_admission_id' => ['nullable', 'integer'],
        ]);

        $results = [];
        foreach (($set->items ?? []) as $i => $item) {
            $type = $item['type'] ?? null;
            $body = array_merge($context, $item['payload'] ?? [], array_filter([
                'lab_test_id'       => $item['ref_id'] ?? null,
                'radiology_test_id' => $item['ref_id'] ?? null,
            ]));
            $sub = Request::create('/', 'POST', $body);
            try {
                if ($type === 'lab') {
                    $this->payload(app(LabOrderController::class)->store($sub));
                    $results[] = ['index' => $i, 'type' => $type, 'ok' => true];
                } elseif ($type === 'radiology') {
                    $this->payload(app(RadiologyOrderController::class)->store($sub));
                    $results[] = ['index' => $i, 'type' => $type, 'ok' => true];
                } else {
                    $results[] = ['index' => $i, 'type' => $type, 'ok' => false, 'error' => 'Unsupported item type for auto-apply.'];
                }
            } catch (\Throwable $e) {
                // Custom validation exceptions carry no message() — the detail is
                // in their render() payload; surface the first line of it.
                $msg = $e->getMessage();
                if ($msg === '' && method_exists($e, 'render')) {
                    $rendered = $e->render();
                    $data = method_exists($rendered, 'getData') ? $rendered->getData(true) : null;
                    $msg = is_string($data) ? $data : (is_array($data) ? (string) collect($data)->flatten()->first() : '');
                }
                $results[] = ['index' => $i, 'type' => $type, 'ok' => false, 'error' => $msg ?: 'Could not place this order.'];
            }
        }

        return $this->mobileSuccess(['results' => $results], 'Order set applied.');
    }

    // ---- DD6 ED admission (reuse ER controllers) ---------------------------

    public function edBoard(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(ErVisitController::class)->board($request)));
    }

    public function edTriage(Request $request)
    {
        return $this->mobileSuccess($this->payload(app(ErTriageController::class)->store($request)), 'Triage recorded.', [], 201);
    }

    // ---- DD7 End-of-shift handover (net-new, shared) -----------------------

    public function handovers(Request $request)
    {
        $rows = ShiftHandover::query()->where('status', 1)->where('role_type', 'doctor')
            ->when($request->filled('ward_id'), fn ($q) => $q->where('ward_id', $request->integer('ward_id')))
            ->orderByDesc('id')->limit(50)->get();
        return $this->mobileSuccess($rows);
    }

    public function storeHandover(Request $request)
    {
        $data = $request->validate([
            'ward_id'     => ['nullable', 'integer'],
            'to_user_id'  => ['nullable', 'integer'],
            'shift_label' => ['nullable', 'string', 'max:60'],
            'summary'     => ['nullable', 'string'],
            'items'       => ['nullable', 'array'],
        ]);
        $data['role_type']      = 'doctor';
        $data['from_user_id']   = $this->currentUserId();
        $data['state']          = 'submitted';
        $data['handed_over_at'] = now();
        $data['created_by']     = $this->currentUserId();
        $h = ShiftHandover::create($data);
        return $this->mobileSuccess($h->fresh(), 'Handover submitted.', [], 201);
    }
}
