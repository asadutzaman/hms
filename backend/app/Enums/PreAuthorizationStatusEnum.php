<?php

namespace App\Enums;

class PreAuthorizationStatusEnum extends BaseEnum
{
    const SUBMITTED    = 'submitted';
    const UNDER_REVIEW = 'under_review';
    const APPROVED     = 'approved';
    const REJECTED     = 'rejected';
    const EXPIRED      = 'expired';
    const CANCELLED    = 'cancelled';

    public static $valueMap = [
        self::SUBMITTED    => 'Submitted',
        self::UNDER_REVIEW => 'Under Review',
        self::APPROVED     => 'Approved',
        self::REJECTED     => 'Rejected',
        self::EXPIRED      => 'Expired',
        self::CANCELLED    => 'Cancelled',
    ];

    public static $allowedTransitions = [
        self::SUBMITTED    => [self::UNDER_REVIEW, self::APPROVED, self::REJECTED, self::CANCELLED],
        self::UNDER_REVIEW => [self::APPROVED, self::REJECTED, self::EXPIRED, self::CANCELLED],
        self::APPROVED     => [],
        self::REJECTED     => [],
        self::EXPIRED      => [],
        self::CANCELLED    => [],
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
