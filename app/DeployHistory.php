<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\DeployCode;

class DeployHistory extends Model
{
    protected $table = 'deploy_history';
    
    public function get_deploy_code(){
        return $this->hasMany(DeployCode::class, 'code', 'code');
    }
}
