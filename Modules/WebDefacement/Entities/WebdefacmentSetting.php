<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class WebdefacmentSetting extends Model
{
    protected $table = 'webdefacment_setting';
    protected $fillable = [];

    public function get_site()
    {
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

}