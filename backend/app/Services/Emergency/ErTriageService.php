<?php

namespace App\Services\Emergency;

use App\Enums\ErTriageLevelEnum;
use App\Enums\ErVisitStatusEnum;
use App\Exceptions\ApiException;
use App\Models\ErTriage;
use App\Models\ErVisit;
use Illuminate\Support\Facades\DB;

class ErTriageService
{
    /**
     * Record a triage assessment (also covers re-triage — each call inserts
     * a new row rather than updating in place, preserving the history of
     * acuity changes over the ER stay) and move the visit's board status to
     * 'triaged' if it's still waiting.
     */
    public function triage(array $data, int $actorId): ErTriage
    {
        return DB::transaction(function () use ($data, $actorId) {
            $visit = ErVisit::query()->lockForUpdate()->findOrFail($data['er_visit_id']);

            if (ErVisitStatusEnum::isTerminal($visit->er_status)) {
                throw new ApiException("Cannot triage a visit that is already {$visit->er_status}.", 422);
            }

            $level = (int) $data['triage_level'];
            if (!in_array($level, [1, 2, 3, 4, 5], true)) {
                throw new ApiException('Triage level must be between 1 and 5.', 422);
            }

            $triage = ErTriage::query()->create(array_merge($data, [
                'organogram_id'  => $visit->organogram_id,
                'color_band'     => ErTriageLevelEnum::color($level),
                'target_minutes' => ErTriageLevelEnum::targetMinutes($level),
                'triaged_by'     => $actorId,
                'triaged_at'     => now(),
            ]));

            if ($visit->er_status === ErVisitStatusEnum::WAITING_TRIAGE) {
                $visit->er_status = ErVisitStatusEnum::TRIAGED;
                $visit->save();
            }

            return $triage->fresh();
        });
    }
}
