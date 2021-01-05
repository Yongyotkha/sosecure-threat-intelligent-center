<?php

namespace App;

use Modules\SiteSettings\Entities\SiteSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataLeakSocialRef extends Model
{
    use SoftDeletes;
    protected $table = 'data_leak_socail_ref';
    public function get_data_leak_feed(){
        return $this->belongsTo(DataLeakFeed::class, 'data_leak_feed_id', 'id');
    }

    public function get_site(){
        return $this->belongsTo(SiteSettings::class, 'site_id', 'id');
    }

    public function get_data_leak_feed_one(){
        return $this->hasOne(DataLeakFeed::class, 'id', 'data_leak_feed_id')->where('deleted_at',null)->where('status',1);
    }
}
