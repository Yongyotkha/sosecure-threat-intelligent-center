<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\RSSFeedSettings\Entities\RSSNews;
class Bookmark extends Model
{
    public function news(){
        return $this->belongsTo(RSSNews::class, 'news_id', 'id');
    }
}
