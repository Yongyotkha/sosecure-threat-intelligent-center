<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataLeakFeed extends Model
{
    protected $table = 'data_leak_feed';

    public function get_cve_asset()
    {
        return $this->hasOne(CVEAssets::class, 'id', 'cveven_id');
    }
}


