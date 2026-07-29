<?php

namespace App\Enums;

class AppointmentStatusEnum extends BaseEnum
{
    // Values are lowercase to match the appointments.status CHECK constraint
    // (pending|confirmed|checked_in|in_consultation|completed|cancelled|
    // no_show|rescheduled|waitlisted|expired) and the seeded data. Uppercase
    // values here made every status write violate the check and every
    // `$appointment->status === self::X` comparison silently fail.
    const PENDING         = 'pending';
    const CONFIRMED       = 'confirmed';
    const CHECKED_IN      = 'checked_in';
    const IN_CONSULTATION = 'in_consultation';
    const COMPLETED       = 'completed';
    const CANCELLED       = 'cancelled';
    const NO_SHOW         = 'no_show';
    const RESCHEDULED     = 'rescheduled';

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