<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Credentials;

class Transaction_client_credentials extends Model
{
    protected $table = 'transaction_client_credentials';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(Credentials::class, 'id', 'transaction_id');
    }
}
