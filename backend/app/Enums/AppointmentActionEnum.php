<?php

namespace App\Enums;

class AppointmentActionEnum extends BaseEnum
{
    const BOOKED       = 'BOOKED';
    const RESCHEDULED  = 'RESCHEDULED';
    const CANCELLED    = 'CANCELLED';
    const CONFIRMED    = 'CONFIRMED';
    const CHECKED_IN   = 'CHECKED_IN';
    const STARTED      = 'STARTED';
    const COMPLETED    = 'COMPLETED';
    const NO_SHOW      = 'NO_SHOW';
    const TOKEN_ISSUED = 'TOKEN_ISSUED';
    const PAYMENT_UPDATED = 'PAYMENT_UPDATED';

    public static $valueMap = [
        self::BOOKED          => 'Booked',
        self::RESCHEDULED     => 'Rescheduled',
        self::CANCELLED       => 'Cancelled',
        self::CONFIRMED       => 'Confirmed',
        self::CHECKED_IN      => 'Checked In',
        self::STARTED         => 'Started',
        self::COMPLETED       => 'Completed',
        self::NO_SHOW         => 'No Show',
        self::TOKEN_ISSUED    => 'Token Issued',
        self::PAYMENT_UPDATED => 'Payment Updated',
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