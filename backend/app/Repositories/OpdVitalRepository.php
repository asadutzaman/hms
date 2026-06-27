<?php

namespace App\Repositories;

use App\Enums\OpdVisitActionEnum;
use App\Models\OpdVital;
use App\Models\OpdVisit;
use App\Services\ODataService;

class OpdVitalRepository extends BaseRepository
{
    /**
     * @var OpdVital
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['notes'];

    public function __construct()
    {
        $this->model = new OpdVital();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    public function findByVisit(int $visitId): ?OpdVital
    {
        return $this->newQuery()
            ->where('opd_visit_id', $visitId)
            ->first();
    }

    /**
     * Save or update vitals for a visit, plus emit an audit log entry on the parent visit.
     * Computes BMI = weight_kg / (height_m)^2 when both values present.
     */
    public function saveForVisit(int $visitId, int $actorId, array $data): OpdVital
    {
        $visit = OpdVisit::query()->findOrFail($visitId);

        $data['opd_visit_id'] = $visitId;
        $data['patient_id']   = $visit->patient_id;
        $data['recorded_by']  = $actorId;
        $data['recorded_at']  = $data['recorded_at'] ?? now();

        if (!empty($data['weight_kg']) && !empty($data['height_cm'])) {
            $heightM = ((float) $data['height_cm']) / 100.0;
            if ($heightM > 0) {
                $data['bmi'] = round(((float) $data['weight_kg']) / ($heightM * $heightM), 2);
            }
        }

        $existing = $this->findByVisit($visitId);

        if ($existing) {
            $existing->fill($data);
            $existing->save();
            $vital = $existing;
        } else {
            $vital = $this->model->create($data);
        }

        // Audit log on parent visit.
        app(OpdVisitRepository::class)->logAudit(
            $visit,
            OpdVisitActionEnum::VITALS_SAVED,
            null,
            null,
            $actorId,
            'Vitals captured',
            ['opd_vital_id' => $vital->id],
        );

        return $vital;
    }
}
