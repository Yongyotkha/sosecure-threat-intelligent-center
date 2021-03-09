<?php

namespace Modules\Monitoring\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class MonitoringSystem extends Model
{
    protected $table = 'monitoring_system';
    protected $fillable = [];

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

}