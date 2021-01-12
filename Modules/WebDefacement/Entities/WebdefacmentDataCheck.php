<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class WebdefacmentDataCheck extends Model
{
    use SoftDeletes;
    protected $table = 'webdefacment_data_check';
    protected $fillable = [];

    // public function get_site()
    // {
    //     return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    // }



}