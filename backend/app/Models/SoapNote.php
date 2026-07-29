<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoapNote extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'soap_notes';

    protected $fillable = [
        'organogram_id',
        'patient_id',
        'opd_visit_id',
        'ipd_admission_id',
        'author_user_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'noted_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'               => 'integer',
        'patient_id'       => 'integer',
        'opd_visit_id'     => 'integer',
        'ipd_admission_id' => 'integer',
        'author_user_id'   => 'integer',
        'noted_at'         => 'datetime:Y-m-d H:i:s',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'     => 1,
        'sort_order' => 0,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
