<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\TFClient_R_s_s_news_categories;

class Transaction_client_News_Categories extends Model
{
    protected $table = 'transaction_client_news_categories';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer_client()
    {
        return $this->hasOne(TFClient_R_s_s_news_categories::class, 'id', 'transaction_id');
    }
}
