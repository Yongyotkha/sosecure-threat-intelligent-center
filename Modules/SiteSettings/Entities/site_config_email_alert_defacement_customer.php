<?php

namespace Modules\SiteSettings\Entities;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class site_config_email_alert_defacement_customer extends Model
{

    protected $table = 'site_config_email_alert_defacement_customer';

    protected $fillable = [
        'id','code','site_id','email'
    ];
}
