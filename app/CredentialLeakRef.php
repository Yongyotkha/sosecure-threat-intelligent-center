<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CredentialLeakRef extends Model
{
    use SoftDeletes;

    protected $table = 'credential_leak_ref';

    protected $fillable = [
        'code',
        'data_leak_feed_id',
        'site_id',
        'keyword',
        'feel_type',
        'content',
        'status',
        'serverity',
        'status_monitoring',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function get_data_leak_feed_one()
    {
        return $this->belongsTo(DataLeakFeed::class, 'data_leak_feed_id', 'id');
    }

    public function get_site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
 