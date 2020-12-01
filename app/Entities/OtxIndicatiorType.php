<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OtxIndicatiorType extends Model
{
    protected $table = 'otx_type';
    protected $primaryKey = 'id';
    protected $attributes = [
        'status' => 1,
        'element_count' => 0,
    ];
    protected $fillable = ['code' ,'slug','name','description','remark','element_count','status','created_by','updated_by'];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];
    
}
