<?php
/**
 * Reset BOTH YARA and ssdeep agent download queues so next Sync pulls everything.
 *
 *   php reset_all_ti_queues.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use App\SsdeepFile;
use App\SsdeepFileSite;
use App\SsdeepFileSiteAgentDownload;
use Illuminate\Support\Facades\Schema;

$expected = [
    'malware', 'cve_rules', 'exploit_kits', 'maldocs', 'webshells',
    'antidebug_antivm', 'capabilities', 'crypto', 'packers', 'utils',
];

echo "== Ensure category packs are Y for all sites ==\n";
$packIds = [];
foreach ($expected as $cat) {
    $zip = $cat . '.zip';
    $pack = RuleFile::where('rule_name', $cat)
        ->orWhere('path', 'like', '%' . $zip)
        ->orderBy('id', 'desc')
        ->first();
    if (!$pack) {
        echo "MISSING pack {$cat}\n";
        continue;
    }
    $packIds[$cat] = (int) $pack->id;
    if (Schema::hasColumn('rule_files', 'status')) {
        $pack->status = 'Y';
        $pack->save();
    }
    echo "pack {$cat} id={$pack->id}\n";
}

$sites = \DB::table('site')->pluck('id');
foreach ($sites as $siteId) {
    foreach ($packIds as $packId) {
        $row = RuleFileSiteDownload::where('site_id', $siteId)->where('rule_files_id', $packId)->first();
        if (!$row) {
            $row = new RuleFileSiteDownload();
            $row->site_id = $siteId;
            $row->rule_files_id = $packId;
        }
        $row->status = 'Y';
        $row->save();
    }
}
echo "site pack links refreshed for " . count($sites) . " sites\n";

echo "== Reset YARA agent queues ==\n";
if (Schema::hasTable('rule_files_site_agent_downloads')) {
    // Remove rows so downloadRuleSite recreates with transaction=2
    $n = RuleFileSiteAgentDownload::query()->delete();
    echo "deleted yara agent download rows={$n}\n";
}

echo "== Reset ssdeep agent queues ==\n";
$ssdeepPacks = SsdeepFile::whereIn('status', ['Y', '1'])->count();
$ssdeepLinks = SsdeepFileSite::where('status', 'Y')->count();
echo "active ssdeep packs={$ssdeepPacks} site_links={$ssdeepLinks}\n";
if (Schema::hasTable('ssdeep_file_site_agent_downloads')) {
    $n = SsdeepFileSiteAgentDownload::query()->delete();
    echo "deleted ssdeep agent download rows={$n}\n";
}

echo "DONE — agents will re-download YARA + ssdeep on next Sync\n";
