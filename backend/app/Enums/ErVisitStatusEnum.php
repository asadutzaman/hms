<?php

namespace App\Enums;

class ErVisitStatusEnum extends BaseEnum
{
    const WAITING_TRIAGE = 'waiting_triage';
    const TRIAGED        = 'triaged';
    const IN_TREATMENT   = 'in_treatment';
    const ADMITTED       = 'admitted';
    const DISCHARGED     = 'discharged';
    const LWBS           = 'lwbs';
    const DECEASED       = 'deceased';

    public static $valueMap = [
        self::WAITING_TRIAGE => 'Waiting for Triage',
        self::TRIAGED        => 'Triaged',
        self::IN_TREATMENT   => 'In Treatment',
        self::ADMITTED       => 'Admitted',
        self::DISCHARGED     => 'Discharged',
        self::LWBS           => 'Left Without Being Seen',
        self::DECEASED       => 'Deceased',
    ];

    public static $allowedTransitions = [
        self::WAITING_TRIAGE => [self::TRIAGED, self::LWBS],
        self::TRIAGED        => [self::IN_TREATMENT, self::LWBS],
        self::IN_TREATMENT   => [self::ADMITTED, self::DISCHARGED, self::DECEASED],
        self::ADMITTED       => [],
        self::DISCHARGED     => [],
        self::LWBS           => [],
        self::DECEASED       => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        $allowed = self::$allowedTransitions[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    public static function isTerminal(string $status): bool
    {
        return empty(self::$allowedTransitions[$status]);
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
