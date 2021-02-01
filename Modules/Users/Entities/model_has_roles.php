<?php

namespace Modules\Users\Entities;


use Illuminate\Database\Eloquent\Model;


class model_has_roles extends Model
{
    protected $fillable = ['code', 'role_id', 'model_type', 'model_id', 'id'];
    protected $guarded = [];
    protected $table = 'model_has_roles';

    public $timestamps = false;

 
}
