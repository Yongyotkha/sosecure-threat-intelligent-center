<?php

namespace Modules\Scans\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Scans\Entities\AssetsData;

class Assets extends Model
{
    protected $fillable = [];

    public function get_assets_data(){
        return $this->hasMany(AssetsData::class, 'asset_id', 'id');
    }
}
