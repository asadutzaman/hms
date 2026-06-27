<?php

namespace App\Repositories;

use App\Enums\OpdVisitActionEnum;
use App\Models\OpdDiagnosis;
use App\Models\OpdVisit;
use App\Services\ODataService;
use Illuminate\Support\Facades\DB;

class OpdDiagnosisRepository extends BaseRepository
{
    /**
     * @var OpdDiagnosis
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = [
        'icd10_code',
        'icd10_description',
        'diagnosis_name',
        'diagnosis_type',
    ];

    public function __construct()
    {
        $this->model = new OpdDiagnosis();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function forVisit(int $visitId)
    {
        return $this->newQuery()
            ->where('opd_visit_id', $visitId)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();
    }

    /**
     * Replace all diagnoses for a visit atomically. Old rows are soft-deleted (status=0),
     * new rows are inserted with sequence preserved. Audit log entry written once.
     */
    public function syncForVisit(int $visitId, int $actorId, array $items): array
    {
        return DB::transaction(function () use ($visitId, $actorId, $items) {
            $visit = OpdVisit::query()->findOrFail($visitId);

            // Soft-delete existing rows for this visit.
            $this->newQuery()
                ->where('opd_visit_id', $visitId)
                ->update(['status' => 0]);

            $created = [];
            foreach ($items as $i => $item) {
                $created[] = OpdDiagnosis::query()->create([
                    'organogram_id'     => $visit->organogram_id,
                    'opd_visit_id'      => $visitId,
                    'patient_id'        => $visit->patient_id,
                    'diagnosis_type'    => $item['diagnosis_type']    ?? 'final',
                    'icd10_code'        => $item['icd10_code']        ?? null,
                    'icd10_description' => $item['icd10_description'] ?? null,
                    'diagnosis_name'    => $item['diagnosis_name']    ?? null,
                    'is_primary'        => (bool) ($item['is_primary']   ?? false),
                    'is_chronic'        => (bool) ($item['is_chronic']   ?? false),
                    'is_confirmed'      => (bool) ($item['is_confirmed'] ?? true),
                    'notes'             => $item['notes']             ?? null,
                    'sequence'          => $item['sequence']          ?? ($i + 1),
                    'recorded_by'       => $actorId,
                    'status'            => 1,
                ]);
            }

            app(OpdVisitRepository::class)->logAudit(
                $visit,
                OpdVisitActionEnum::DIAGNOSIS_SAVED,
                null,
                null,
                $actorId,
                'Diagnoses recorded',
                ['count' => count($created)],
            );

            return $created;
        });
    }
}
