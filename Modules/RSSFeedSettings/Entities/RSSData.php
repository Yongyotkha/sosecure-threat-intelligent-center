<?php

namespace Modules\RSSFeedSettings\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class RSSData extends Model
{
    use SoftDeletes;
    protected $table = 'rss';
    protected $fillable = [];
}
