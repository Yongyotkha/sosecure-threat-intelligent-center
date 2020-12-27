<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakSocial extends Model
{
    protected $table = 'data_leak_social';


    public function get_social_ref(){
        return $this->hasOne(DataLeakSocialRef::class, 'data_leak_feed_id', 'id')->where('deleted_at',null)->where('status',1)->with('');
    }


}
