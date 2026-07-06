<?php

namespace App\Enums;

class IpdAdmissionActionEnum extends BaseEnum
{
    const ADMIT              = 'admit';
    const BED_TRANSFER       = 'bed_transfer';
    const DISCHARGE          = 'discharge';
    const DISCHARGE_OVERRIDE = 'discharge_override';
    const DAMA               = 'dama';
    const DECEASED           = 'deceased';
    const UPDATE             = 'update';
    const BILL_GENERATED     = 'bill_generated';
    const PAYMENT_RECORDED   = 'payment_recorded';
    const DISCOUNT_REQUESTED = 'discount_requested';
    const DISCOUNT_APPROVED  = 'discount_approved';
    const DISCOUNT_REJECTED  = 'discount_rejected';
    const ADVANCE_RECEIVED   = 'advance_received';
    const ADVANCE_APPLIED    = 'advance_applied';

    public static $valueMap = [
        self::ADMIT              => 'Admitted',
        self::BED_TRANSFER       => 'Bed Transferred',
        self::DISCHARGE          => 'Discharged',
        self::DISCHARGE_OVERRIDE => 'Discharged With Balance (Override)',
        self::DAMA               => 'Discharged Against Medical Advice',
        self::DECEASED           => 'Deceased',
        self::UPDATE             => 'Updated',
        self::BILL_GENERATED     => 'Bill Generated',
        self::PAYMENT_RECORDED   => 'Payment Recorded',
        self::DISCOUNT_REQUESTED => 'Discount Requested',
        self::DISCOUNT_APPROVED  => 'Discount Approved',
        self::DISCOUNT_REJECTED  => 'Discount Rejected',
        self::ADVANCE_RECEIVED   => 'Advance Received',
        self::ADVANCE_APPLIED    => 'Advance Applied',
    ];

    public static function getList()
    {
        return self::$valueMap;
    }

    public static function getValue($value)
    {
        return (string) (isset(self::$valueMap[$value]) && self::$valueMap[$value] ? self::$valueMap[$value] : $value);
    }

    public static function getValues()
    {
        return array_values(self::$valueMap);
    }

    public static function getKey($value)
    {
        return (string) array_search($value, self::$valueMap, true);
    }

    public static function getKeys()
    {
        return array_keys(self::$valueMap);
    }

    public static function label($value)
    {
        return self::getValue($value);
    }
}
