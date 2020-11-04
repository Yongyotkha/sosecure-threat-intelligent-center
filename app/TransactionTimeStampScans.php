<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionTimeStampScans extends Model
{
    public function get_domain(){
        return $this->hasMany(DeployCode::class, 'code', 'code');
    }
}
