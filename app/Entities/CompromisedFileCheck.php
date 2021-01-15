<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
class CompromisedFileCheck extends Model
{
    protected $table = 'compromised_files_check';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];
}
