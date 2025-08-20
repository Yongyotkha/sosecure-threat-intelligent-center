<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataLog;
use Illuminate\Database\Eloquent\SoftDeletes;


class WebdefacmentSetting extends Model
{
    use SoftDeletes;
    protected $table = 'webdefacment_setting';
    protected $fillable = [];

    public function get_site()
    {
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

    public function get_webdefacment_data_original()
    {
        return $this->hasMany(WebdefacmentDataOriginal::class, 'webdefacment_setting_id', 'id');
    }

    public function get_webdefacment_data_original_detail()
    {
        return $this->hasMany(WebdefacmentDataOriginal::class, 'webdefacment_setting_id', 'id')->orderBy('last_update','desc');
    }

    public function get_webdefacment_data_check()
    {
        return $this->hasMany(WebdefacmentDataCheck::class, 'webdefacment_setting_id', 'id');
    }

    public function get_webdefacment_data_check_detail()
    {
        return $this->hasMany(WebdefacmentDataCheck::class, 'webdefacment_setting_id', 'id')->orderBy('last_update','desc');
    }

    public function get_webdefacment_data_log()
    {
        return $this->hasMany(WebdefacmentDataLog::class, 'webdefacment_setting_id', 'id');
    }

    public function get_webdefacment_data_log_detail()
    {
        return $this->hasMany(WebdefacmentDataLog::class, 'webdefacment_setting_id', 'id')->orderBy('updated_date','desc');
    }

    

    public function webdefacment_data_original_last($webdefacment_setting_id)
    {
        $val = '';
        // return $this->hasOne(SiteSettings::class, 'id', 'site_id');
        $w = WebdefacmentDataOriginal::where('webdefacment_setting_id',$webdefacment_setting_id)->orderBy('last_update','desc')->first();
        if($w) {
            $val = $w;
        }
        return $val;
    }

}