<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Permissions;

class Transaction_permissions extends Model
{
    protected $table = 'transaction_permissions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Permissions::class, 'id', 'transaction_id');
    }
}
