<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAllergy extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'patient_id',
        'allergy_type',
        'allergen_name',
        'drug_id',
        'reaction_type',
        'severity',
        'notes',
        'recorded_by',
        'recorded_at',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'            => 'integer',
        'patient_id'    => 'integer',
        'drug_id'       => 'integer',
        'recorded_by'   => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'status'        => 'integer',
        //Date Time
        'recorded_at'   => 'datetime:Y-m-d H:i:s',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
        // String
        'allergy_type'  => 'string',
        'allergen_name' => 'string',
        'reaction_type' => 'string',
        'severity'      => 'string',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'       => StatusEnum::ACTIVE,
        'allergy_type' => 'drug',
        'severity'     => 'mild',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'drug_id')->select('id', 'name_en', 'name_bn', 'code');
    }
}
