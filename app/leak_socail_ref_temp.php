<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class leak_socail_ref_temp extends Model
{
    protected $table = 'data_leak_socail_ref_temp';

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }
}
