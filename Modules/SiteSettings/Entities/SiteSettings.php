<?php

namespace Modules\SiteSettings\Entities;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model{
    use Observable;

    protected static $observer = SetupObserver::class;
    protected static $scope    = null;

    protected $table = "site";
    public $timestamps = true;
    protected $fillable = [
        'id','name', 'descript', 'logo', 'address', 'remark', 'active',
    ];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    public function preferredLocale()
    {
        return $this->locale;
    }
}
