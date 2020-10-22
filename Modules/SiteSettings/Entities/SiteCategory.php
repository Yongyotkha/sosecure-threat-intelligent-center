<?php

namespace Modules\SiteSettings\Entities;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Model;
use Modules\CategorySettings\Entities\CategorySettings;

class SiteCategory extends Model {
    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id', 'code', 'site_id', 'category_id',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function category(){
        return $this->belongsTo(CategorySettings::class, 'category_id');
    }
}
