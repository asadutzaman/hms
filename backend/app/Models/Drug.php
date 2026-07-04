<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'item_id',
        'generic_name',
        'brand_name',
        'strength',
        'dosage_form',
        'hsn_code',
        'is_controlled',
        'controlled_schedule',
        'generic_drug_id',
        'status',
        'sort_order',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'               => 'integer',
        'organogram_id'    => 'integer',
        'item_id'          => 'integer',
        'generic_drug_id'  => 'integer',
        'is_controlled'    => 'boolean',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'sort_order'       => 'integer',
        'status'           => 'integer',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'        => StatusEnum::ACTIVE,
        'dosage_form'   => 'tablet',
        'is_controlled' => false,
        'sort_order'    => 0,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'generic_drug_id');
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Drug::class, 'generic_drug_id');
    }
}
