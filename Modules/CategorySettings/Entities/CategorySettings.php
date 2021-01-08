<?php

namespace Modules\CategorySettings\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class CategorySettings extends Model {
    use SoftDeletes;
    protected $table = "categories";
    public $timestamps = false;
    protected $fillable = [
        'id','name', 'module', 'color', 'active', 'order', 'description', 'pipeline',
    ];

    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];


    public function preferredLocale()
    {
        return $this->locale;
    }
}
