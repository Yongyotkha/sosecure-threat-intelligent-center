<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Roles;

class Transaction_roles extends Model
{
    protected $table = 'transaction_client_roles';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Roles::class, 'id', 'transaction_id');
    }
}
