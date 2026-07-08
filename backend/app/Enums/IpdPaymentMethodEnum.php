<?php

namespace App\Enums;

class IpdPaymentMethodEnum extends BaseEnum
{
    const CASH      = 'cash';
    const CARD      = 'card';
    const BANK      = 'bank';
    const MOBILE    = 'mobile';
    const INSURANCE = 'insurance';
    const CHEQUE    = 'cheque';
    const ADVANCE   = 'advance';
    const ONLINE    = 'online';
    const OTHER     = 'other';

    public static $valueMap = [
        self::CASH      => 'Cash',
        self::CARD      => 'Card',
        self::BANK      => 'Bank Transfer',
        self::MOBILE    => 'Mobile Banking',
        self::INSURANCE => 'Insurance',
        self::CHEQUE    => 'Cheque',
        self::ADVANCE   => 'Applied Advance',
        self::ONLINE    => 'Online Payment',
        self::OTHER     => 'Other',
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
