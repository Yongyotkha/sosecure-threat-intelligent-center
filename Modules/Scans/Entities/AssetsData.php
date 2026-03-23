<?php

namespace Modules\Scans\Entities;

use Illuminate\Database\Eloquent\Model;
use App\DataTypes;

class AssetsData extends Model
{
    protected $fillable = [];

    public function get_data_type(){
        return $this->belongsTo(DataTypes::class, 'data_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(AssetsData::class, 'refer_asset_id');
    }

    public function children()
    {
        return $this->hasMany(AssetsData::class, 'refer_asset_id');
    }
}
