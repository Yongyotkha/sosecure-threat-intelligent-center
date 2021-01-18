<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_data_leak_feed_temp;
use App\Entities\TF_Center_data_leak_socail_ref_temp;


class Transaction_center_data_leak_feed_temp extends Model
{
    protected $table = 'transaction_center_data_leak_feed_temp';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer()
    {
        //One
        return $this->hasOne(TF_Center_data_leak_feed_temp::class, 'id', 'transaction_id');
    }

    public function get_transfer_ref()
    {
        //Many
        return $this->hasOne(TF_Center_data_leak_socail_ref_temp::class, 'id', 'transaction_id_ref');
    }
}
