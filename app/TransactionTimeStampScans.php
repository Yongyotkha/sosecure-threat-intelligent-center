<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteSettings;

class TransactionTimeStampScans extends Model
{
    public function get_domain(){
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function get_site(){
        return $this->belongsTo(SiteSettings::class, 'site_id');
    }
}
