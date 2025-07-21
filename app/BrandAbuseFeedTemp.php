<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BrandAbuseFeedTemp extends Model
{
    protected $table = 'brand_abuse_feed_temp';

    public function get_social(){
        return $this->belongsTo(BrandAbuseSocial::class, 'sourceid', 'id');
    }

    public function get_socail_ref_temp(){
        return $this->hasMany(leak_socail_ref_temp ::class, 'brand_abuse_feed_id', 'id')->with('get_site');
    }


}
