<?php

namespace App\Enums;

class RadOrderStatusEnum extends BaseEnum
{
    const ORDERED     = 'ordered';
    const IN_PROGRESS = 'in_progress';
    const REPORTED    = 'reported';
    const CANCELLED   = 'cancelled';

    public static $valueMap = [
        self::ORDERED     => 'Ordered',
        self::IN_PROGRESS => 'In Progress',
        self::REPORTED    => 'Reported',
        self::CANCELLED   => 'Cancelled',
    ];

    public static $allowedTransitions = [
        self::ORDERED     => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS => [self::REPORTED, self::CANCELLED],
        self::REPORTED    => [],
        self::CANCELLED   => [],
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
