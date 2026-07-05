<?php

namespace App\Repositories\Report;

use App\Repositories\BaseRepository;
use App\Models\ControlledDrugRegister;

class ControlledDrugRegisterReportRepository extends BaseRepository
{
    protected $request;

    public function init()
    {
        $this->request = request();
        return $this;
    }

    protected function buildQuery()
    {
        $query = ControlledDrugRegister::query()
            ->with([
                'drug.item:id,name_en,code',
                'patient:id,first_name,last_name,mrn',
                'dispenser:id,name',
                'witness:id,name',
            ])
            ->orderByDesc('dispensed_at');

        if ($drugId = $this->request->query('drug_id')) {
            $query->where('drug_id', $drugId);
        }
        if ($dispensedBy = $this->request->query('dispensed_by')) {
            $query->where('dispensed_by', $dispensedBy);
        }
        if ($from = $this->request->query('date_from')) {
            $query->whereDate('dispensed_at', '>=', $from);
        }
        if ($to = $this->request->query('date_to')) {
            $query->whereDate('dispensed_at', '<=', $to);
        }

        return $query;
    }

    public function getRegisterList()
    {
        $rows = $this->buildQuery()->get();

        $results = $rows->map(function ($row) {
            return [
                'id'                 => $row->id,
                'drug_id'            => $row->drug_id,
                'drug_name'          => $row->drug->item->name_en ?? $row->drug->brand_name ?? null,
                'patient_id'         => $row->patient_id,
                'patient_name'       => trim(($row->patient->first_name ?? '') . ' ' . ($row->patient->last_name ?? '')),
                'patient_no'         => $row->patient->mrn ?? null,
                'dispensed_quantity' => $row->dispensed_quantity,
                'dispensed_by_name'  => $row->dispenser->name ?? null,
                'witnessed_by_name'  => $row->witness->name ?? null,
                'dispensed_at'       => $row->dispensed_at ? $row->dispensed_at->format('Y-m-d H:i:s') : null,
                'remarks'            => $row->remarks,
            ];
        })->values();

        return [
            'results' => $results,
        ];
    }
}
