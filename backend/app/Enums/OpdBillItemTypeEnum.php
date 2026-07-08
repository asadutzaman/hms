<?php

namespace App\Enums;

class OpdBillItemTypeEnum extends BaseEnum
{
    const CONSULTATION  = 'consultation';
    const PRESCRIPTION  = 'prescription';
    const INVESTIGATION = 'investigation';
    const PACKAGE       = 'package';
    const INSURANCE_ADJUSTMENT = 'insurance_adjustment';
    const OTHER         = 'other';

    public static $valueMap = [
        self::CONSULTATION  => 'Consultation',
        self::PRESCRIPTION  => 'Prescription',
        self::INVESTIGATION => 'Investigation',
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
