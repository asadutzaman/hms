<?php

namespace App\Enums;

class IpdDiscountStatusEnum extends BaseEnum
{
    const NONE              = 'none';
    const PENDING_APPROVAL  = 'pending_approval';
    const APPROVED          = 'approved';
    const REJECTED          = 'rejected';

    public static $valueMap = [
        self::NONE             => 'None',
        self::PENDING_APPROVAL => 'Pending Approval',
        self::APPROVED         => 'Approved',
        self::REJECTED         => 'Rejected',
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
