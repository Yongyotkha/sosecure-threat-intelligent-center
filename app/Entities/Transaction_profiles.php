<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Profiles;

class Transaction_profiles extends Model
{
    protected $table = 'transaction_client_profiles';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Profiles::class, 'id', 'transaction_id');
    }
}
