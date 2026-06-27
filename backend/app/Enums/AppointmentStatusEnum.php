<?php

namespace App\Enums;

class AppointmentStatusEnum extends BaseEnum
{
    const PENDING         = 'PENDING';
    const CONFIRMED       = 'CONFIRMED';
    const CHECKED_IN      = 'CHECKED_IN';
    const IN_CONSULTATION = 'IN_CONSULTATION';
    const COMPLETED       = 'COMPLETED';
    const CANCELLED       = 'CANCELLED';
    const NO_SHOW         = 'NO_SHOW';
    const RESCHEDULED     = 'RESCHEDULED';

    public static $valueMap = [
        self::PENDING         => 'Pending',
        self::CONFIRMED       => 'Confirmed',
        self::CHECKED_IN      => 'Checked In',
        self::IN_CONSULTATION => 'In Consultation',
        self::COMPLETED       => 'Completed',
        self::CANCELLED       => 'Cancelled',
        self::NO_SHOW         => 'No Show',
        self::RESCHEDULED     => 'Rescheduled',
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