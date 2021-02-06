<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Assets;

class Transaction_client_asset extends Model
{
    protected $table = 'transaction_client_asset';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Assets::class, 'id', 'transaction_id');
    }
}
