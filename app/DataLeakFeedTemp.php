<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakFeedTemp extends Model
{
    protected $table = 'data_leak_feed_temp';
    protected $dateFormat = 'Y-m-d H:i:s.u';
}
