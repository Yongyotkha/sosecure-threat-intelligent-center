<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_data_leak_feed;
use App\Entities\TF_Center_data_leak_socail_ref;

class Transaction_center_data_leak_feed extends Model
{
    protected $table = 'transaction_center_data_leak_feed';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer()
    {
        return $this->hasOne(TF_Center_data_leak_feed::class, 'id', 'transaction_id');
    }

    public function get_transfer_ref()
    {
        return $this->hasOne(TF_Center_data_leak_socail_ref::class, 'id', 'transaction_id_ref');
    }
}
