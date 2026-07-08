<?php

namespace App\Repositories;

use App\Models\AnaesthesiaRecord;

class AnaesthesiaRecordRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = [];

    public function __construct(AnaesthesiaRecord $model)
    {
        $this->model = $model;
    }

    public function forBooking(int $otBookingId)
    {
        return $this->newQuery()->with('entries')->where('ot_booking_id', $otBookingId)->first();
    }
}
