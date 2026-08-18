<?php
/**
 * Full YARA reset: replace packs with category ZIPs + rebuild rule_name catalog.
 *
 * Prerequisites:
 *   1) Upload these ZIPs into public/rule_files/ :
 *        malware.zip, cve_rules.zip, exploit_kits.zip, maldocs.zip, webshells.zip,
 *        antidebug_antivm.zip, capabilities.zip, crypto.zip, packers.zip, utils.zip
 *   2) From Laravel project root:
 *        php reset_yara_category_packs.php
 *
 * Optional:
 *   KEEP_OLD_CATALOG=1  — do not soft-delete existing rule_name rows
 *   SITE_ID=123         — only link this site (default: all sites)
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\RuleFile;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use App\RuleNameSite;
use App\TBLRuleName;
use App\TBLRuleCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

$keepOldCatalog = getenv('KEEP_OLD_CATALOG') === '1';
$onlySiteId = getenv('SITE_ID') !== false && getenv('SITE_ID') !== '' ? (int) getenv('SITE_ID') : null;

$expected = [
    'malware',
    'cve_rules',
    'exploit_kits',
    'maldocs',
    'webshells',
    'antidebug_antivm',
    'capabilities',
    'crypto',
    'packers',
    'utils',
];

$dir = public_path('rule_files');
if (!is_dir($dir)) {
    File::makeDirectory($dir, 0777, true, true);
}

echo "== Check category ZIPs in {$dir} ==\n";
$missing = [];
foreach ($expected as $cat) {
    $path = $dir . DIRECTORY_SEPARATOR . $cat . '.zip';
    if (!is_file($path)) {
        $missing[] = $cat . '.zip';
        echo "MISSING {$cat}.zip\n";
    } else {
        echo "OK {$cat}.zip (" . filesize($path) . " bytes)\n";
    }
}
if ($missing) {
    fwrite(STDERR, "Upload missing ZIPs first: " . implode(', ', $missing) . "\n");
    exit(1);
}

echo "== Soft-disable old rule_files packs (keep category zips) ==\n";
$keepNames = array_map(function ($c) {
    return $c . '.zip';
}, $expected);
$keepRels = [];
foreach ($keepNames as $n) {
    $keepRels[] = 'rule_files/' . $n;
    $keepRels[] = '/rule_files/' . $n;
}

$oldPacks = RuleFile::query()->get();
foreach ($oldPacks as $pack) {
    $base = strtolower(basename((string) $pack->path));
    if (in_array($base, array_map('strtolower', $keepNames), true)) {
        continue;
    }
    // Delete file on disk if present and not a keep pack
    $full = public_path(ltrim(str_replace('\\', '/', (string) $pack->path), '/'));
    if (is_file($full) && !in_array(strtolower(basename($full)), array_map('strtolower', $keepNames), true)) {
        echo "DEL FILE " . basename($full) . "\n";
        @unlink($full);
    }
    if (Schema::hasColumn('rule_files', 'status')) {
        $pack->status = 'N';
        $pack->save();
    }
}

// Also delete any other zips in folder that are not expected
foreach (File::files($dir) as $f) {
    if (strtolower($f->getExtension()) !== 'zip') {
        continue;
    }
    if (in_array($f->getFilename(), $keepNames, true)) {
        continue;
    }
    echo "DEL EXTRA " . $f->getFilename() . "\n";
    File::delete($f->getPathname());
}

echo "== Ensure categories ==\n";
$categoryIds = [];
foreach ($expected as $cat) {
    $row = TBLRuleCategory::where('name', $cat)->where(function ($q) {
        $q->whereNull('deleted_at')->orWhere('deleted_at', '');
    })->first();
    if (!$row) {
        $row = TBLRuleCategory::where('name', $cat)->first();
    }
    if (!$row) {
        $row = new TBLRuleCategory();
        $row->name = $cat;
        if (Schema::hasColumn('rule_category', 'mode')) {
            $row->mode = 'category';
        }
        if (Schema::hasColumn('rule_category', 'status')) {
            $row->status = 'Y';
        }
        $row->save();
        echo "CREATE category {$cat} id={$row->id}\n";
    } else {
        if (Schema::hasColumn('rule_category', 'status')) {
            $row->status = 'Y';
        }
        if (Schema::hasColumn('rule_category', 'deleted_at')) {
            $row->deleted_at = null;
        }
        $row->save();
        echo "USE category {$cat} id={$row->id}\n";
    }
    $categoryIds[$cat] = $row->id;
}

if (!$keepOldCatalog) {
    echo "== Soft-delete old rule_name + rule_name_site ==\n";
    $now = date('Y-m-d H:i:s');
    if (Schema::hasColumn('rule_name', 'deleted_at')) {
        DB::table('rule_name')->whereNull('deleted_at')->update(['deleted_at' => $now]);
    }
    if (Schema::hasTable('rule_name_site') && Schema::hasColumn('rule_name_site', 'deleted_at')) {
        DB::table('rule_name_site')->whereNull('deleted_at')->update(['deleted_at' => $now]);
    }
}

$ruleModel = TBLRuleName::class;

echo "== Upsert rule_files packs + parse catalog ==\n";
$packIds = [];
$totalRules = 0;

foreach ($expected as $cat) {
    $zipName = $cat . '.zip';
    $rel = 'rule_files/' . $zipName;
    $full = $dir . DIRECTORY_SEPARATOR . $zipName;

    $pack = RuleFile::where('path', $rel)
        ->orWhere('path', '/' . $rel)
        ->orWhere('path', 'like', '%' . $zipName)
        ->first();
    if (!$pack) {
        $pack = new RuleFile();
    }
    $pack->path = $rel;
    $pack->rule_name = $cat;
    if (Schema::hasColumn('rule_files', 'rule_category_id')) {
        $pack->rule_category_id = $categoryIds[$cat];
    }
    if (Schema::hasColumn('rule_files', 'version')) {
        $pack->version = '1';
    }
    if (Schema::hasColumn('rule_files', 'status')) {
        $pack->status = 'Y';
    }
    $pack->save();
    $packIds[$cat] = $pack->id;
    echo "PACK {$cat} id={$pack->id}\n";

    $parsed = parseYaraZip($full);
    echo "  parsed files=" . count($parsed['files']) . " rules=" . count($parsed['rules']) . "\n";

    foreach ($parsed['rules'] as $item) {
        $fileName = $item['file_name'];
        $ruleName = $item['rule_name'];

        $rn = $ruleModel::where('rule_name', $ruleName)
            ->where('file_name', $fileName)
            ->first();
        if (!$rn) {
            $rn = new $ruleModel();
        }
        $rn->rule_name = $ruleName;
        $rn->file_name = $fileName;
        if (Schema::hasColumn('rule_name', 'rule_category_id')) {
            $rn->rule_category_id = $categoryIds[$cat];
        }
        if (Schema::hasColumn('rule_name', 'rule_file_id')) {
            $rn->rule_file_id = $pack->id;
        }
        if (Schema::hasColumn('rule_name', 'status')) {
            $rn->status = 'Y';
        }
        if (Schema::hasColumn('rule_name', 'severity') && empty($rn->severity)) {
            $rn->severity = 'medium';
        }
        if (Schema::hasColumn('rule_name', 'deleted_at')) {
            $rn->deleted_at = null;
        }
        $rn->save();
        $totalRules++;
    }
}

echo "== Link packs + rules to sites ==\n";
$sites = DB::table('site')->pluck('id');
if ($onlySiteId) {
    $sites = collect([$onlySiteId]);
}

$ruleIds = $ruleModel::query()
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

$hasCreateBy = Schema::hasColumn('rule_name_site', 'create_by');
$hasUpdateBy = Schema::hasColumn('rule_name_site', 'update_by');
$hasDeletedAt = Schema::hasColumn('rule_name_site', 'deleted_at');
$hasCreatedAt = Schema::hasColumn('rule_name_site', 'created_at');
$hasUpdatedAt = Schema::hasColumn('rule_name_site', 'updated_at');
$now = date('Y-m-d H:i:s');

foreach ($sites as $siteId) {
    $siteId = (int) $siteId;
    foreach ($packIds as $cat => $packId) {
        $row = RuleFileSiteDownload::where('site_id', $siteId)->where('rule_files_id', $packId)->first();
        if (!$row) {
            $row = new RuleFileSiteDownload();
            $row->site_id = $siteId;
            $row->rule_files_id = $packId;
        }
        $row->status = 'Y';
        $row->save();
    }

    RuleFileSiteDownload::where('site_id', $siteId)
        ->whereNotIn('rule_files_id', array_values($packIds))
        ->update(['status' => 'N']);

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
        $row = ['site_id' => $siteId, 'rule_id' => $ruleId];
        if ($hasCreateBy) {
            $row['create_by'] = 1;
        }
        if ($hasUpdateBy) {
            $row['update_by'] = 1;
        }
        if ($hasDeletedAt) {
            $row['deleted_at'] = null;
        }
        if ($hasCreatedAt) {
            $row['created_at'] = $now;
        }
        if ($hasUpdatedAt) {
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

echo "DONE packs=" . count($packIds) . " catalog_rules_upserted~={$totalRules}\n";

/**
 * @return array{files: string[], rules: array<int, array{file_name:string, rule_name:string}>}
 */
function parseYaraZip($zipPath)
{
    $out = ['files' => [], 'rules' => []];
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return $out;
    }
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (!$stat || substr($stat['name'], -1) === '/') {
            continue;
        }
        $name = str_replace('\\', '/', $stat['name']);
        $base = basename($name);
        if (stripos($base, '.yar') === false) {
            continue;
        }
        if (strpos($name, '__MACOSX/') !== false || strpos($base, '._') === 0) {
            continue;
        }
        if (strcasecmp($base, '_insite_all.yar') === 0) {
            continue;
        }
        $out['files'][] = $name;
        $content = $zip->getFromIndex($i);
        if ($content === false) {
            continue;
        }
        if (preg_match_all('/^\s*(?:private\s+)?rule\s+([A-Za-z_][A-Za-z0-9_]*)\b/m', $content, $m)) {
            foreach ($m[1] as $ruleName) {
                $out['rules'][] = [
                    'file_name' => $name,
                    'rule_name' => $ruleName,
                ];
            }
        }
    }
    $zip->close();
    return $out;
}
