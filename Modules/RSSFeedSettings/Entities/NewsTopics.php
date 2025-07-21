<?php

namespace Modules\RSSFeedSettings\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Topic;

class NewsTopics extends Model
{
    protected $fillable = [];
    public function topic(){
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    public function news(){
        return $this->belongsTo(RSSNews::class, 'rss_news_id', 'id');
    }
}
