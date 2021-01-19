<?php

namespace Modules\Scans\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Scans\Entities\AssetsData;
use Modules\SiteSettings\Entities\SiteSettings;

class Assets extends Model
{
    protected $fillable = [];

    public function get_assets_data(){
        return $this->hasMany(AssetsData::class, 'asset_id', 'id');
    }

    public function find_id($uuid){
        return $this->select('id')->where('code', $uuid)->first();
    }

    public function get_site(){
        return $this->belongsTo(SiteSettings::class, 'site_id', 'id');
    }
}
