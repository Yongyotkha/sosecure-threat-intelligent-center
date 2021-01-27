<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Compromised_server;

class Transaction_client_compromised_server extends Model
{
    protected $table = 'transaction_client_compromised_server';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(Compromised_server::class, 'id', 'transaction_id');
    }
}
