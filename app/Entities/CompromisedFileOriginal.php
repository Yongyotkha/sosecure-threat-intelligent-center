<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class CompromisedFileOriginal extends Model
{
    protected $table = 'compromised_files_original';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    
}
