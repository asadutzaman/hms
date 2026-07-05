<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpdProcedure extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'opd_procedures';

    protected $fillable = [
        'organogram_id',
        'opd_visit_id',
        'patient_id',
        'procedure_name',
        'procedure_code',
        'performed_by',
        'performed_at',
        'notes',
        'outcome',
        'status',
        'sort_order',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'            => 'integer',
        'organogram_id' => 'integer',
        'opd_visit_id'  => 'integer',
        'patient_id'    => 'integer',
        'performed_by'  => 'integer',
        'sort_order'    => 'integer',
        'status'        => 'integer',
        'performed_at'  => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status' => StatusEnum::ACTIVE,
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }
}
