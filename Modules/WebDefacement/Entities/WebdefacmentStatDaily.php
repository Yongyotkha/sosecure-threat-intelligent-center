<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;

class WebdefacmentStatDaily extends Model
{
    protected $table = 'webdefacement_stat_daily';
    protected $fillable = [];

    public $timestamps = true;

    /**
     * Get stats by webdefacement ID and date range.
     * Columns: webdefacement_id, date (see recreate migration 2025_11_10_115348).
     */
    public static function getStatsByDateRange($webdefacmentSettingId, $startDate, $endDate)
    {
        return self::where('webdefacement_id', $webdefacmentSettingId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }
}
