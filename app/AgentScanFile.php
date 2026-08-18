<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentScanFile extends Model
{
    protected $table = 'agent_scan_file';

    protected $fillable = [
        'site_id',
        'agent_id',
        'run_id',
        'path',
        'result',
        'rule',
        'engine',
        'score',
        'scanned_at',
    ];

    protected $dates = ['scanned_at'];
}
