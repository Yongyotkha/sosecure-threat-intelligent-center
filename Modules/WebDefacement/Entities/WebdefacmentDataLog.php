<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;


class WebdefacmentDataLog extends Model
{
    protected $table = 'webdefacment_data_logs';
    protected $fillable = [];

    // public function get_site()
    // {
    //     return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    // }



}