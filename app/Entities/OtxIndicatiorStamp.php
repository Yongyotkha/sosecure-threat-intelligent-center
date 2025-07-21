<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OtxIndicatiorStamp extends Model
{
    protected $table = 'transaction_otx_indicatior_stamp';
    protected $primaryKey = 'id';
    protected $attributes = [
        'status' => 1,
    ];
    protected $fillable = ['code' ,'transaction_date','created_by','updated_by','status'];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];
}
