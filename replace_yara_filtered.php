<?php
/**
 * Replace Center YARA packs with insite_yara_filtered.zip
 *
 * Usage (from Laravel project root):
 *   php replace_yara_filtered.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

$keepName = 'insite_yara_filtered.zip';
$keepRel  = 'rule_files/' . $keepName;
$dir = public_path('rule_files');

if (!is_dir($dir)) {
    fwrite(STDERR, "Missing directory: {$dir}\n");
    exit(1);
}

if (!is_file($dir . DIRECTORY_SEPARATOR . $keepName)) {
    fwrite(STDERR, "Upload {$keepName} to {$dir} first!\n");
    exit(1);
}

echo "== Delete other ZIP files ==\n";
foreach (File::files($dir) as $f) {
    if (strtolower($f->getExtension()) !== 'zip') {
        continue;
    }
    if ($f->getFilename() === $keepName) {
        continue;
    }
    echo "DEL FILE " . $f->getFilename() . "\n";
    File::delete($f->getPathname());
}

echo "== Upsert pack row ==\n";
$pack = RuleFile::where('path', $keepRel)
    ->orWhere('path', '/' . $keepRel)
    ->orWhere('path', 'like', '%' . $keepName)
    ->first();
if (!$pack) {
    $pack = new RuleFile();
}
$pack->path = $keepRel;
$pack->rule_name = 'insite_yara_filtered';
if (Schema::hasColumn('rule_files', 'version')) {
    $pack->version = '1';
}
if (Schema::hasColumn('rule_files', 'transaction_download_client')) {
    $pack->transaction_download_client = 1;
}
$pack->save();
echo "PACK ID {$pack->id} path={$pack->path}\n";

echo "== Reassign site packs ==\n";
$oldIds = RuleFile::where('id', '!=', $pack->id)->pluck('id');
$siteIds = RuleFileSiteDownload::whereIn('rule_files_id', $oldIds)
    ->orWhere('rule_files_id', $pack->id)
    ->pluck('site_id')
    ->unique()
    ->filter()
    ->values();

if ($siteIds->isEmpty()) {
    // Fallback: all sites that ever had any pack assignment
    $siteIds = RuleFileSiteDownload::query()->pluck('site_id')->unique()->filter()->values();
}

foreach ($oldIds as $oldId) {
    RuleFileSiteDownload::where('rule_files_id', $oldId)->update(['status' => 'N']);
}

foreach ($siteIds as $siteId) {
    $exist = RuleFileSiteDownload::where('site_id', $siteId)
        ->where('rule_files_id', $pack->id)
        ->first();
    if (!$exist) {
        $n = new RuleFileSiteDownload();
        $n->site_id = $siteId;
        $n->rule_files_id = $pack->id;
        $n->status = 'Y';
        $n->save();
        echo "SITE {$siteId}: linked new pack\n";
    } else {
        $exist->status = 'Y';
        $exist->save();
        echo "SITE {$siteId}: enabled new pack\n";
    }
}

echo "== Reset agent downloads ==\n";
if (Schema::hasTable('rule_files_site_agent_downloads')) {
    RuleFileSiteAgentDownload::query()->update(['transaction_download_client' => 0]);
}

echo "DONE keep={$keepRel} pack_id={$pack->id}\n";
