<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction_client_leak_social_ref extends Model
{
    protected $table = 'transaction_client_leak_social_ref';

    protected $fillable = [
        'site_id',
        'transaction_id',
    ];
}
