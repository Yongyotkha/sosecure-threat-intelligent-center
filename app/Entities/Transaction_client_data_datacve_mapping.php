<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_data_datacve_mapping;

class Transaction_client_data_datacve_mapping extends Model
{
    protected $table = 'transaction_client_data_datacve_mapping';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(TF_Center_data_datacve_mapping::class, 'id', 'transaction_id');
    }
}
