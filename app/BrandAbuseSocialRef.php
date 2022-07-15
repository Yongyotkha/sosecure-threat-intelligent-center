<?php

namespace App;

use Modules\SiteSettings\Entities\SiteSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandAbuseSocialRef extends Model
{
    use SoftDeletes;
    protected $table = 'brand_abuse_socail_ref';
    protected $dates = ['deleted_at'];

    public function get_brand_abuse_feed(){
        return $this->belongsTo(BrandAbuseFeed::class, 'brand_abuse_feed_id', 'id');
    }

    public function get_site(){
        return $this->belongsTo(SiteSettings::class, 'site_id', 'id');
    }

    public function get_brand_abuse_feed_one(){
        return $this->hasOne(BrandAbuseFeed::class, 'id', 'brand_abuse_feed_id')->where('deleted_at',null)->where('status',1);
    }
}
