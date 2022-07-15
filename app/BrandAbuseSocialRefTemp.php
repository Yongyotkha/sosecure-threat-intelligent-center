<?php

namespace App;

use Modules\SiteSettings\Entities\SiteSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandAbuseSocialRefTemp extends Model
{
    use SoftDeletes;
    protected $table = 'brand_abuse_socail_ref_temp';
    protected $dates = ['deleted_at'];

    // public function get_brand_abuse_feed(){
    //     return $this->belongsTo(BrandAbuseFeed::class, 'brand_abuse_feed_id', 'id');
    // }

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id');
    }

    public function get_brand_abuse_feed_temp_one(){
        return $this->hasOne(BrandAbuseFeedTemp::class, 'id', 'brand_abuse_feed_id')->where('deleted_at',null)->where('status',1);
    }
}
