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
}
