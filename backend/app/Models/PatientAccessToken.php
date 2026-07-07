<?php

namespace App\Models;

use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAccessToken extends Model
{
    public static $uuIdPrefix = '';

    use Uuid;

    protected $table = 'patient_access_tokens';

    protected $guarded = [];

    protected $casts = [
        'id'          => 'integer',
        'patient_id'  => 'integer',
        'revoked'     => 'boolean',
        'expires_at'  => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
        'updated_at'  => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'revoked' => false,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
