<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;


class TF_Center_transaction_batchjob extends Model
{
    protected $table = 'transaction_batchjob';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
}
