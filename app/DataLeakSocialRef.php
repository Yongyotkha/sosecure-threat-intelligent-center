<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakSocialRef extends Model
{
    protected $table = 'data_leak_socail_ref';
    public function get_data_leak_feed(){
        return $this->belongsTo(DataLeakFeed::class, 'data_leak_feed_id', 'id');
    }
}
