<?php

namespace App\Enums;

class RadReportStatusEnum extends BaseEnum
{
    const DRAFT   = 'draft';
    const FINAL   = 'final';
    const AMENDED = 'amended';

    public static $valueMap = [
        self::DRAFT   => 'Draft',
        self::FINAL   => 'Final',
        self::AMENDED => 'Amended',
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
