<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Assets_datas;

class Transaction_client_asset_data extends Model
{
    protected $table = 'transaction_client_asset_data';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Assets_datas::class, 'id', 'transaction_id');
    }
}
