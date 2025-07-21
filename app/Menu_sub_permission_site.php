<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Menu_sub_permission_site extends Model
{
    use SoftDeletes;
    protected $table = 'site_menu_sub_permission';

    // public function get_menu_sub(){
    //         return $this->hasMany(Menu_sub::class, 'menu_id', 'id');
    // }
}
