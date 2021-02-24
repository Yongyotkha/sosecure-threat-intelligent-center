<?php

namespace Modules\SiteSettings\Entities;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Model;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\SiteSettings\Entities\site_config_email_alert;

class SiteCategory extends Model {
    protected $table = "site_category";
    public $timestamps = true;
    protected $fillable = [
        'id', 'code', 'site_id', 'category_id', 'active',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function category(){
        return $this->belongsTo(CategorySettings::class, 'category_id');
    }

    public function site_email_alert(){
        return $this->hasMany(site_config_email_alert::class, 'site_id', 'site_id');
    }
}
