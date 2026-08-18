<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentReleaseTarget extends Model
{
    protected $table = 'agent_release_targets';

    protected $fillable = [
        'site_id', 'package_id', 'target_version', 'status',
    ];
}
