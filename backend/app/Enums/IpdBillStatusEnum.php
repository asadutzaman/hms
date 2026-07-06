<?php

namespace App\Enums;

class IpdBillStatusEnum extends BaseEnum
{
    const UNPAID  = 'unpaid';
    const PARTIAL = 'partial';
    const PAID    = 'paid';
    const WAIVED  = 'waived';

    public static $valueMap = [
        self::UNPAID  => 'Unpaid',
        self::PARTIAL => 'Partial',
        self::PAID    => 'Paid',
        self::WAIVED  => 'Waived',
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
