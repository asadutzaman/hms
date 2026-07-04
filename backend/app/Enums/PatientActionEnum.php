<?php

namespace App\Enums;

class PatientActionEnum extends BaseEnum
{
    const CREATE       = 'create';
    const UPDATE       = 'update';
    const DELETE       = 'delete';
    const MERGED       = 'merged';
    const MERGED_AWAY  = 'merged_away';

    public static $valueMap = [
        self::CREATE      => 'Created',
        self::UPDATE      => 'Updated',
        self::DELETE      => 'Deleted',
        self::MERGED      => 'Merged (kept as survivor)',
        self::MERGED_AWAY => 'Merged into another record',
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
