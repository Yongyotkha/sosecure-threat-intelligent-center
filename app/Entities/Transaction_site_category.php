<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Site_category;

class Transaction_site_category extends Model
{
    protected $table = 'transaction_client_site_category';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Site_category::class, 'id', 'transaction_id');
    }
}
