<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;

class WebdefacmentSetting extends Model
{
    protected $table = 'webdefacment_setting';
    protected $fillable = [];

    public function get_site()
    {
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
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