<?php

namespace App\Enums;

class InsuranceClaimStatusEnum extends BaseEnum
{
    const DRAFT               = 'draft';
    const SUBMITTED            = 'submitted';
    const UNDER_REVIEW         = 'under_review';
    const APPROVED             = 'approved';
    const PARTIALLY_APPROVED   = 'partially_approved';
    const REJECTED             = 'rejected';
    const SETTLED              = 'settled';

    public static $valueMap = [
        self::DRAFT             => 'Draft',
        self::SUBMITTED         => 'Submitted',
        self::UNDER_REVIEW      => 'Under Review',
        self::APPROVED          => 'Approved',
        self::PARTIALLY_APPROVED => 'Partially Approved',
        self::REJECTED          => 'Rejected',
        self::SETTLED           => 'Settled',
    ];

    public static $allowedTransitions = [
        self::DRAFT              => [self::SUBMITTED],
        self::SUBMITTED          => [self::UNDER_REVIEW, self::REJECTED],
        self::UNDER_REVIEW       => [self::APPROVED, self::PARTIALLY_APPROVED, self::REJECTED],
        self::APPROVED           => [self::SETTLED],
        self::PARTIALLY_APPROVED => [self::SETTLED],
        self::REJECTED           => [],
        self::SETTLED            => [],
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
