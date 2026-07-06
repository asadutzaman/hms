<?php

namespace App\Enums;

class IpdAdmissionStatusEnum extends BaseEnum
{
    const ADMITTED  = 'admitted';
    const DISCHARGED = 'discharged';
    const DAMA       = 'dama';
    const DECEASED   = 'deceased';

    public static $valueMap = [
        self::ADMITTED   => 'Admitted',
        self::DISCHARGED => 'Discharged',
        self::DAMA       => 'Discharged Against Medical Advice',
        self::DECEASED   => 'Deceased',
    ];

    /**
     * Bed transfer does NOT change admission_status — only discharge/dama/deceased
     * end the admission. All three exits are terminal.
     */
    public static $allowedTransitions = [
        self::ADMITTED   => [self::DISCHARGED, self::DAMA, self::DECEASED],
        self::DISCHARGED => [],
        self::DAMA       => [],
        self::DECEASED   => [],
    ];

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::DISCHARGED, self::DAMA, self::DECEASED], true);
    }

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
