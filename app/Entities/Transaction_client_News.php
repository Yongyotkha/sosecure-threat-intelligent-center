<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\TF_Center_R_s_s_news;

class Transaction_client_News extends Model
{
    protected $table = 'transaction_client_news';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(TF_Center_R_s_s_news::class, 'id', 'transaction_id');
    }
}
