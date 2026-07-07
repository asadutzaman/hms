<?php

namespace App\Enums;

class LabOrderItemStatusEnum extends BaseEnum
{
    const ORDERED          = 'ordered';
    const SAMPLE_COLLECTED = 'sample_collected';
    const IN_PROGRESS      = 'in_progress';
    const ENTERED          = 'entered';
    const VERIFIED         = 'verified';
    const CANCELLED        = 'cancelled';

    public static $valueMap = [
        self::ORDERED          => 'Ordered',
        self::SAMPLE_COLLECTED => 'Sample Collected',
        self::IN_PROGRESS      => 'In Progress',
        self::ENTERED          => 'Result Entered',
        self::VERIFIED         => 'Verified',
        self::CANCELLED        => 'Cancelled',
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
