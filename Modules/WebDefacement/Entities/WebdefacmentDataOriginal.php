<?php

namespace Modules\WebDefacement\Entities;
use Modules\WebDefacement\Entities\WebdefacmentSetting;

use Illuminate\Database\Eloquent\Model;


class WebdefacmentDataOriginal extends Model
{
    protected $table = 'webdefacment_data_original';
    protected $fillable = [];

    // public function get_webdefacment_setting()
    // {
    //         return $this->belongsTo(WebdefacmentSetting::class, 'webdefacment_setting_id', 'id');
    // }



}