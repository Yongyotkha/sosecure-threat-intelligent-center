<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use SoftDeletes;
    protected $table = 'menu';

    // public function get_social(){
    //     return $this->belongsTo(DataLeakSocial::class, 'sourceid', 'id');
    // }
}
