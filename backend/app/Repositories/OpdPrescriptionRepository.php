<?php

namespace App\Repositories;

use App\Enums\OpdVisitActionEnum;
use App\Models\Drug;
use App\Models\OpdPrescription;
use App\Models\OpdPrescriptionItem;
use App\Models\OpdVisit;
use App\Services\ODataService;
use Illuminate\Support\Facades\DB;

class OpdPrescriptionRepository extends BaseRepository
{
    /**
     * @var OpdPrescription
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['advice', 'notes'];

    public function __construct()
    {
        $this->model = new OpdPrescription();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function findByVisit(int $visitId): ?OpdPrescription
    {
        return $this->newQuery()
            ->with('items')
            ->where('opd_visit_id', $visitId)
            ->first();
    }

    /**
     * Create or replace the prescription + items for a visit atomically.
     * Items come in as an array of arrays. Quantity/pricing stored at the item level for later bill snapshotting.
     */
    public function saveForVisit(int $visitId, int $actorId, array $header, array $items): OpdPrescription
    {
        return DB::transaction(function () use ($visitId, $actorId, $header, $items) {
            $visit = OpdVisit::query()->findOrFail($visitId);

            $existing = $this->findByVisit($visitId);

            if ($existing) {
                $existing->fill($header);
                $existing->prescribed_by = $actorId;
                $existing->prescribed_at = $header['prescribed_at'] ?? now();
                $existing->save();
                $rx = $existing;

                // Soft-delete old items.
                OpdPrescriptionItem::query()
                    ->where('opd_prescription_id', $rx->id)
                    ->update(['status' => 0]);
            } else {
                $rx = OpdPrescription::query()->create([
                    'organogram_id'  => $visit->organogram_id,
                    'opd_visit_id'   => $visitId,
                    'patient_id'     => $visit->patient_id,
                    'prescribed_by'  => $actorId,
                    'prescribed_at'  => $header['prescribed_at'] ?? now(),
                    'advice'         => $header['advice']         ?? null,
                    'follow_up_date' => $header['follow_up_date'] ?? null,
                    'notes'          => $header['notes']          ?? null,
                    'is_printed'     => false,
                    'status'         => 1,
                ]);
            }

            foreach ($items as $i => $it) {
                if (!empty($it['drug_id'])) {
                    $it = self::snapshotFromDrug($it);
                }

                OpdPrescriptionItem::query()->create([
                    'organogram_id'       => $visit->organogram_id,
                    'opd_prescription_id' => $rx->id,
                    'opd_visit_id'        => $visitId,
                    'drug_id'             => $it['drug_id']       ?? null,
                    'drug_name'           => $it['drug_name']     ?? 'Unknown',
                    'generic_name'        => $it['generic_name']  ?? null,
                    'strength'            => $it['strength']      ?? null,
                    'dosage_form'         => $it['dosage_form']   ?? null,
                    'dose_value'          => $it['dose_value']    ?? null,
                    'dose_unit'           => $it['dose_unit']     ?? null,
                    'frequency'           => $it['frequency']     ?? 'OD',
                    'duration_value'      => $it['duration_value'] ?? null,
                    'duration_unit'       => $it['duration_unit']  ?? 'days',
                    'route'               => $it['route']         ?? 'oral',
                    'instruction'         => $it['instruction']   ?? null,
                    'is_prn'              => (bool) ($it['is_prn'] ?? false),
                    'sequence'            => $it['sequence']      ?? ($i + 1),
                    'unit_price'          => $it['unit_price']    ?? 0,
                    'amount'              => $it['amount']        ?? ($it['unit_price'] ?? 0),
                    'status'              => 1,
                ]);
            }

            app(OpdVisitRepository::class)->logAudit(
                $visit,
                OpdVisitActionEnum::PRESCRIPTION_SAVED,
                null,
                null,
                $actorId,
                'Prescription saved',
                ['item_count' => count($items)],
            );

            return $rx->fresh('items');
        });
    }

    /**
     * When a prescription line references a catalog drug, the catalog is the
     * source of truth for name/generic/strength/dosage_form — overwrite
     * whatever free text was submitted so dispensing/interaction checks can
     * trust drug_id without also trusting client-supplied text.
     */
    public static function snapshotFromDrug(array $item): array
    {
        $drug = Drug::query()->with('item')->find($item['drug_id']);
        if (!$drug) {
            unset($item['drug_id']);
            return $item;
        }

        $item['drug_name']    = $drug->item->name_en ?? $drug->brand_name ?? $drug->generic_name;
        $item['generic_name'] = $drug->generic_name;
        $item['strength']     = $drug->strength;
        $item['dosage_form']  = $drug->dosage_form;

        return $item;
    }

    /**
     * The patient's most recent prescription (any prescriber), optionally
     * excluding the current visit's own prescription so "Copy Previous Rx"
     * never copies the form onto itself.
     */
    public function latestForPatient(int $patientId, ?int $excludeVisitId = null): ?OpdPrescription
    {
        $q = $this->newQuery()
            ->with('items')
            ->where('patient_id', $patientId)
            ->where('status', 1)
            ->orderByDesc('prescribed_at')
            ->orderByDesc('id');

        if ($excludeVisitId) {
            $q->where('opd_visit_id', '!=', $excludeVisitId);
        }

        return $q->first();
    }

    /**
     * Catalog drugs this prescriber has used most recently, deduped by drug,
     * newest first — derived from their own prescription items so no
     * favorites table is needed.
     */
    public function recentDrugsForPrescriber(int $userId, int $limit = 10)
    {
        // No raw aggregates: table names carry a connection prefix that
        // DB::raw() strings would miss. Scan a bounded window of the
        // prescriber's newest lines and dedupe by drug in PHP instead.
        return OpdPrescriptionItem::query()
            ->join('opd_prescriptions', 'opd_prescriptions.id', '=', 'opd_prescription_items.opd_prescription_id')
            ->where('opd_prescriptions.prescribed_by', $userId)
            ->where('opd_prescription_items.status', 1)
            ->whereNotNull('opd_prescription_items.drug_id')
            ->whereNull('opd_prescriptions.deleted_at')
            ->orderByDesc('opd_prescription_items.id')
            ->limit(200)
            ->get([
                'opd_prescription_items.drug_id',
                'opd_prescription_items.drug_name',
                'opd_prescription_items.generic_name',
                'opd_prescription_items.strength',
                'opd_prescription_items.dosage_form',
            ])
            ->unique('drug_id')
            ->take($limit)
            ->values();
    }

    public function markPrinted(int $prescriptionId): OpdPrescription
    {
        $rx = $this->newQuery()->findOrFail($prescriptionId);
        $rx->is_printed = true;
        $rx->printed_at = now();
        $rx->save();
        return $rx;
    }
}
