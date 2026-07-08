<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgeryNote extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'surgery_notes';

    protected $fillable = [
        'organogram_id',
        'ot_booking_id',
        'pre_op_notes',
        'who_sign_in_checklist',
        'who_sign_in_by',
        'who_sign_in_at',
        'who_time_out_checklist',
        'who_time_out_by',
        'who_time_out_at',
        'who_sign_out_checklist',
        'who_sign_out_by',
        'who_sign_out_at',
        'procedure_performed',
        'intra_op_notes',
        'post_op_notes',
        'complications',
        'surgeon_signed_by',
        'surgeon_signed_at',
        'created_by',
        'updated_by',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'                     => 'integer',
        'organogram_id'          => 'integer',
        'ot_booking_id'          => 'integer',
        'who_sign_in_by'         => 'integer',
        'who_time_out_by'        => 'integer',
        'who_sign_out_by'        => 'integer',
        'surgeon_signed_by'      => 'integer',
        'created_by'             => 'integer',
        'updated_by'             => 'integer',
        'sort_order'             => 'integer',
        'status'                 => 'integer',
        'who_sign_in_checklist'  => 'array',
        'who_time_out_checklist' => 'array',
        'who_sign_out_checklist' => 'array',
        'who_sign_in_at'         => 'datetime:Y-m-d H:i:s',
        'who_time_out_at'        => 'datetime:Y-m-d H:i:s',
        'who_sign_out_at'        => 'datetime:Y-m-d H:i:s',
        'surgeon_signed_at'      => 'datetime:Y-m-d H:i:s',
        'created_at'             => 'datetime:Y-m-d H:i:s',
        'updated_at'             => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'     => StatusEnum::ACTIVE,
        'sort_order' => 0,
    ];

    public function otBooking(): BelongsTo
    {
        return $this->belongsTo(OtBooking::class, 'ot_booking_id');
    }
}
