<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SsdeepLog extends Model
{
    protected $table = 'ssdeep_log';

    protected $dates = ['detected_at'];
}
