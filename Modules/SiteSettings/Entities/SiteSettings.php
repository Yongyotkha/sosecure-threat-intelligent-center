<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteCategory;

class SiteSettings extends Model{
    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id','code','name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at', 'start_active', 'end_active', 'start_active_key', 'end_active_key'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function find_id($uuid){
        return $this->select('id')->where('code', $uuid)->first();
    }

    public function get_data($uuid, $active = null){
        $data = $this->select('id', 'code', 'name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 'created_at', 'updated_at', 'start_active', 'end_active', 'start_active_key', 'end_active_key')
        ->where('code', $uuid)
        ->where('deleted_at', '=', null);
        if($active !== null){
            $data->where('active', $active);
        }
        return $data->first();
    }

    public function get_categorys(){
        return $this->hasMany(SiteCategory::class, 'site_id', 'id');
    }
}
