<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakFeedTemp extends Model
{
    protected $table = 'data_leak_feed_temp';

    public function get_social(){
        return $this->belongsTo(DataLeakSocial::class, 'sourceid', 'id');
    }
}
