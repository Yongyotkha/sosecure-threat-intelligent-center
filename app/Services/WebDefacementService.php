<?php

namespace App\Services;

use Modules\WebDefacement\Entities\WebdefacmentDataCheck;

class WebDefacementService
{
    /** Canonical scan / stat_log statuses (Title Case — matches status_val UI). */
    public const STATUS_NORMAL  = 'Normal';
    public const STATUS_MEDIUM  = 'Medium';
    public const STATUS_HIGH    = 'High';
    public const STATUS_DOWN    = 'Down';
    public const STATUS_SKIPPED = 'Skipped';
    public const STATUS_ERROR   = 'Error';

    public const ALL_STATUSES = [
        self::STATUS_NORMAL,
        self::STATUS_MEDIUM,
        self::STATUS_HIGH,
        self::STATUS_DOWN,
        self::STATUS_SKIPPED,
        self::STATUS_ERROR,
    ];

    /**
     * Severity rank for "worst of day" aggregation (higher = worse).
     */
    public static function statusSeverity(?string $status): int
    {
        switch (self::normalizeStatus($status)) {
            case self::STATUS_HIGH:
                return 5;
            case self::STATUS_MEDIUM:
                return 4;
            case self::STATUS_DOWN:
                return 3;
            case self::STATUS_ERROR:
                return 2;
            case self::STATUS_NORMAL:
                return 1;
            case self::STATUS_SKIPPED:
            default:
                return 0;
        }
    }

    /**
     * Normalize any casing / legacy value to canonical Title Case.
     */
    public static function normalizeStatus(?string $status): string
    {
        if ($status === null || trim($status) === '') {
            return self::STATUS_NORMAL;
        }

        $map = [
            'normal'  => self::STATUS_NORMAL,
            'medium'  => self::STATUS_MEDIUM,
            'high'    => self::STATUS_HIGH,
            'down'    => self::STATUS_DOWN,
            'skipped' => self::STATUS_SKIPPED,
            'error'   => self::STATUS_ERROR,
            'critical'=> self::STATUS_HIGH,
        ];

        $key = strtolower(trim($status));

        return $map[$key] ?? self::STATUS_NORMAL;
    }

    /**
     * Map alert score (%) to defacement severity.
     */
    public static function statusFromScore($pointAlert): string
    {
        $score = (float) $pointAlert;

        if ($score >= 75) {
            return self::STATUS_HIGH;
        }
        if ($score >= 50) {
            return self::STATUS_MEDIUM;
        }

        return self::STATUS_NORMAL;
    }

    /**
     * SQL CASE expression: map status text → severity int (for MAX aggregation).
     * Accepts both Title Case and lowercase historical rows.
     */
    public static function sqlSeverityCase(string $column = 'status'): string
    {
        return "CASE LOWER({$column})
            WHEN 'high' THEN 5
            WHEN 'medium' THEN 4
            WHEN 'down' THEN 3
            WHEN 'error' THEN 2
            WHEN 'normal' THEN 1
            WHEN 'skipped' THEN 0
            ELSE 0
        END";
    }

    /**
     * SQL CASE expression: map severity int → canonical Title Case status.
     */
    public static function sqlStatusFromSeverity(string $severityExpr): string
    {
        return "CASE MAX({$severityExpr})
            WHEN 5 THEN 'High'
            WHEN 4 THEN 'Medium'
            WHEN 3 THEN 'Down'
            WHEN 2 THEN 'Error'
            WHEN 1 THEN 'Normal'
            ELSE 'Skipped'
        END";
    }

    public function getDiffData($settingId): array
    {
        $row = WebdefacmentDataCheck::where('webdefacment_setting_id', $settingId)
            ->latest()
            ->first();

        if (!$row) {
            return ['success' => false, 'message' => 'Diff not found'];
        }

        return [
            'success'       => true,
            'merkle_old'    => (string) ($row->merkle_old ?? ''),
            'merkle_new'    => (string) ($row->merkle_new ?? ''),
            'simhash_bits'  => (string) ($row->simhash_bits ?? ''),
            'section_diffs' => $row->section_diffs ?? [],
            'assets_add'    => $row->assets_add ?? [],
            'assets_del'    => $row->assets_del ?? [],
            'outbound_new'  => $row->outbound_new_not_whitelisted ?? [],
        ];
    }
}
