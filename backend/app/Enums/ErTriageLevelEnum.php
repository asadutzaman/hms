<?php

namespace App\Enums;

/**
 * ESI-style 5-level acuity scale (also commonly mapped 1:1 to CTAS in
 * practice) — level drives both the printed colour band and the target
 * time-to-be-seen used to drive the ward's triage timer.
 */
class ErTriageLevelEnum extends BaseEnum
{
    const LEVEL_1 = 1;
    const LEVEL_2 = 2;
    const LEVEL_3 = 3;
    const LEVEL_4 = 4;
    const LEVEL_5 = 5;

    public static $valueMap = [
        self::LEVEL_1 => 'Level 1 — Resuscitation',
        self::LEVEL_2 => 'Level 2 — Emergent',
        self::LEVEL_3 => 'Level 3 — Urgent',
        self::LEVEL_4 => 'Level 4 — Less Urgent',
        self::LEVEL_5 => 'Level 5 — Non-Urgent',
    ];

    public static $colorMap = [
        self::LEVEL_1 => 'red',
        self::LEVEL_2 => 'orange',
        self::LEVEL_3 => 'yellow',
        self::LEVEL_4 => 'green',
        self::LEVEL_5 => 'blue',
    ];

    /** Target time-to-be-seen, in minutes, by acuity level. */
    public static $targetMinutesMap = [
        self::LEVEL_1 => 0,
        self::LEVEL_2 => 10,
        self::LEVEL_3 => 30,
        self::LEVEL_4 => 60,
        self::LEVEL_5 => 120,
    ];

    public static function color($value): string
    {
        return self::$colorMap[$value] ?? 'default';
    }

    public static function targetMinutes($value): int
    {
        return self::$targetMinutesMap[$value] ?? 120;
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
