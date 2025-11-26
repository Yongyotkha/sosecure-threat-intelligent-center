<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CveAssetsProgress extends Model
{
    // ชื่อตารางในฐานข้อมูล
    protected $table = 'cve_assets_progress';

    // ระบุ field ที่สามารถ fill ได้
    protected $fillable = [
        'last_asset_id',
        'last_cve_name',
    ];

    // ปิด timestamps ได้ถ้าไม่ต้องการ auto update
    public $timestamps = true;
}
