<?php

namespace App\Services\Opd;

use App\Exceptions\ApiException;
use App\Models\PrescriptionTemplate;
use App\Models\PrescriptionTemplateItem;
use App\Repositories\OpdPrescriptionRepository;
use Illuminate\Support\Facades\DB;

class PrescriptionTemplateService
{
    public function create(array $data, int $actorId): PrescriptionTemplate
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new ApiException('At least one template item is required.', 422);
        }

        return DB::transaction(function () use ($data, $actorId) {
            $template = PrescriptionTemplate::query()->create([
                'name'       => $data['name'],
                'is_shared'  => (bool) ($data['is_shared'] ?? false),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            foreach (array_values($data['items']) as $i => $row) {
                if (!empty($row['drug_id'])) {
                    $row = OpdPrescriptionRepository::snapshotFromDrug($row);
                }

                PrescriptionTemplateItem::query()->create(array_merge([
                    'prescription_template_id' => $template->id,
                    'sequence'                 => $i + 1,
                    'is_prn'                   => $row['is_prn'] ?? false,
                    'created_by'               => $actorId,
                    'updated_by'               => $actorId,
                ], $row));
            }

            return $template->fresh('items');
        });
    }

    /**
     * Return the template's items shaped for direct injection into
     * OpdPrescriptionForm's items array (drop template-specific fields).
     */
    public function apply(int $templateId): array
    {
        $template = PrescriptionTemplate::query()->with('items')->find($templateId);
        if (!$template) {
            throw new ApiException('Prescription template not found.', 404);
        }

        return $template->items->map(function (PrescriptionTemplateItem $item) {
            return [
                'drug_id'        => $item->drug_id,
                'drug_name'      => $item->drug_name,
                'generic_name'   => $item->generic_name,
                'strength'       => $item->strength,
                'dosage_form'    => $item->dosage_form,
                'dose_value'     => $item->dose_value,
                'dose_unit'      => $item->dose_unit,
                'frequency'      => $item->frequency,
                'duration_value' => $item->duration_value,
                'duration_unit'  => $item->duration_unit,
                'route'          => $item->route,
                'instruction'    => $item->instruction,
                'is_prn'         => $item->is_prn,
            ];
        })->values()->toArray();
    }
}
