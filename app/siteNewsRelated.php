<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteSettings;

class siteNewsRelated extends Model
{
    protected $table = 'site_news_related';

    public function get_site(){
        return $this->belongsTo(SiteSettings::class, 'site_id', 'id');
    }
}
