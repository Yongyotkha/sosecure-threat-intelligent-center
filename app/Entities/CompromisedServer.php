<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Database\Eloquent\SoftDeletes;


class CompromisedServer extends Model
{
    use SoftDeletes;
    protected $table = 'compromised_server';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $dates   = ['created_at', 'updated_at'];

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id')->where('deleted_at',null)->where('active',1);
    }
}

