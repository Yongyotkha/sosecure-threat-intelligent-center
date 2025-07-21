<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Model_has_roles;

class Transaction_model_has_roles extends Model
{
    protected $table = 'transaction_client_model_has_roles';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(Model_has_roles::class, 'id', 'transaction_id');
    }
}
