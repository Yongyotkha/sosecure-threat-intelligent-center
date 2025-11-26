<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataCveSource extends Model
{
    protected $table = 'data_cve_sources';

    protected $fillable = [
        'namecve',
        'source',
    ];

    public $timestamps = true;   // ใช้ created_at / updated_at
}
