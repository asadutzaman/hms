<?php

namespace App\Enums;

class OpdPaymentMethodEnum extends BaseEnum
{
    const CASH      = 'cash';
    const CARD      = 'card';
    const INSURANCE = 'insurance';
    const MOBILE    = 'mobile';
    const OTHER     = 'other';

    public static $valueMap = [
        self::CASH      => 'Cash',
        self::CARD      => 'Card',
        self::INSURANCE => 'Insurance',
        self::MOBILE    => 'Mobile Banking',
        self::OTHER     => 'Other',
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