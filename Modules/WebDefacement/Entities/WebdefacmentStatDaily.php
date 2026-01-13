<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;

class WebdefacmentStatDaily extends Model
{
    protected $table = 'webdefacement_stat_daily';
    protected $fillable = [];
    
    public $timestamps = true;
    
    /**
     * Get stats by webdefacement ID and date range
     */
    public static function getStatsByDateRange($webdefacmentSettingId, $startDate, $endDate)
    {
        return self::where('webdefacment_setting_id', $webdefacmentSettingId)
            ->whereBetween('stat_date', [$startDate, $endDate])
            ->orderBy('stat_date', 'desc')
            ->get();
    }
}
