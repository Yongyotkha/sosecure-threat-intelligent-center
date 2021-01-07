<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class WebdefacmentDataOriginal extends Model
{
    protected $table = 'webdefacment_data_original';
    protected $fillable = [];

    // public function get_site()
    // {
    //     return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    // }



}