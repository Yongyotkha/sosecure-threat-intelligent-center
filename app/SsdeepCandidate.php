<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SsdeepCandidate extends Model
{
    protected $table = 'ssdeep_candidate';

    protected $dates = ['detected_at'];

    protected $fillable = [
        'site_id',
        'agent_id',
        'ip_private',
        'path',
        'file_name',
        'hash_md5',
        'hash_sha256',
        'ssdeep',
        'engine',
        'rule',
        'score',
        'scan_mode',
        'detected_at',
        'source',
        'status',
        'note',
    ];
}
