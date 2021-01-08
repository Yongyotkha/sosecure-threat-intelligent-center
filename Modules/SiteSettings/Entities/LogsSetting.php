<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
// use Modules\SiteSettings\Entities\SiteCategory;

class LogsSetting extends Model {

    protected $table = "logs_setting";
    public $timestamps = true;
    protected $fillable = [
        'type','site_id','ip', 'link', 'protocal', 'port', 'protocal_format'
    ];

    protected $dates   = ['created_at', 'updated_at'];


    public function get_data($site_id,$type){
        return $this->select('*')->where('site_id', $site_id)->where('type', $type)->first();
    }
}
