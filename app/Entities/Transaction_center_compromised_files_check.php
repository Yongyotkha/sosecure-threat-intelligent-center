<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Entities\CompromisedFileCheck;

class Transaction_center_compromised_files_check extends Model
{
    protected $table = 'transaction_center_compromised_files_check';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_transfer()
    {
        return $this->hasOne(CompromisedFileCheck::class, 'id', 'transaction_id');
    }
}
