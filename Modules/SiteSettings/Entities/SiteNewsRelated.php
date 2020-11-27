<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteCategory;

class SiteNewsRelated extends Model{
    protected $table = "site_news_related";
    public $timestamps = true;
    protected $fillable = [
        'id','site_id','news_id', 'deleted'
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function get_categorys(){
        return $this->hasMany(SiteCategory::class, 'site_id', 'id');
    }

    
}
