<?php

namespace App\Enums;

/**
 * Values are lowercase to match the ot_bookings.booking_status column
 * directly (no DB CHECK constraint on this column, but kept lowercase for
 * consistency with the rest of the app's string-status columns — see the
 * AppointmentStatusEnum case-mismatch bug documented in
 * project_hms_sprint8_scope memory; this enum's values are used as-is, not
 * uppercase constants mapped to lowercase storage).
 */
class OtBookingStatusEnum extends BaseEnum
{
    const SCHEDULED   = 'scheduled';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED   = 'completed';
    const CANCELLED   = 'cancelled';

    public static $valueMap = [
        self::SCHEDULED   => 'Scheduled',
        self::IN_PROGRESS => 'In Progress',
        self::COMPLETED   => 'Completed',
        self::CANCELLED   => 'Cancelled',
    ];

    public static function getList()
    {
        return self::$valueMap;
    }

    public static function label($value)
    {
        return self::$valueMap[$value] ?? $value;
    }
}
