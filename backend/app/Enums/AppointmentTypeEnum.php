<?php

namespace App\Enums;

class AppointmentTypeEnum extends BaseEnum
{
    const ONLINE  = 'ONLINE';
    const WALK_IN = 'WALK_IN';
    const FOLLOW_UP = 'FOLLOW_UP';

    public static $valueMap = [
        self::ONLINE    => 'Online',
        self::WALK_IN   => 'Walk-in',
        self::FOLLOW_UP => 'Follow-up',
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