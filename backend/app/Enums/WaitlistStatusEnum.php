<?php

namespace App\Enums;

class WaitlistStatusEnum extends BaseEnum
{
    const WAITING   = 'WAITING';
    const NOTIFIED  = 'NOTIFIED';
    const CONVERTED = 'CONVERTED';
    const EXPIRED   = 'EXPIRED';
    const CANCELLED = 'CANCELLED';

    public static $valueMap = [
        self::WAITING   => 'Waiting',
        self::NOTIFIED  => 'Notified',
        self::CONVERTED => 'Converted',
        self::EXPIRED   => 'Expired',
        self::CANCELLED => 'Cancelled',
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
        return array_values(self::getList());
    }

    public static function getKey($value)
    {
        return (string) array_search($value, self::getList(), true);
    }

    public static function getKeys()
    {
        return array_keys(self::getList());
    }

    public static function label($value)
    {
        return self::getValue($value);
    }
}