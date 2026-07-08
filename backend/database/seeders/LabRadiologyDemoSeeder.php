<?php

namespace Database\Seeders;

use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabQcLot;
use App\Models\LabQcRun;
use App\Models\LabResult;
use App\Models\LabSample;
use App\Models\LabTest;
use App\Models\LabTestParameter;
use App\Models\Patient;
use App\Models\RadiologyOrder;
use App\Models\RadiologyOrderItem;
use App\Models\RadiologyReport;
use App\Models\RadiologyTest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Lab + Radiology volume: ~15 lab orders across statuses with samples,
 * order items, and results (a few flagged high/low), plus a genuine
 * Levey-Jennings trend (15 QC runs per lot, mean ~target with occasional
 * out-of-control points) and ~10 radiology orders with reports.
 *
 * Idempotent: lab/radiology orders keyed by their order-no columns via a
 * pre-generated demo-only sequence (`LAB-DEMO-*` / `RAD-DEMO-*`); QC lots
 * keyed by `lot_number`.
 */
class LabRadiologyDemoSeeder extends Seeder
{
    private array $patientIds;
    private int $actorId;
    private int $techUserId;

    public function run(): void
    {
        $this->command->info('[LabRadiologyDemoSeeder] Starting ...');

        $this->patientIds = Patient::query()->pluck('id')->all();
        $this->actorId    = User::query()->where('email', 'doctor@hms.local')->value('id') ?? 1;
        $this->techUserId = User::query()->where('email', 'reception@hms.local')->value('id') ?? 1;

        $this->seedLabOrders();
        $this->seedQcTrend();
        $this->seedRadiologyOrders();

        $this->command->info('[LabRadiologyDemoSeeder] Done.');
    }

    private function seedLabOrders(): void
    {
        $cbc   = LabTest::query()->where('name', 'like', 'Complete Blood Count%')->first();
        $lipid = LabTest::query()->where('name', 'Lipid Profile')->first();
        $fbs   = LabTest::query()->where('name', 'like', 'Fasting Blood Sugar%')->first();
        $hb    = LabTestParameter::query()->where('parameter_name', 'Haemoglobin')->where('lab_test_id', $cbc?->id)->first();

        $created = 0;
        for ($i = 1; $i <= 15; $i++) {
            $orderNo = 'LAB-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            if (DB::table('lab_orders')->where('lab_order_no', $orderNo)->exists()) {
                continue;
            }

            $orderedAt = Carbon::now()->subDays(rand(0, 20));
            $status = $i <= 10 ? 'reported' : ($i <= 13 ? 'in_progress' : 'ordered');

            $order = LabOrder::query()->forceCreate([
                'lab_order_no' => $orderNo,
                'patient_id' => $this->patientIds[$i % count($this->patientIds)],
                'order_source' => 'opd',
                'ordered_by' => $this->actorId,
                'ordered_at' => $orderedAt,
                'priority' => $i % 5 === 0 ? 'urgent' : 'routine',
                'clinical_indication' => 'Routine health checkup (demo)',
                'order_status' => $status,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            $test = $i % 3 === 0 ? $lipid : ($i % 3 === 1 ? $cbc : $fbs);
            $item = LabOrderItem::query()->forceCreate([
                'lab_order_id' => $order->id, 'lab_test_id' => $test->id,
                'test_name_snapshot' => $test->name, 'sample_type_snapshot' => $test->sample_type,
                'price_snapshot' => $test->default_price, 'item_status' => $status === 'ordered' ? 'pending' : 'completed',
                'sequence' => 1, 'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            if ($status !== 'ordered') {
                $sample = LabSample::query()->forceCreate([
                    'lab_order_id' => $order->id,
                    'barcode' => 'BC-' . $orderNo,
                    'sample_type' => $test->sample_type ?? 'Serum',
                    'sample_status' => 'received',
                    'collected_by' => $this->techUserId, 'collected_at' => $orderedAt->copy()->addMinutes(20),
                    'received_by' => $this->techUserId, 'received_at' => $orderedAt->copy()->addMinutes(35),
                    'created_by' => $this->techUserId, 'updated_by' => $this->techUserId, 'status' => 1,
                ]);
            }

            if ($status === 'reported') {
                // Result on the primary parameter of the test, with occasional flags.
                $param = LabTestParameter::query()->where('lab_test_id', $test->id)->orderBy('sequence')->first();
                if ($param) {
                    $isFlagged = $i % 4 === 0;
                    $value = $isFlagged ? 18.5 : 13.5;
                    LabResult::query()->forceCreate([
                        'lab_order_item_id' => $item->id,
                        'lab_test_parameter_id' => $param->id,
                        'parameter_name_snapshot' => $param->parameter_name,
                        'unit_snapshot' => $param->unit,
                        'result_value' => (string) $value,
                        'result_flag' => $isFlagged ? 'high' : 'normal',
                        'reference_range_display' => '12.0 - 16.0',
                        'verification_status' => 'verified',
                        'entered_by' => $this->techUserId, 'entered_at' => $orderedAt->copy()->addHours(2),
                        'verified_by' => $this->actorId, 'verified_at' => $orderedAt->copy()->addHours(3),
                        'result_source' => 'manual',
                        'created_by' => $this->techUserId, 'updated_by' => $this->techUserId, 'status' => 1,
                    ]);
                }
            }

            $created++;
        }

        $this->command->info("[LabRadiologyDemoSeeder] Lab orders created: {$created}");

        // QC lots reused by seedQcTrend(); stash the Haemoglobin parameter id.
        if ($hb) {
            $this->hbParamId = $hb->id;
        }
    }

    private ?int $hbParamId = null;

    private function seedQcTrend(): void
    {
        $hbParamId = $this->hbParamId ?? LabTestParameter::query()->where('parameter_name', 'Haemoglobin')->value('id');
        if (!$hbParamId) {
            return;
        }

        $lot = LabQcLot::query()->updateOrCreate(
            ['lot_number' => 'QC-HB-LOT-01'],
            [
                'lab_test_parameter_id' => $hbParamId,
                'level' => 'normal',
                'target_mean' => 13.5,
                'target_sd' => 0.5,
                'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
                'status' => 1,
            ],
        );

        $created = 0;
        for ($d = 15; $d >= 1; $d--) {
            $date = Carbon::today()->subDays($d);
            $exists = DB::table('lab_qc_runs')->where(['qc_lot_id' => $lot->id, 'run_date' => $date->toDateString()])->exists();
            if ($exists) {
                continue;
            }

            // Mostly within +/-2SD, one deliberate outlier for the out-of-control demo.
            $isOutlier = $d === 8;
            $measured = $isOutlier ? 15.3 : round(13.5 + (mt_rand(-100, 100) / 100) * 0.6, 2);
            $z = round(($measured - 13.5) / 0.5, 2);

            LabQcRun::query()->create([
                'qc_lot_id' => $lot->id,
                'run_date' => $date->toDateString(),
                'measured_value' => $measured,
                'z_score' => $z,
                'is_out_of_control' => abs($z) > 3,
                'performed_by' => $this->techUserId,
                'status' => 1,
            ]);
            $created++;
        }

        $this->command->info("[LabRadiologyDemoSeeder] QC runs created: {$created}");
    }

    private function seedRadiologyOrders(): void
    {
        $tests = RadiologyTest::query()->limit(6)->get();
        if ($tests->isEmpty()) {
            return;
        }

        $created = 0;
        for ($i = 1; $i <= 10; $i++) {
            $orderNo = 'RAD-DEMO-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            if (DB::table('radiology_orders')->where('rad_order_no', $orderNo)->exists()) {
                continue;
            }

            $orderedAt = Carbon::now()->subDays(rand(0, 15));
            $status = $i <= 7 ? 'reported' : 'ordered';

            $order = RadiologyOrder::query()->forceCreate([
                'rad_order_no' => $orderNo,
                'patient_id' => $this->patientIds[$i % count($this->patientIds)],
                'order_source' => 'opd',
                'ordered_by' => $this->actorId, 'ordered_at' => $orderedAt,
                'priority' => 'routine',
                'clinical_indication' => 'Diagnostic imaging workup (demo)',
                'order_status' => $status,
                'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            $test = $tests[$i % $tests->count()];
            $item = RadiologyOrderItem::query()->forceCreate([
                'radiology_order_id' => $order->id, 'radiology_test_id' => $test->id,
                'test_name_snapshot' => $test->name,
                'price_snapshot' => 1200,
                'item_status' => $status === 'ordered' ? 'pending' : 'completed',
                'sequence' => 1, 'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
            ]);

            if ($status === 'reported') {
                RadiologyReport::query()->forceCreate([
                    'radiology_order_item_id' => $item->id,
                    'findings' => 'No acute abnormality detected. Normal study (demo).',
                    'impression' => 'Unremarkable study.',
                    'report_status' => 'verified',
                    'reported_by' => $this->actorId, 'reported_at' => $orderedAt->copy()->addHours(4),
                    'verified_by' => $this->actorId, 'verified_at' => $orderedAt->copy()->addHours(5),
                    'created_by' => $this->actorId, 'updated_by' => $this->actorId, 'status' => 1,
                ]);
            }

            $created++;
        }

        $this->command->info("[LabRadiologyDemoSeeder] Radiology orders created: {$created}");
    }
}
