<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakFeed extends Model
{
    protected $table = 'data_leak_feed';

    public function get_social_ref(){
        return $this->hasMany(DataLeakSocialRef::class, 'data_leak_feed_id', 'id')->where('deleted_at',null)->where('status',1)->with('get_site');
    }
}


