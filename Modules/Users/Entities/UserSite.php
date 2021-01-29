<?php

namespace Modules\Users\Entities;
use Modules\SiteSettings\Entities\SiteSettings;

use Illuminate\Database\Eloquent\Model;

class UserSite extends Model
{
    protected $table = 'user_site';
    protected $fillable = [];

    public function get_site(){
        return $this->hasOne(SiteSettings::class, 'id', 'site_id')->select(['code','id','name']);
    }
}
