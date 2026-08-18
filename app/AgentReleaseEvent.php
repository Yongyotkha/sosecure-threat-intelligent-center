<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentReleaseEvent extends Model
{
    protected $table = 'agent_release_events';

    protected $fillable = [
        'site_id', 'agent_id', 'ip_private', 'current_version', 'target_version',
        'status', 'message', 'started_at', 'finished_at',
    ];

    protected $dates = ['started_at', 'finished_at'];
}
