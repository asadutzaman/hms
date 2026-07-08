<?php

namespace App\Services\Lis;

use App\Models\LabQcLot;
use App\Models\LabQcRun;
use Illuminate\Support\Facades\DB;

/**
 * F-05-10 Lab Quality Control Module. is_out_of_control uses a simple
 * 1-3s Westgard rule (|z| > 3) — see the migration note on lab_qc_runs for
 * why a full multi-rule Westgard engine is out of scope here.
 */
class LabQcService
{
    public function createLot(array $data, int $actorId): LabQcLot
    {
        return LabQcLot::query()->create([
            'lab_test_parameter_id' => $data['lab_test_parameter_id'],
            'lot_number'            => $data['lot_number'],
            'level'                 => $data['level'] ?? 'Level 1',
            'target_mean'           => $data['target_mean'],
            'target_sd'             => $data['target_sd'],
            'expiry_date'           => $data['expiry_date'] ?? null,
            'notes'                 => $data['notes'] ?? null,
            'created_by'            => $actorId,
        ]);
    }

    public function recordRun(int $qcLotId, array $data, int $actorId): LabQcRun
    {
        return DB::transaction(function () use ($qcLotId, $data, $actorId) {
            $lot = LabQcLot::query()->findOrFail($qcLotId);

            $measuredValue = (float) $data['measured_value'];
            $sd = (float) $lot->target_sd;
            $zScore = $sd > 0 ? round(($measuredValue - (float) $lot->target_mean) / $sd, 2) : null;
            $outOfControl = $zScore !== null && abs($zScore) > 3;

            return LabQcRun::query()->create([
                'qc_lot_id'         => $qcLotId,
                'run_date'          => $data['run_date'] ?? now(),
                'measured_value'    => $measuredValue,
                'z_score'           => $zScore,
                'is_out_of_control' => $outOfControl,
                'performed_by'      => $actorId,
                'remarks'           => $data['remarks'] ?? null,
                'created_by'        => $actorId,
            ]);
        });
    }

    public function levelJenningsData(int $qcLotId): array
    {
        $lot = LabQcLot::query()->with('runs')->findOrFail($qcLotId);

        return [
            'lot'      => $lot,
            'runs'     => $lot->runs,
            'summary'  => [
                'total_runs'         => $lot->runs->count(),
                'out_of_control_count' => $lot->runs->where('is_out_of_control', true)->count(),
            ],
        ];
    }
}
