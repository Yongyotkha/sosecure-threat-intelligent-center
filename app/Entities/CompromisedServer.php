<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class CompromisedServer extends Model
{
    protected $table = 'compromised_server';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
}
