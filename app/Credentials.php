<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Entities\CompromisedServer;

class Credentials extends Model
{
    protected $table = 'credentials';

    public function get_compromised_server(){
        return $this->hasMany(CompromisedServer::class, 'credentials_id', 'id');
    }
}




