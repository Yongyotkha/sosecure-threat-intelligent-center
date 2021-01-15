<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\DataLeakSocialRef;


class Transaction_center_data_leak_feed_temp extends Model
{
    protected $table = 'transaction_center_data_leak_feed_temp';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer()
    {
        return $this->hasOne(DataLeakSocialRef::class, 'id', 'transaction_id');
    }
}
