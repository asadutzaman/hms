<?php

namespace App\Models;

use App\Traits\Model\Autofill;
use App\Traits\Model\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReview extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $table = 'daily_reviews';

    protected $fillable = [
        'organogram_id', 'ipd_admission_id', 'author_user_id', 'review_date',
        'progress_note', 'assessment', 'plan', 'obs_snapshot',
        'created_by', 'updated_by', 'sort_order', 'status',
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'id'               => 'integer',
        'ipd_admission_id' => 'integer',
        'author_user_id'   => 'integer',
        'review_date'      => 'date:Y-m-d',
        'obs_snapshot'     => 'array',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = ['status' => 1, 'sort_order' => 0];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(IpdAdmission::class, 'ipd_admission_id');
    }
}
