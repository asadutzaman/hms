<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionTemplate extends BaseModel
{
    public static $uuIdPrefix = '';

    use SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'organogram_id',
        'name',
        'is_shared',
        'status',
        'sort_order',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'id'         => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status'     => 'integer',
        'is_shared'  => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status'    => StatusEnum::ACTIVE,
        'is_shared' => false,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionTemplateItem::class, 'prescription_template_id')->orderBy('sequence');
    }
}
