<?php

namespace App\Repositories;

use App\Models\SurgeryNote;

class SurgeryNoteRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = [];

    public function __construct(SurgeryNote $model)
    {
        $this->model = $model;
    }

    public function forBooking(int $otBookingId)
    {
        return $this->newQuery()->where('ot_booking_id', $otBookingId)->first();
    }
}
