<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\Tags_site;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\Site_keywords;
use Modules\SiteSettings\Entities\Domain;
use Modules\Users\Entities\UserSite;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteSettings extends Model{
    use SoftDeletes;
    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id','code','name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'ip_public', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 'installed',
        'laravel_version', 'code_version', 'os', 'server_time', 'php_version', 'your_app_name', 'time_zone', 'key_system', 'register_step', 'user_allow', 'user_limit_amount', 'role_allow_admin', 'domain_allow', 'domain_limit', 'asset_allow', 'asset_limit'
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at', 'start_active', 'end_active', 'start_active_key', 'end_active_key'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function find_id($uuid){
        return $this->select('id')->where('code', $uuid)->first();
    }

    public function find_code($id){
        return $this->select('code')->where('id', $id)->first();
    }

    public function get_data($uuid, $active = null){
        $data = $this->select('id', 'code', 'name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'ip_public', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 'created_at', 'updated_at', 'start_active', 'end_active', 'start_active_key', 'end_active_key', 'installed', 'laravel_version', 'code_version', 'os', 'server_time', 'php_version', 'your_app_name', 'time_zone', 'key_system', 'register_step', 'user_allow', 'user_limit_amount', 'role_allow_admin', 'domain_allow', 'domain_limit', 'asset_allow', 'asset_limit', 'web_defacement_allow', 'web_defacement_limit', 'server_log_ip', 'server_log_protocol', 'server_log_port', 'last_client_update', 'mysql_user', 'mysql_password', 'mongo_user', 'mongo_password')
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

    public function get_keywords_darkweb(){
        return $this->hasMany(Site_keywords::class, 'site_id', 'id')->where('type','darkweb');
    }

    public function get_domains_default(){
        return $this->hasMany(Domain::class, 'site_id', 'id')->where('domain_default',1);
    }

    public function get_tags(){
        return $this->hasMany(Tags_site::class, 'site_id', 'id');
    }

    public function get_site_config_email_alert(){
        return $this->hasMany(site_config_email_alert::class, 'site_id', 'id');
    }

    public function get_user_site($id){
        $user_site = UserSite::where('user_id',$id)->get();
        return $user_site;
    }
}
