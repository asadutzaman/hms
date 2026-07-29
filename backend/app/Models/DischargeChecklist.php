<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DischargeChecklist extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'discharge_checklists';

    protected $fillable = [
        'organogram_id', 'ipd_admission_id', 'items', 'state', 'completed_at',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'               => 'integer',
        'ipd_admission_id' => 'integer',
        'items'            => 'array',
        'completed_at'     => 'datetime:Y-m-d H:i:s',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['state' => 'in_progress', 'status' => 1, 'sort_order' => 0];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }
}
