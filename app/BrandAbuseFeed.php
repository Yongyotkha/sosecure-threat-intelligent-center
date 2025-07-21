<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandAbuseFeed extends Model
{
    protected $table = 'brand_abuse_feed';

    public function get_social_ref(){
        return $this->hasMany(BrandAbuseSocialRef::class, 'brand_abuse_feed_id', 'id')->where('deleted_at',null)->where('status',1)->with('get_site');
    }
}


