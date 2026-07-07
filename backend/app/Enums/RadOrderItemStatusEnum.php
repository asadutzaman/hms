<?php

namespace App\Enums;

class RadOrderItemStatusEnum extends BaseEnum
{
    const ORDERED     = 'ordered';
    const IN_PROGRESS = 'in_progress';
    const REPORTED    = 'reported';
    const VERIFIED    = 'verified';
    const CANCELLED   = 'cancelled';

    public static $valueMap = [
        self::ORDERED     => 'Ordered',
        self::IN_PROGRESS => 'In Progress',
        self::REPORTED    => 'Reported',
        self::VERIFIED    => 'Verified',
        self::CANCELLED   => 'Cancelled',
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
