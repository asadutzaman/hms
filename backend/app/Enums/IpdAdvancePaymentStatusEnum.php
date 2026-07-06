<?php

namespace App\Enums;

class IpdAdvancePaymentStatusEnum extends BaseEnum
{
    const RECEIVED          = 'received';
    const PARTIALLY_APPLIED = 'partially_applied';
    const FULLY_APPLIED     = 'fully_applied';
    const REFUNDED          = 'refunded';

    public static $valueMap = [
        self::RECEIVED          => 'Received',
        self::PARTIALLY_APPLIED => 'Partially Applied',
        self::FULLY_APPLIED     => 'Fully Applied',
        self::REFUNDED          => 'Refunded',
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
