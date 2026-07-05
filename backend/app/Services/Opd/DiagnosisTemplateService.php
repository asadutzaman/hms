<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\DiagnosisTemplate;
use App\Models\DiagnosisTemplateItem;
use App\Models\Icd10Code;
use Illuminate\Support\Facades\DB;

class DiagnosisTemplateService
{
    public function create(array $data, int $actorId): DiagnosisTemplate
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new ApiException('At least one template item is required.', 422);
        }

        return DB::transaction(function () use ($data, $actorId) {
            $template = DiagnosisTemplate::query()->create([
                'name'       => $data['name'],
                'is_shared'  => (bool) ($data['is_shared'] ?? false),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            foreach (array_values($data['items']) as $i => $row) {
                if (!empty($row['icd10_id'])) {
                    $icd10 = Icd10Code::query()->find($row['icd10_id']);
                    if ($icd10) {
                        $row['icd10_code'] = $icd10->code;
                        $row['icd10_description'] = $icd10->description;
                    }
                }

                DiagnosisTemplateItem::query()->create(array_merge([
                    'diagnosis_template_id' => $template->id,
                    'sequence'              => $i + 1,
                    'created_by'            => $actorId,
                    'updated_by'            => $actorId,
                ], $row));
            }

            return $template->fresh('items');
        });
    }

    /**
     * Return the template's items shaped for direct injection into
     * OpdDiagnosisForm's items array.
     */
    public function apply(int $templateId): array
    {
        $template = DiagnosisTemplate::query()->with('items')->find($templateId);
        if (!$template) {
            throw new ApiException('Diagnosis template not found.', 404);
        }

        return $template->items->map(function (DiagnosisTemplateItem $item) {
            return [
                'diagnosis_type'     => $item->diagnosis_type,
                'icd10_id'           => $item->icd10_id,
                'icd10_code'         => $item->icd10_code,
                'icd10_description'  => $item->icd10_description,
                'diagnosis_name'     => $item->diagnosis_name,
                'notes'              => $item->notes,
            ];
        })->values()->toArray();
    }
}
