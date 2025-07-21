<?php

namespace App;

use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Database\Eloquent\Model;

class DataLeakSocial extends Model
{
    protected $table = 'data_leak_social';
    protected $guarded = ['id'];

    function get_site()
    {
        return $this->belongsTo(SiteSettings::class, 'site_id', 'id');
    }
}
