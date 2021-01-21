<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Password_resets;

class Transaction_password_resets extends Model
{
    protected $table = 'transaction_password_resets';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Password_resets::class, 'id', 'transaction_id');
    }
}
