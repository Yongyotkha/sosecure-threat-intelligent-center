<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YaraLog extends Model
{
    protected $table = 'yara_log';

    protected $dates = ['first_scan', 'last_scan'];
}


