<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\User_site;

class Transaction_user_site extends Model
{
    protected $table = 'transaction_client_users_site';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(User_site::class, 'id', 'transaction_id');
    }
}
