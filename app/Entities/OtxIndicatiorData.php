<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OtxIndicatiorData extends Model
{
    protected $table = 'transaction_otx_indicatior_data';
    protected $primaryKey = 'id';
    protected $attributes = [
        'status' => 1,
    ];
    //protected $fillable = ['name'];
}
