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
}
