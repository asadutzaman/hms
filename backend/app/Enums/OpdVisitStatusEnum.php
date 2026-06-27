<?php

namespace App\Enums;

class OpdVisitStatusEnum extends BaseEnum
{
    const WAITING         = 'waiting';
    const VITALS_TAKEN    = 'vitals_taken';
    const IN_CONSULTATION = 'in_consultation';
    const COMPLETED       = 'completed';
    const BILLED          = 'billed';
    const CLOSED          = 'closed';
    const CANCELLED       = 'cancelled';

    public static $valueMap = [
        self::WAITING         => 'Waiting',
        self::VITALS_TAKEN    => 'Vitals Taken',
        self::IN_CONSULTATION => 'In Consultation',
        self::COMPLETED       => 'Completed',
        self::BILLED          => 'Billed',
        self::CLOSED          => 'Closed',
        self::CANCELLED       => 'Cancelled',
    ];

    /**
     * Allowed forward transitions. `cancelled` is allowed from any non-terminal
     * state and is enforced separately by the repository (it terminates the visit).
     */
    public static $allowedTransitions = [
        self::WAITING         => [self::VITALS_TAKEN, self::IN_CONSULTATION, self::CANCELLED],
        self::VITALS_TAKEN    => [self::IN_CONSULTATION, self::CANCELLED],
        self::IN_CONSULTATION => [self::COMPLETED, self::CANCELLED],
        self::COMPLETED       => [self::BILLED, self::CLOSED, self::CANCELLED],
        self::BILLED          => [self::CLOSED, self::CANCELLED],
        self::CLOSED          => [],
        self::CANCELLED       => [],
    ];

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::CLOSED, self::CANCELLED], true);
    }

    public static function canTransition(string $from, string $to): bool
    {
        $allowed = self::$allowedTransitions[$from] ?? [];
        return in_array($to, $allowed, true);
    }

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