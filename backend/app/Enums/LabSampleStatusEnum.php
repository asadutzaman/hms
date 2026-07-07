<?php

namespace App\Enums;

class LabSampleStatusEnum extends BaseEnum
{
    const PENDING_COLLECTION = 'pending_collection';
    const COLLECTED          = 'collected';
    const RECEIVED           = 'received';
    const REJECTED           = 'rejected';

    public static $valueMap = [
        self::PENDING_COLLECTION => 'Pending Collection',
        self::COLLECTED          => 'Collected',
        self::RECEIVED           => 'Received in Lab',
        self::REJECTED           => 'Rejected',
    ];

    public static $allowedTransitions = [
        self::PENDING_COLLECTION => [self::COLLECTED, self::REJECTED],
        self::COLLECTED          => [self::RECEIVED, self::REJECTED],
        self::RECEIVED           => [],
        self::REJECTED           => [],
    ];

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
