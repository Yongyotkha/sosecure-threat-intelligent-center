<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Users;

class Transaction_users extends Model
{
    protected $table = 'transaction_client_users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(Users::class, 'id', 'transaction_id');
    }
}
