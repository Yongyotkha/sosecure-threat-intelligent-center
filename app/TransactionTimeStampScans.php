<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\Domain;

class TransactionTimeStampScans extends Model
{
    public function get_domain(){
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
