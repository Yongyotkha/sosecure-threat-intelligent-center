<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_cve_assets;

class Transaction_client_cve_assets extends Model
{
    protected $table = 'transaction_client_cve_assets';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(TF_Center_cve_assets::class, 'id', 'transaction_id');
    }
}
