<?php

namespace App\Enums;

class IpdMedicationOrderStatusEnum extends BaseEnum
{
    const ACTIVE       = 'active';
    const DISCONTINUED = 'discontinued';
    const COMPLETED    = 'completed';

    public static $valueMap = [
        self::ACTIVE       => 'Active',
        self::DISCONTINUED => 'Discontinued',
        self::COMPLETED    => 'Completed',
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
