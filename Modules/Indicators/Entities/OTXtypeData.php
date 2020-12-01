<?php

namespace Modules\indicators\Entities;

use Illuminate\Database\Eloquent\Model;

class OTXtypeData extends Model
{
    protected $table = 'otx_type';
    protected $fillable = [];

    // public function get_rss(){
    //     return $this->belongsTo(RSSData::class, 'rss_id', 'id');
    // }
}