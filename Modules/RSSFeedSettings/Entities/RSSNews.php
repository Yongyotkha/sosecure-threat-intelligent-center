<?php

namespace Modules\RSSFeedSettings\Entities;

use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Illuminate\Database\Eloquent\Model;

class RSSNews extends Model
{
    protected $fillable = [];

    public function get_cate(){
        return $this->hasMany(RSSNewsCategory::class, 'rss_news_id', 'id')->with("get_cate_name");
    }

    public function get_topic(){
        return $this->belongsTo(NewsTopics::class, 'id', 'rss_news_id');
    }
}
