<?php

namespace App\Enums;

class OpdInvestigationOrderStatusEnum extends BaseEnum
{
    public const ORDERED         = 'ordered';
    public const SAMPLE_COLLECTED = 'sample_collected';
    public const IN_PROGRESS     = 'in_progress';
    public const REPORTED        = 'reported';
    public const CANCELLED       = 'cancelled';

    public static array $valueMap = [
        self::ORDERED         => 'Ordered',
        self::SAMPLE_COLLECTED => 'Sample Collected',
        self::IN_PROGRESS     => 'In Progress',
        self::REPORTED        => 'Reported',
        self::CANCELLED       => 'Cancelled',
    ];

    public static array $allowedTransitions = [
        self::ORDERED          => [self::SAMPLE_COLLECTED, self::IN_PROGRESS, self::CANCELLED],
        self::SAMPLE_COLLECTED => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS      => [self::REPORTED, self::CANCELLED],
        self::REPORTED         => [],
        self::CANCELLED        => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::$allowedTransitions[$from] ?? [], true);
    }

    public static function isTerminal(string $status): bool
    {
        return empty(self::$allowedTransitions[$status] ?? []);
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
