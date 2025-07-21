<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentScanLog extends Model
{
    protected $table = 'agent_scan_log';

    protected $dates = ['first_scan', 'last_scan'];
}


