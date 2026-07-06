<?php

namespace App\Enums;

class IpdMedicationAdministrationStatusEnum extends BaseEnum
{
    const SCHEDULED = 'scheduled';
    const GIVEN     = 'given';
    const HELD      = 'held';
    const REFUSED   = 'refused';
    const MISSED    = 'missed';

    public static $valueMap = [
        self::SCHEDULED => 'Scheduled',
        self::GIVEN     => 'Given',
        self::HELD      => 'Held',
        self::REFUSED   => 'Refused',
        self::MISSED    => 'Missed',
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
