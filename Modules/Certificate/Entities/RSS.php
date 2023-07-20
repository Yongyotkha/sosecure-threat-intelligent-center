<?php

namespace Modules\RSSFeedSettings\Entities;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RSS extends Model
{
    use SoftDeletes, BelongsToUser;

    protected $guarded = [];
    protected $table = 'rss';
    public $timestamps = true;
}
