<?php

namespace Modules\SiteSettings\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
// use Modules\SiteSettings\Entities\SiteCategory;

class DataCveven extends Model {

    protected $table = "data_cveven";
    public $timestamps = true;
    protected $fillable = [
        'navecve','vendor','title', 'version', 'edition', 'rawtext'
    ];

    protected $dates   = ['created_at'];



}
