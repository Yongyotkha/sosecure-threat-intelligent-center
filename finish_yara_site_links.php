<?php
/**
 * Fast finish after SSH drop: link packs/rules to sites + reset agent queue.
 * Skips re-parsing ZIPs (assumes packs + rule_name already imported).
 *
 *   php finish_yara_site_links.php
 *   SITE_ID=59 php finish_yara_site_links.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$onlySiteId = getenv('SITE_ID') !== false && getenv('SITE_ID') !== '' ? (int) getenv('SITE_ID') : null;

$expected = [
    'malware', 'cve_rules', 'exploit_kits', 'maldocs', 'webshells',
    'antidebug_antivm', 'capabilities', 'crypto', 'packers', 'utils',
];

echo "== Resolve pack IDs ==\n";
$packIds = [];
foreach ($expected as $cat) {
    $zip = $cat . '.zip';
    $pack = RuleFile::where('path', 'rule_files/' . $zip)
        ->orWhere('path', '/rule_files/' . $zip)
        ->orWhere('path', 'like', '%' . $zip)
        ->orWhere('rule_name', $cat)
        ->orderBy('id', 'desc')
        ->first();
    if (!$pack) {
        fwrite(STDERR, "MISSING pack for {$cat}\n");
        exit(1);
    }
    $packIds[$cat] = (int) $pack->id;
    echo "{$cat} => id={$pack->id}\n";
}

$ruleIds = DB::table('rule_name')
    ->where(function ($q) {
        $q->whereNull('deleted_at');
    })
    ->when(Schema::hasColumn('rule_name', 'status'), function ($q) {
        $q->where('status', 'Y');
    })
    ->pluck('id')
    ->map(function ($id) {
        return (int) $id;
    })
    ->all();

echo "active rule_name count=" . count($ruleIds) . "\n";
if (!$ruleIds) {
    fwrite(STDERR, "No active rules in rule_name. Re-run full reset_yara_category_packs.php first.\n");
    exit(1);
}

$sites = DB::table('site')->pluck('id')->map(function ($id) {
    return (int) $id;
})->all();
if ($onlySiteId) {
    $sites = [$onlySiteId];
}

$hasCreateBy = Schema::hasColumn('rule_name_site', 'create_by');
$hasUpdateBy = Schema::hasColumn('rule_name_site', 'update_by');
$hasDeletedAt = Schema::hasColumn('rule_name_site', 'deleted_at');
$now = date('Y-m-d H:i:s');

echo "== Link packs + bulk link rules ==\n";
foreach ($sites as $siteId) {
    foreach ($packIds as $packId) {
        $exists = DB::table('rule_files_site_downloads')
            ->where('site_id', $siteId)
            ->where('rule_files_id', $packId)
            ->first();
        if ($exists) {
            DB::table('rule_files_site_downloads')->where('id', $exists->id)->update(['status' => 'Y']);
        } else {
            DB::table('rule_files_site_downloads')->insert([
                'site_id' => $siteId,
                'rule_files_id' => $packId,
                'status' => 'Y',
            ]);
        }
    }
    DB::table('rule_files_site_downloads')
        ->where('site_id', $siteId)
        ->whereNotIn('rule_files_id', array_values($packIds))
        ->update(['status' => 'N']);

    // Reactivate existing links
    if ($hasDeletedAt) {
        DB::table('rule_name_site')
            ->where('site_id', $siteId)
            ->whereIn('rule_id', $ruleIds)
            ->update(['deleted_at' => null]);
    }

    $existing = DB::table('rule_name_site')
        ->where('site_id', $siteId)
        ->whereIn('rule_id', $ruleIds)
        ->pluck('rule_id')
        ->map(function ($id) {
            return (int) $id;
        })
        ->all();
    $existingMap = array_fill_keys($existing, true);
    $toInsert = [];
    foreach ($ruleIds as $ruleId) {
        if (isset($existingMap[$ruleId])) {
            continue;
        }
        $row = [
            'site_id' => $siteId,
            'rule_id' => $ruleId,
        ];
        if ($hasCreateBy) {
            $row['create_by'] = 1;
        }
        if ($hasUpdateBy) {
            $row['update_by'] = 1;
        }
        if ($hasDeletedAt) {
            $row['deleted_at'] = null;
        }
        if (Schema::hasColumn('rule_name_site', 'created_at')) {
            $row['created_at'] = $now;
        }
        if (Schema::hasColumn('rule_name_site', 'updated_at')) {
            $row['updated_at'] = $now;
        }
        $toInsert[] = $row;
        if (count($toInsert) >= 500) {
            DB::table('rule_name_site')->insert($toInsert);
            $toInsert = [];
        }
    }
    if ($toInsert) {
        DB::table('rule_name_site')->insert($toInsert);
    }

    echo "SITE {$siteId}: packs=" . count($packIds) . " rules=" . count($ruleIds) . "\n";
}

echo "== Reset agent YARA download queues ==\n";
if (Schema::hasTable('rule_files_site_agent_downloads')) {
    RuleFileSiteAgentDownload::query()->update(['transaction_download_client' => 0]);
}

echo "DONE\n";
