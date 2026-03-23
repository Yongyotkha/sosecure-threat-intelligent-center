<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteSettings;

class TransactionScansCveTemp extends Model
{
    protected $table = 'transaction_scans_cve_temp';

    protected $fillable = [
        'code',
        'site_id',
        'domain_id',
        'namecve',
        'severity',
        'cvss_score',
        'description',
        'published',
        'modified',
        'target',
        'affected_cpe',
        'cpe_code',
        'cpe_uri',
        'source',
        'is_mapped',
    ];

    public function get_domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function get_site()
    {
        return $this->belongsTo(SiteSettings::class, 'site_id');
    }
}
