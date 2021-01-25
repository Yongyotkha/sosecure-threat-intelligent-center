<?php

namespace Modules\Scans\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Scans\Entities\AssetsData;
use Modules\SiteSettings\Entities\SiteSettings;

class CPE extends Model
{
    protected $fillable = [];
    protected $table = 'cpe';
}
