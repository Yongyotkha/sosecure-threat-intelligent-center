<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'site';

    protected $fillable = [];

    public static function getSite($id)
    {
        $site = Site::where('id', $id)->first();
        return $site->name ?? '-';
    }
}
