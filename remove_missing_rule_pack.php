<?php
/**
 * Remove leftover missing pack rules-webshells (id 52 by default).
 *
 *   php remove_missing_rule_pack.php
 *   PACK_ID=52 php remove_missing_rule_pack.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use Illuminate\Support\Facades\Schema;

$packId = getenv('PACK_ID') !== false && getenv('PACK_ID') !== '' ? (int) getenv('PACK_ID') : 52;

$pack = RuleFile::find($packId);
if (!$pack) {
    // fallback by name/path
    $pack = RuleFile::where('rule_name', 'rules-webshells')
        ->orWhere('path', 'like', '%rules-webshells.zip%')
        ->first();
}

if (!$pack) {
    echo "Pack not found (id={$packId}). Nothing to delete.\n";
    exit(0);
}

echo "Deleting pack id={$pack->id} path={$pack->path} name={$pack->rule_name}\n";

$n1 = RuleFileSiteDownload::where('rule_files_id', $pack->id)->delete();
echo "deleted rule_files_site_downloads={$n1}\n";

if (Schema::hasTable('rule_files_site_agent_downloads')) {
    $n2 = RuleFileSiteAgentDownload::where('rule_files_id', $pack->id)->delete();
    echo "deleted rule_files_site_agent_downloads={$n2}\n";
}

$pack->delete();
echo "DONE\n";
