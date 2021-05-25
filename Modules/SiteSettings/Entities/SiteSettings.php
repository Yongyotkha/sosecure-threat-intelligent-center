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
use App\transcation_jobs_clients;

class SiteSettings extends Model{
    use SoftDeletes;
    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id','code','name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'ip_public', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 'installed',
        'laravel_version', 'code_version', 'os', 'server_time', 'php_version', 'your_app_name', 'time_zone', 'key_system', 'register_step', 'user_allow', 'user_limit_amount', 'role_allow_admin', 'domain_allow', 'domain_limit', 'asset_allow', 'asset_limit','search_api_loookup_limit','search_api_loookup_Use','allow_api_api_loookup'
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
        $data = $this->select('id', 'code', 'name', 'descript', 'logo', 'address', 'remark', 'active', 'ip_key', 'ip_public', 'mac_address_key', 'system_key', 'public_key', 'system_web_online', 'system_site_online', 'no_expiration_active', 'created_at', 'updated_at', 'start_active', 'end_active', 'start_active_key', 'end_active_key', 'installed', 'laravel_version', 'code_version', 'os', 'server_time', 'php_version', 'your_app_name', 'time_zone', 'key_system', 'register_step', 'user_allow', 'user_limit_amount', 'role_allow_admin', 'domain_allow', 'domain_limit', 'asset_allow', 'asset_limit', 'web_defacement_allow', 'web_defacement_limit', 'server_log_ip', 'server_log_protocol', 'server_log_port', 'last_client_update', 'mysql_user', 'mysql_password', 'mongo_user', 'mongo_password','search_api_loookup_limit','search_api_loookup_Use','allow_api_api_loookup')
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

    public function get_cache_clear(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'cache_clear')->orderBy('updated_at', 'desc');
    }

    public function get_config_cache(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'config_cache')->orderBy('updated_at', 'desc');
    }

    public function get_config_clear(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'config_clear')->orderBy('updated_at', 'desc');
    }

    public function get_set_permission(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'set_permission')->orderBy('updated_at', 'desc');
    }

    public function get_update_code(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'update_code')->orderBy('updated_at', 'desc');
    }

    public function get_status_nginx(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'get_status_nginx')->orderBy('updated_at', 'desc');
    }

    public function restart_nginx(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'restart_nginx')->orderBy('updated_at', 'desc');
    }

    public function get_status_mongo(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'get_status_mongo')->orderBy('updated_at', 'desc');
    }

    public function restart_mongo(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'restart_mongo')->orderBy('updated_at', 'desc');
    }

    public function get_status_mysql(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'get_status_mysql')->orderBy('updated_at', 'desc');
    }

    public function restart_mysql(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'restart_mysql')->orderBy('updated_at', 'desc');
    }

    public function disabled_debug(){
        return $this->belongsTo(transcation_jobs_clients::class, 'id', 'site_id')->select('updated_at')->where('mode', 'disabled_debug')->orderBy('updated_at', 'desc');
    }

    
}
