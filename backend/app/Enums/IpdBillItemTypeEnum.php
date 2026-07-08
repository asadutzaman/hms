<?php

namespace App\Enums;

class IpdBillItemTypeEnum extends BaseEnum
{
    const ROOM_CHARGE   = 'room_charge';
    const CONSULTATION  = 'consultation';
    const NURSING       = 'nursing';
    const PROCEDURE     = 'procedure';
    const INVESTIGATION = 'investigation';
    const PHARMACY      = 'pharmacy';
    const PACKAGE       = 'package';
    const INSURANCE_ADJUSTMENT = 'insurance_adjustment';
    const OTHER         = 'other';

    public static $valueMap = [
        self::ROOM_CHARGE   => 'Room Charge',
        self::CONSULTATION  => 'Consultation',
        self::NURSING       => 'Nursing',
        self::PROCEDURE     => 'Procedure',
        self::INVESTIGATION => 'Investigation',
        self::PHARMACY      => 'Pharmacy',
        self::PACKAGE       => 'Package',
        self::INSURANCE_ADJUSTMENT => 'Insurance Adjustment',
        self::OTHER         => 'Other',
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
