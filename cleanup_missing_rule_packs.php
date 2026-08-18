<?php
/**
 * Soft-disable / unlink every rule pack whose ZIP is missing on disk.
 * Also removes the legacy ghost pack rules-webshells if present.
 *
 *   php cleanup_missing_rule_packs.php
 *   php cleanup_missing_rule_packs.php --delete-orphan-rows
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use Illuminate\Support\Facades\Schema;

$deleteOrphans = in_array('--delete-orphan-rows', $argv ?? [], true);

function ruleRelPath($path) {
    $path = ltrim(str_replace('\\', '/', (string) $path), '/');
    if ($path === '') {
        return '';
    }
    if (strpos($path, 'rule_files/') === 0) {
        return $path;
    }
    return 'rule_files/' . basename($path);
}

echo "== Cleanup missing rule packs ==\n";
$packs = RuleFile::query()->get();
$missing = 0;
$disabled = 0;
$unlinked = 0;

foreach ($packs as $pack) {
    $rel = ruleRelPath($pack->path);
    $full = $rel !== '' ? public_path($rel) : '';
    $exists = $full !== '' && is_file($full);
    $isGhost = stripos((string) $pack->rule_name, 'rules-webshells') !== false
        || stripos((string) $pack->path, 'rules-webshells.zip') !== false;

    if ($exists && !$isGhost) {
        continue;
    }

    $missing++;
    echo "MISSING/GHOST id={$pack->id} name={$pack->rule_name} path={$pack->path} rel={$rel}\n";

    if (Schema::hasColumn('rule_files', 'status')) {
        $pack->status = 'N';
        $pack->save();
        $disabled++;
    }

    $n1 = RuleFileSiteDownload::where('rule_files_id', $pack->id)->update(['status' => 'N']);
    $unlinked += (int) $n1;

    if ($deleteOrphans || $isGhost) {
        RuleFileSiteDownload::where('rule_files_id', $pack->id)->delete();
        if (Schema::hasTable('rule_files_site_agent_downloads')) {
            RuleFileSiteAgentDownload::where('rule_files_id', $pack->id)->delete();
        }
        if ($isGhost) {
            $pack->delete();
            echo "  deleted ghost pack row\n";
        }
    }
}

echo "missing_or_ghost={$missing} soft_disabled={$disabled} site_rows_flagged={$unlinked}\n";
echo "DONE — run reset_all_ti_queues.php after confirming category zips exist, then Sync on agents\n";
