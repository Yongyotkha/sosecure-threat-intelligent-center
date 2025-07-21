<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\RSSFeedSettings\Entities\RSSNews;

class SiteNewsRelated extends Model{
    protected $table = "site_news_related";
    public $timestamps = true;
    protected $fillable = [
        'id','site_id','news_id', 'deleted'
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function get_news(){
        // return $this->hasMany(RSSNews::class, 'news_id', 'id');
        return $this->hasMany(RSSNews::class, 'id', 'news_id')->with("get_cate");
        // return $this->belongsTo(RSSNews::class, 'news_id', 'id')->with("get_cate");
    }

    
}
