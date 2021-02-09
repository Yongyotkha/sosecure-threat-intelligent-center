<?php

namespace Modules\Monitoring\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class MonitorLogs extends Model
{
    protected $table = 'logs';
    protected $fillable = [];

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

}