<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use App\ReadTopic;
use Illuminate\Support\Facades\Auth;

class Topic extends Model
{
    protected $table = 'topics';

    public function news_topic(){
        return $this->hasMany(NewsTopics::class, 'topic_id', 'id');
    }

    public function read_news(){
        return $this->hasMany(ReadTopic::class, 'topic_id', 'id')->where('user_id', Auth::user()->id);
    }
}
