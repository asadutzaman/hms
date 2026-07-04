<?php

namespace App\Repositories;

use App\Exceptions\ApiException;
use App\Models\Appointment;
use App\Models\AppointmentWaitlist;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Services\ODataService;
use Illuminate\Support\Facades\DB;

class PatientRepository extends BaseRepository
{
    /**
     * @var Patient
     */
    protected $model;

    protected $request;

    protected $oDataService;

    protected $fieldSearchable = ['first_name', 'last_name', 'primary_phone', 'mrn'];

    /**
     * Nullable, unique fields that may be safely moved from the duplicate onto
     * the survivor when the survivor's own value is empty. `mrn` and
     * `primary_phone` are deliberately excluded — they are required identity
     * fields and each patient keeps their own.
     */
    private const MERGEABLE_UNIQUE_FIELDS = [
        'email', 'pan_number', 'voter_id', 'driving_license', 'passport_number',
    ];

    public function __construct()
    {
        $this->model         = new Patient();
    }
    protected function init()
    {
        $this->request      = request();
        $this->oDataService = (new ODataService())->init();
    }

    /**
     * Merge a duplicate patient record into a survivor: re-points child
     * records (appointments, waitlist entries, OPD visits) to the survivor,
     * backfills empty unique fields on the survivor from the duplicate, then
     * soft-deletes the duplicate. Returns the survivor plus a summary of
     * what was reassigned, for the audit log and API response.
     */
    public function merge(int $survivorId, int $duplicateId, int $actorId): array
    {
        return DB::transaction(function () use ($survivorId, $duplicateId, $actorId) {
            $survivor  = $this->model->lockForUpdate()->find($survivorId);
            $duplicate = $this->model->lockForUpdate()->find($duplicateId);

            if (!$survivor || !$duplicate) {
                throw new ApiException('Both survivor and duplicate patient records must exist.', 404);
            }

            $reassigned = [
                'appointments'          => Appointment::where('patient_id', $duplicateId)->update(['patient_id' => $survivorId]),
                'appointment_waitlists' => AppointmentWaitlist::where('patient_id', $duplicateId)->update(['patient_id' => $survivorId]),
                'opd_visits'            => OpdVisit::where('patient_id', $duplicateId)->update(['patient_id' => $survivorId]),
            ];

            // Backfill empty unique fields on the survivor. Clear the duplicate's
            // copy first so the two updates never collide on the unique index.
            $backfilled = [];
            foreach (self::MERGEABLE_UNIQUE_FIELDS as $field) {
                if (empty($survivor->{$field}) && !empty($duplicate->{$field})) {
                    $backfilled[$field] = $duplicate->{$field};
                }
            }
            if (!empty($backfilled)) {
                $duplicate->forceFill(array_fill_keys(array_keys($backfilled), null))->save();
                $survivor->forceFill($backfilled)->save();
            }

            $duplicate->delete();

            return [
                'survivor'   => $survivor->fresh(),
                'reassigned' => $reassigned,
                'backfilled' => $backfilled,
            ];
        });
    }
}
