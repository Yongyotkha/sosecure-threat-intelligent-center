<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Sites;

class Transaction_site extends Model
{
    protected $table = 'transaction_client_site';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Sites::class, 'id', 'transaction_id');
    }
}
