<?php

namespace Modules\SiteSettings\Entities;

// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
// use Modules\SiteSettings\Entities\SiteCategory;

class LogsSent extends Model {

    protected $table = "logs_sent";
    public $timestamps = true;
    protected $fillable = [
        'id','mode','start', 'end', 'status_progrss'
    ];

    protected $dates   = ['created_at', 'updated_at'];


    // public function get_data($site_id,$type){
    //     return $this->select('*')->where('site_id', $site_id)->where('type', $type)->first();
    // }
}
