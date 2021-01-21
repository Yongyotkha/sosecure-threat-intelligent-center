<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Role_permissions;

class Transaction_role_permissions extends Model
{
    protected $table = 'transaction_role_permissions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Role_permissions::class, 'id', 'transaction_id');
    }
}
