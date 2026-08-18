<?php
/**
 * Report duplicate active agent IPs within each site.
 *
 * Usage (from project root):
 *   php report_duplicate_agent_ips.php
 *
 * Does not modify data. Soft-delete extras via migration/SQL before adding the unique index.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('site_agents')) {
    fwrite(STDERR, "Table site_agents not found (with DB prefix).\n");
    exit(1);
}

$table = 'site_agents';

$rows = DB::table($table)
    ->select('site_id', 'ip_private', DB::raw('COUNT(*) as cnt'), DB::raw('GROUP_CONCAT(id ORDER BY id) as agent_ids'))
    ->whereNull('deleted_at')
    ->whereNotNull('ip_private')
    ->where('ip_private', '!=', '')
    ->groupBy('site_id', 'ip_private')
    ->having('cnt', '>', 1)
    ->orderBy('site_id')
    ->orderBy('ip_private')
    ->get();

if ($rows->isEmpty()) {
    echo "No duplicate active IPs found.\n";
    exit(0);
}

echo "Duplicate active IPs (site_id | ip | count | agent_ids):\n";
foreach ($rows as $r) {
    echo sprintf(
        "%s | %s | %d | %s\n",
        $r->site_id,
        $r->ip_private,
        $r->cnt,
        $r->agent_ids
    );
}

echo "\nTo resolve: keep the lowest agent id, soft-delete the rest, then run migration/SQL for ip_unique_key.\n";
exit(2);
