<?php

namespace Modules\Scans\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Scans\Entities\Assets;
use Modules\SiteSettings\Entities\SiteSettings;

class CPE extends Model
{
    protected $fillable = [];
    protected $table = 'cpe';

    public function get_assets(){
        return $this->hasOne(Assets::class, 'id', 'asset_id');
    }
}
