<?php

namespace Modules\SiteSettings\Entities;

// use App\TransactionTimeStampScans;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
// use Modules\SiteSettings\Entities\SiteCategory;

class site_keywords_main extends Model {
    // use SoftDeletes;
    protected $table = "site_keywords_main";
    public $timestamps = true;
    protected $fillable = [];
    protected $dates   = ['created_at', 'updated_at'];

    // public function preferredLocale()
    // {
    //     return $this->locale;
    // }

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

    // public function get_transaction_time_stamp_scans(){
    //     return $this->hasOne(TransactionTimeStampScans::class, 'domain_id', 'id');
    // }
}
