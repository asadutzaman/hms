<?php

namespace App\Enums;

class OpdVisitActionEnum extends BaseEnum
{
    const CREATE         = 'create';
    const STATUS_CHANGE  = 'status_change';
    const UPDATE         = 'update';
    const VITALS_SAVED   = 'vitals_saved';
    const DIAGNOSIS_SAVED = 'diagnosis_saved';
    const PRESCRIPTION_SAVED = 'prescription_saved';
    const ORDER_SAVED    = 'order_saved';
    const BILL_GENERATED = 'bill_generated';
    const PAYMENT_RECORDED = 'payment_recorded';
    const WAIVED         = 'waived';
    const CANCEL         = 'cancel';
    const CLOSE          = 'close';
    const CALLED         = 'called';
    const FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';
    const DISCOUNT_REQUESTED = 'discount_requested';
    const DISCOUNT_APPROVED = 'discount_approved';
    const DISCOUNT_REJECTED = 'discount_rejected';

    public static $valueMap = [
        self::CREATE             => 'Created',
        self::STATUS_CHANGE      => 'Status Changed',
        self::UPDATE             => 'Updated',
        self::VITALS_SAVED       => 'Vitals Saved',
        self::DIAGNOSIS_SAVED    => 'Diagnoses Saved',
        self::PRESCRIPTION_SAVED => 'Prescription Saved',
        self::ORDER_SAVED        => 'Investigation Order Saved',
        self::BILL_GENERATED     => 'Bill Generated',
        self::PAYMENT_RECORDED   => 'Payment Recorded',
        self::WAIVED             => 'Waived',
        self::CANCEL             => 'Cancelled',
        self::CLOSE              => 'Closed',
        self::CALLED             => 'Token Called',
        self::FOLLOW_UP_SCHEDULED => 'Follow-up Scheduled',
        self::DISCOUNT_REQUESTED => 'Discount Requested',
        self::DISCOUNT_APPROVED  => 'Discount Approved',
        self::DISCOUNT_REJECTED  => 'Discount Rejected',
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