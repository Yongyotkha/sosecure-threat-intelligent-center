<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class TransactionBatchjob extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $table = 'transaction_batchjob';
}
