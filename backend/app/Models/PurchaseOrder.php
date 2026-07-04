<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\Model\Uuid;
use App\Traits\Model\Autofill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends BaseModel
{
    public static $uuIdPrefix = ''; // C-

    use  SoftDeletes, Autofill, Uuid;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'branch_id',
        'order_date',
        'expected_delivery_date',
        'po_status',
        'process_status',
        'notes',
        'requisition_id',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        // Integer
        'id'               => 'integer',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'status'           => 'integer',
        'supplier_id'      => 'integer',
        'branch_id'        => 'integer',
        'requisition_id'   => 'integer',
        'approved_by'      => 'integer',
        //Date
        'order_date'             => 'date:Y-m-d',
        'expected_delivery_date' => 'date:Y-m-d',
        //Date Time
        'approved_at'      => 'datetime:Y-m-d H:i:s',
        'created_at'       => 'datetime:Y-m-d H:i:s',
        'updated_at'       => 'datetime:Y-m-d H:i:s',
        // String
        'po_number'        => 'string',
        'po_status'        => 'string',
        'process_status'   => 'string',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $attributes = [
        'status'         => StatusEnum::ACTIVE,
        'po_status'      => 'draft',
        'process_status' => 'DRAFT',
    ];

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id')->select(['id', 'supplier_name']);
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id')->select(['id', 'name']);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id', 'id');
    }
}
