<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bleep extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'bleeps';

    protected $fillable = [
        'organogram_id', 'from_user_id', 'to_user_id', 'patient_id', 'ward_id',
        'callback', 'priority', 'message', 'state', 'acknowledged_at', 'escalated_at',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'              => 'integer',
        'from_user_id'    => 'integer',
        'to_user_id'      => 'integer',
        'patient_id'      => 'integer',
        'ward_id'         => 'integer',
        'acknowledged_at' => 'datetime:Y-m-d H:i:s',
        'escalated_at'    => 'datetime:Y-m-d H:i:s',
        'created_by'      => 'integer',
        'updated_by'      => 'integer',
        'sort_order'      => 'integer',
        'status'          => 'integer',
        'created_at'      => 'datetime:Y-m-d H:i:s',
        'updated_at'      => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['priority' => 'routine', 'state' => 'sent', 'status' => 1, 'sort_order' => 0];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
