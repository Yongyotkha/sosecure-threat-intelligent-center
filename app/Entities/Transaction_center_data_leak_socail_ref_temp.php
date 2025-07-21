<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\leak_socail_ref_temp;

class Transaction_center_data_leak_socail_ref_temp extends Model
{
    protected $table = 'transaction_center_data_leak_socail_ref_temp';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer()
    {
        return $this->hasOne(leak_socail_ref_temp::class, 'id', 'transaction_id');
    }
}
