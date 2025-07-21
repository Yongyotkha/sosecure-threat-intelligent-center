<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_webdefacment_data_logs;

class Transaction_client_webdefacment_data_logs extends Model
{
    protected $table = 'transaction_client_webdefacment_data_logs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(TF_Center_webdefacment_data_logs::class, 'id', 'transaction_id');
    }
}
