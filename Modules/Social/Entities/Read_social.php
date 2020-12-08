<?php

namespace Modules\Social\Entities;

// use Modules\Social\Entities\SiteNewsRelated;
use Illuminate\Database\Eloquent\Model;

class Read_social extends Model
{
    protected $table = 'read_social';
    protected $fillable = [];

    // public function get_cate(){
    //     return $this->hasMany(RSSNewsCategory::class, 'rss_news_id', 'id')->with("get_cate_name");
    // }

    // public function get_topic(){
    //     return $this->belongsTo(NewsTopics::class, 'id', 'rss_news_id');
    // }

    // public function get_topic_multi(){
    //     return $this->hasMany(NewsTopics::class, 'rss_news_id', 'id');
    // }

    // public function get_site_news_related(){
    //     return $this->hasMany(SiteNewsRelated::class, 'news_id', 'id');
    // }
}
