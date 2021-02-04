<?php

namespace Modules\Users\Entities;

// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
// use Modules\SiteSettings\Entities\SiteCategory;

class permission_menu_sub extends Model {
    protected $table = "permissions_menu_sub";
    public $timestamps = true;
    protected $fillable = [
        'id','menu_sub_id','permission_id'
    ];
    protected $dates   = ['created_at', 'updated_at'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    // public function find_id($uuid){
    //     return $this->select('id')->where('code', $uuid)->first();
    // }

    // public function get_data($uuid, $active = null){
    //     $data = $this->select('id', 'code', 'name', 'domain', 'open_scan', 'scan_interval', 'status', 'created_at', 'updated_at', 'deleted_at', 'site_id')
    //     ->where('code', $uuid)
    //     ->where('deleted_at', '=', null);
    //     if($active !== null){
    //         $data->where('active', $active);
    //     }
    //     return $data->first();
    // }

    // public function get_menu_sub(){
    //     return $this->hasMany(Menu_sub::class, 'menu_id', 'id');
    // }
}
