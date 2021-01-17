<?php

namespace App;

use Modules\SiteSettings\Entities\SiteSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataLeakSocialRefTemp extends Model
{
    use SoftDeletes;
    protected $table = 'data_leak_socail_ref_temp';
    protected $dates = ['deleted_at'];

    // public function get_data_leak_feed(){
    //     return $this->belongsTo(DataLeakFeed::class, 'data_leak_feed_id', 'id');
    // }

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

    public function get_data_leak_feed_temp_one(){
        return $this->hasOne(DataLeakFeedTemp::class, 'id', 'data_leak_feed_id')->where('deleted_at',null)->where('status',1);
    }
}
