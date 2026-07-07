<?php

namespace App\Enums;

class BillRefundStatusEnum extends BaseEnum
{
    const PENDING_APPROVAL = 'pending_approval';
    const APPROVED         = 'approved';
    const REJECTED         = 'rejected';
    const PROCESSED        = 'processed';

    public static $valueMap = [
        self::PENDING_APPROVAL => 'Pending Approval',
        self::APPROVED         => 'Approved',
        self::REJECTED         => 'Rejected',
        self::PROCESSED        => 'Processed',
    ];

    public static $allowedTransitions = [
        self::PENDING_APPROVAL => [self::APPROVED, self::REJECTED],
        self::APPROVED         => [self::PROCESSED],
        self::REJECTED         => [],
        self::PROCESSED        => [],
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
