<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\CPE;

class Transaction_client_cpe extends Model
{
    protected $table = 'transaction_client_cpe';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(CPE::class, 'id', 'transaction_id');
    }
}
