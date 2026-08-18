<?php

namespace App\Support;

/**
 * Agent schedules:
 * - Batch scan: daily clock time HH:mm
 * - TI sync: interval minutes
 * - Agent update check: day-based intervals (stored as minutes)
 */
class AgentScheduleInterval
{
    const PRESETS = [1, 5, 10, 15, 20, 25, 30, 45, 60, 120, 180, 360, 720, 1440];

    /** Day cadence for agent update checks (stored as days * 1440 minutes). */
    const AGENT_UPDATE_DAY_PRESETS = [1, 3, 7, 14, 30, 60, 90];

    /** @deprecated batch uses DEFAULT_BATCH_TIME (HH:mm); kept for older callers */
    const DEFAULT_BATCH = 1440;
    const DEFAULT_BATCH_TIME = '02:00';
    const DEFAULT_TI_SYNC = 60;
    /** Default agent update: every 1 day (minutes). */
    const DEFAULT_AGENT_UPDATE = 1440;

    /**
     * @return array<int,string> minutes => label
     */
    public static function options()
    {
        $out = [];
        foreach (self::PRESETS as $m) {
            $out[$m] = self::label($m);
        }
        return $out;
    }

    /**
     * Agent update options: every N days (value = minutes).
     *
     * @return array<int,string> minutes => label
     */
    public static function agentUpdateOptions()
    {
        $out = [];
        foreach (self::AGENT_UPDATE_DAY_PRESETS as $days) {
            $mins = (int) $days * 1440;
            $out[$mins] = self::agentUpdateLabelDays($days);
        }
        return $out;
    }

    public static function agentUpdateLabelDays($days)
    {
        $d = (int) $days;
        if ($d <= 1) {
            return 'Every day';
        }
        return 'Every '.$d.' days';
    }

    /**
     * Normalize agent update schedule onto day presets (minutes).
     * Legacy minute/hour values and HH:mm map to nearest day preset.
     */
    public static function normalizeAgentUpdate($value, $defaultMinutes = self::DEFAULT_AGENT_UPDATE)
    {
        $default = self::snapAgentUpdateMinutes((int) $defaultMinutes);
        $s = trim((string) $value);
        if ($s === '' || strpos($s, ':') !== false) {
            return (string) $default;
        }
        if (!ctype_digit($s) && !preg_match('/^\d+$/', $s)) {
            return (string) $default;
        }
        return (string) self::snapAgentUpdateMinutes((int) $s);
    }

    public static function snapAgentUpdateMinutes($minutes)
    {
        $m = (int) $minutes;
        if ($m < 1) {
            $m = self::DEFAULT_AGENT_UPDATE;
        }
        $presets = [];
        foreach (self::AGENT_UPDATE_DAY_PRESETS as $days) {
            $presets[] = (int) $days * 1440;
        }
        if (in_array($m, $presets, true)) {
            return $m;
        }
        // Values that look like "days" (1–90) without minutes scaling.
        if ($m <= 90 && in_array($m, self::AGENT_UPDATE_DAY_PRESETS, true)) {
            return $m * 1440;
        }
        $best = $presets[0];
        $bestDist = abs($best - $m);
        foreach ($presets as $p) {
            $d = abs($p - $m);
            if ($d < $bestDist || ($d === $bestDist && $p > $best)) {
                $best = $p;
                $bestDist = $d;
            }
        }
        return $best;
    }

    /**
     * Daily HH:mm options (30-minute steps).
     *
     * @return array<string,string> value => label
     */
    public static function dailyTimeOptions()
    {
        $out = [];
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 30) {
                $v = sprintf('%02d:%02d', $h, $m);
                $out[$v] = $v;
            }
        }
        return $out;
    }

    /**
     * Normalize batch schedule to HH:mm (30-minute snap).
     * Legacy interval-minute values map to DEFAULT_BATCH_TIME.
     */
    public static function normalizeDailyTime($value, $default = self::DEFAULT_BATCH_TIME)
    {
        $fallback = self::DEFAULT_BATCH_TIME;
        $def = trim((string) $default);
        if ($def === '' || !preg_match('/^(\d{1,2}):(\d{1,2})$/', $def)) {
            $def = $fallback;
        } else {
            $def = self::formatDailyTimeParts((int) explode(':', $def)[0], (int) explode(':', $def)[1]);
        }

        $s = trim((string) $value);
        if ($s === '' || strpos($s, ':') === false) {
            return $def;
        }
        if (!preg_match('/^(\d{1,2}):(\d{1,2})$/', $s, $m)) {
            return $def;
        }
        return self::formatDailyTimeParts((int) $m[1], (int) $m[2], $def);
    }

    private static function formatDailyTimeParts($hour, $min, $fallback = self::DEFAULT_BATCH_TIME)
    {
        if ($hour < 0 || $hour > 23 || $min < 0 || $min > 59) {
            return $fallback;
        }
        if ($min < 15) {
            $min = 0;
        } elseif ($min < 45) {
            $min = 30;
        } else {
            $min = 0;
            $hour = ($hour + 1) % 24;
        }
        return sprintf('%02d:%02d', $hour, $min);
    }

    public static function label($minutes)
    {
        $m = (int) $minutes;
        if ($m < 60) {
            return $m === 1 ? 'Every 1 minute' : 'Every '.$m.' minutes';
        }
        $h = (int) ($m / 60);
        if ($m % 60 === 0) {
            return $h === 1 ? 'Every 1 hour' : 'Every '.$h.' hours';
        }
        return 'Every '.$m.' minutes';
    }

    /**
     * Normalize any stored/API value to a preset minute string (TI sync).
     */
    public static function normalize($value, $defaultMinutes)
    {
        $default = self::snapToPreset((int) $defaultMinutes);
        $s = trim((string) $value);
        if ($s === '') {
            return (string) $default;
        }

        // Legacy daily clock HH:mm → field default.
        if (strpos($s, ':') !== false) {
            return (string) $default;
        }

        if (!ctype_digit($s) && !preg_match('/^\d+$/', $s)) {
            return (string) $default;
        }

        return (string) self::snapToPreset((int) $s);
    }

    public static function snapToPreset($minutes)
    {
        $m = (int) $minutes;
        if ($m < 1) {
            $m = 1;
        }
        if (in_array($m, self::PRESETS, true)) {
            return $m;
        }
        // Nearest preset (prefer higher if tie).
        $best = self::PRESETS[0];
        $bestDist = abs($best - $m);
        foreach (self::PRESETS as $p) {
            $d = abs($p - $m);
            if ($d < $bestDist || ($d === $bestDist && $p > $best)) {
                $best = $p;
                $bestDist = $d;
            }
        }
        return $best;
    }
}
