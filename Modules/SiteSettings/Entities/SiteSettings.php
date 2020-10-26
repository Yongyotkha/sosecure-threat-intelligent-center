<?php

namespace Modules\SiteSettings\Entities;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\SiteCategory;

class SiteSettings extends Model{
    protected $guarded = [];
    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id','code','name', 'descript', 'logo', 'address', 'remark', 'active',
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    public function preferredLocale()
    {
        return $this->locale;
    }

    public function find_id($uuid){
        return $this->select('id')->where('code', $uuid)->first();
    }

    public function get_data($uuid, $active = null){
        $data = $this->select('id', 'code', 'name', 'descript', 'logo', 'address', 'remark', 'active', 'created_at', 'updated_at')
        ->where('code', $uuid)
        ->where('deleted_at', '=', null);
        if($active !== null){
            $data->where('active', $active);
        }
        return $data->first();
    }

    public function get_categorys(){
        return $this->hasMany(SiteCategory::class, 'site_id', 'id');
    }
}
