<?php

namespace Modules\RSSFeedSettings\Entities;

use Illuminate\Database\Eloquent\Model;

class TransactionRssData extends Model
{
    protected $table = 'transaction_rss_data';
    protected $fillable = [];

    public function get_rss(){
        return $this->belongsTo(RSSData::class, 'rss_id', 'id');
    }
    public function get_rss_news(){
        // return $this->hasOne(RSSNews::class, 'id', 'transaction_rss_id')->where('transaction_rss_id','!=',null);
        return $this->belongsTo(RSSNews::class, 'id', 'transaction_rss_id')->where('transaction_rss_id','!=',null)->with('get_cate');
    }

    public function get_rss_source(){
        return $this->hasOne(RSSData::class, 'id', 'rss_id');
    }
}
