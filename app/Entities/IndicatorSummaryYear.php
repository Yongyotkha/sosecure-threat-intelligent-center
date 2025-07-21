<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class IndicatorSummaryYear extends Model
{
    protected $table = 'indicator_summary_year';
    protected $primaryKey = 'id';
    protected $attributes = [
        'status' => 1,
    ];

    protected $dates   = ['created_at', 'updated_at'];
}
