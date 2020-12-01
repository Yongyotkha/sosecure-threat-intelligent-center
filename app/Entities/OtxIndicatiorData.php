<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OtxIndicatiorData extends Model
{
    protected $table = 'transaction_otx_indicatior_data';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $attributes = [
        'status' => 1,
    ];
    protected $fillable = ['id' ,'indicatior','type','tile','desciption','content','status','created_by','updated_by','transaction_date','transcation_id'];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];
}
