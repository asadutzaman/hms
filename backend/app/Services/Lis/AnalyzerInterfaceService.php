<?php

namespace App\Services\Lis;

use App\Models\LabAnalyzerMessage;
use App\Models\LabSample;
use Illuminate\Support\Facades\DB;

/**
 * F-05-06 Machine Interfacing (ASTM/HL7) — "bi-directional analyzer
 * integration". This project has no real analyzer hardware/serial-TCP
 * listener; import() accepts an already-parsed, structured representation
 * of what an ASTM E1394/HL7 v2 result message would carry (barcode +
 * per-parameter values), matches it to the sample's lab order items by
 * parameter, and feeds it through the SAME LabResultService::enterResults()
 * path a technician's manual entry uses (marked result_source='machine').
 * "Manual override possible" falls out for free — a technician can just
 * re-save the same parameter afterward via the normal entry screen,
 * exactly like Sprint 9's biometric-attendance-sync scope decision.
 */
class AnalyzerInterfaceService
{
    protected LabResultService $labResultService;

    public function __construct(LabResultService $labResultService)
    {
        $this->labResultService = $labResultService;
    }

    public function import(array $data, int $systemActorId): LabAnalyzerMessage
    {
        return DB::transaction(function () use ($data, $systemActorId) {
            $message = LabAnalyzerMessage::query()->create([
                'analyzer_name' => $data['analyzer_name'] ?? 'Unknown Analyzer',
                'barcode'       => $data['barcode'] ?? null,
                'raw_message'   => json_encode($data),
                'received_at'   => now(),
                'created_by'    => $systemActorId,
            ]);

            $sample = LabSample::query()->where('barcode', $data['barcode'] ?? null)->first();
            if (!$sample) {
                $message->parse_status = 'failed';
                $message->error_message = "No sample found for barcode '{$data['barcode']}'.";
                $message->processed_at = now();
                $message->save();
                return $message;
            }

            $order = $sample->order()->with('items.labTest.parameters')->first();
            if (!$order) {
                $message->parse_status = 'failed';
                $message->error_message = 'Sample is not linked to a lab order.';
                $message->processed_at = now();
                $message->save();
                return $message;
            }

            // Group the incoming per-parameter values by which lab_order_item
            // (i.e. which ordered test) that parameter belongs to.
            $itemResults = [];
            $unmatched = [];
            foreach (($data['results'] ?? []) as $row) {
                $matchedItem = null;
                $matchedParameter = null;

                foreach ($order->items as $item) {
                    $parameter = $item->labTest?->parameters->first(function ($p) use ($row) {
                        if (!empty($row['lab_test_parameter_id'])) {
                            return $p->id === (int) $row['lab_test_parameter_id'];
                        }
                        return isset($row['parameter_name']) && strcasecmp($p->parameter_name, $row['parameter_name']) === 0;
                    });
                    if ($parameter) {
                        $matchedItem = $item;
                        $matchedParameter = $parameter;
                        break;
                    }
                }

                if (!$matchedItem || !$matchedParameter) {
                    $unmatched[] = $row;
                    continue;
                }

                $itemResults[$matchedItem->id][] = [
                    'lab_test_parameter_id' => $matchedParameter->id,
                    'result_value'          => $row['result_value'] ?? null,
                ];
            }

            $matchedCount = 0;
            foreach ($itemResults as $orderItemId => $results) {
                $this->labResultService->enterResults((int) $orderItemId, $results, $systemActorId, 'machine');
                $matchedCount += count($results);
            }

            $message->matched_result_count = $matchedCount;
            $message->parse_status = empty($unmatched) ? 'success' : ($matchedCount > 0 ? 'partial' : 'failed');
            if (!empty($unmatched)) {
                $message->error_message = 'Unmatched parameters: ' . json_encode($unmatched);
            }
            $message->processed_at = now();
            $message->save();

            return $message;
        });
    }
}
