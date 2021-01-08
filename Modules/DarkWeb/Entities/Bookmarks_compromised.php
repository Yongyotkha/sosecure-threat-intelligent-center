<?php

namespace Modules\Darkweb\Entities;

// use Modules\Social\Entities\SiteNewsRelated;
use Illuminate\Database\Eloquent\Model;
use Modules\Social\Entities\Data_leak_feed;
class Bookmarks_compromised extends Model
{
    protected $table = 'bookmarks_compromised';
    protected $fillable = [];

    public function data_leak_feed(){
        return $this->belongsTo(Data_leak_feed::class, 'data_leak_feed_id', 'id');
    }

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
