<?php

namespace App\Repositories;

use App\Models\BloodUnit;

class BloodUnitRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['bag_no', 'blood_group', 'component_type'];

    public function __construct(BloodUnit $model)
    {
        $this->model = $model;
    }

    /** Available inventory, optionally filtered by blood group / component. */
    public function inventory(?string $bloodGroup = null, ?string $componentType = null)
    {
        $query = $this->newQuery()->where('unit_status', 'available');

        if ($bloodGroup) {
            $query->where('blood_group', $bloodGroup);
        }
        if ($componentType) {
            $query->where('component_type', $componentType);
        }

        return $query->orderBy('expiry_date')->get();
    }

    public function inventorySummary()
    {
        return $this->newQuery()
            ->where('unit_status', 'available')
            ->selectRaw('blood_group, component_type, COUNT(*) as unit_count')
            ->groupBy('blood_group', 'component_type')
            ->orderBy('blood_group')
            ->get();
    }

    public function expiringSoon(int $withinDays = 7)
    {
        return $this->newQuery()
            ->where('unit_status', 'available')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($withinDays)->toDateString()])
            ->orderBy('expiry_date')
            ->get();
    }
}
