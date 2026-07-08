<?php

namespace App\Repositories;

use App\Models\BloodDonor;

class BloodDonorRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['donor_no', 'name', 'phone', 'blood_group'];

    public function __construct(BloodDonor $model)
    {
        $this->model = $model;
    }

    public function withRelations(int $id)
    {
        return $this->newQuery()->with('donations')->find($id);
    }

    public function eligibleDonors()
    {
        return $this->newQuery()
            ->where(function ($q) {
                $q->where('is_deferred', false)
                    ->orWhere('deferral_until_date', '<', now()->toDateString());
            })
            ->orderBy('name')
            ->get();
    }
}
