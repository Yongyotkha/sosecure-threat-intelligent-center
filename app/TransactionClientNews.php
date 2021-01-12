<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use App\R_s_s_news;

class TransactionClientNews extends Model
{
    protected $table = 'transaction_client_news';

    public function get_Transaction_client_news()
    {
        return $this->hasOne(R_s_s_news::class, 'id', 'news_id');
    }
}
