<?php

namespace Modules\RSSFeedSettings\Entities;

use Modules\CategorySettings\Entities\CategorySettings;
use Illuminate\Database\Eloquent\Model;

class RSSNewsCategory extends Model
{
    protected $fillable = [];

    public function get_cate_name(){
        return $this->belongsTo(CategorySettings::class, 'news_category_id', 'id');
    }

    public function get_cate_name_news(){
        return $this->belongsTo(CategorySettings::class, 'news_category_id', 'id')->where('deleted_at',null)->where('active',1);
    }

    public function news(){
        return $this->belongsTo(RSSNews::class, 'rss_news_id', 'id');
    }
}
