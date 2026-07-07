<?php

namespace App\Enums;

class LabResultFlagEnum extends BaseEnum
{
    const NORMAL        = 'normal';
    const LOW           = 'low';
    const HIGH          = 'high';
    const CRITICAL_LOW  = 'critical_low';
    const CRITICAL_HIGH = 'critical_high';

    public static $valueMap = [
        self::NORMAL        => 'Normal',
        self::LOW           => 'Low',
        self::HIGH          => 'High',
        self::CRITICAL_LOW  => 'Critical Low',
        self::CRITICAL_HIGH => 'Critical High',
    ];

    public static function isCritical($value): bool
    {
        return in_array($value, [self::CRITICAL_LOW, self::CRITICAL_HIGH], true);
    }

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
