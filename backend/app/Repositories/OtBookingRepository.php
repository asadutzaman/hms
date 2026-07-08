<?php

namespace App\Repositories;

use App\Models\OtBooking;

class OtBookingRepository extends BaseRepository
{
    protected $model;

    protected $fieldSearchable = ['booking_no', 'surgery_name', 'booking_status'];

    public function __construct(OtBooking $model)
    {
        $this->model = $model;
    }

    public function withRelations(int $id)
    {
        return $this->newQuery()
            ->with(['patient', 'admission', 'theatre', 'department', 'surgeon', 'anaesthetist', 'surgeryNote', 'anaesthesiaRecord.entries'])
            ->find($id);
    }

    public function forTheatreAndDate(int $theatreId, string $date)
    {
        return $this->newQuery()
            ->where('theatre_id', $theatreId)
            ->where('scheduled_date', $date)
            ->whereNotIn('booking_status', ['cancelled'])
            ->orderBy('scheduled_start_time')
            ->get();
    }
}
