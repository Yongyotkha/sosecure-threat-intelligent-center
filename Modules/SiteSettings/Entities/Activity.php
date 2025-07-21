<?php
namespace Modules\SiteSettings\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model {
    use SoftDeletes;
    protected $table = "activity";
    public $timestamps = true;
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
}
