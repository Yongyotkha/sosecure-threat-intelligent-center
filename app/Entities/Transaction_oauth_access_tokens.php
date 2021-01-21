<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;
use App\Entities\Permissions;

class Transaction_oauth_access_tokens extends Model
{
    protected $table = 'transaction_oauth_access_tokens';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
    
    public function get_transfer_client()
    {
        return $this->hasOne(Oauth_access_tokens::class, 'id', 'transaction_id');
    }
}
