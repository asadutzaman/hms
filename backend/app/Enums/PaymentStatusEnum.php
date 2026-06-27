<?php

namespace App\Enums;

class PaymentStatusEnum extends BaseEnum
{
    const UNPAID = 'UNPAID';
    const PAID   = 'PAID';
    const PARTIAL = 'PARTIAL';
    const REFUNDED = 'REFUNDED';

    public static $valueMap = [
        self::UNPAID  => 'Unpaid',
        self::PAID    => 'Paid',
        self::PARTIAL => 'Partial',
        self::REFUNDED => 'Refunded',
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
        return array_values(self::getList());
    }

    public static function getKey($value)
    {
        return (string) array_search($value, self::getList(), true);
    }

    public static function getKeys()
    {
        return array_keys(self::getList());
    }

    public static function label($value)
    {
        return self::getValue($value);
    }
}