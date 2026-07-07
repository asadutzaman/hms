<?php

namespace App\Enums;

class NotificationChannelEnum extends BaseEnum
{
    const IN_APP = 'in_app';
    const EMAIL  = 'email';
    const SMS    = 'sms';

    public static $valueMap = [
        self::IN_APP => 'In-App',
        self::EMAIL  => 'Email',
        self::SMS    => 'SMS',
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
