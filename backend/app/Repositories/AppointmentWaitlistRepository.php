<?php

namespace App\Repositories;

use App\Models\AppointmentWaitlist;
use App\Services\ODataService;
use Illuminate\Support\Facades\DB;

class AppointmentWaitlistRepository extends BaseRepository
{
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['reason_for_visit', 'notes'];

    public function __construct()
    {
        $this->model = new AppointmentWaitlist();
    }

    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Ordered candidates for a (doctor, date). Priority ASC, then queue_position ASC.
     */
    public function candidatesFor(int $doctorId, string $date)
    {
        return $this->newQuery()
            ->where('doctor_id', $doctorId)
            ->where('status', 'waiting')
            ->whereDate('preferred_date_from', '<=', $date)
            ->whereDate('preferred_date_to', '>=', $date)
            ->orderBy('priority')
            ->orderBy('queue_position')
            ->orderBy('created_at')
            ->get();
    }

    public function assignQueuePositions(int $doctorId)
    {
        return DB::transaction(function () use ($doctorId) {
            $rows = $this->newQuery()
                ->where('doctor_id', $doctorId)
                ->where('status', 'waiting')
                ->orderBy('priority')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            $position = 1;
            foreach ($rows as $row) {
                $row->queue_position = $position++;
                $row->save();
            }
            return $rows;
        });
    }
}
