<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentReleasePackage extends Model
{
    protected $table = 'agent_release_packages';

    protected $fillable = [
        'version', 'kind', 'os', 'file_name', 'path', 'sha256', 'size_bytes', 'status', 'notes',
    ];

    public static function allowedOs()
    {
        return ['windows', 'debian', 'ubuntu', 'centos', 'fedora'];
    }
}
