<?php
/**
 * Reset ssdeep agent download queues (keep existing ssdeep_file packs).
 *
 * Ssdeep is versioned packs (sqlite/json zip), NOT category-split like YARA.
 * Use this after you uploaded/confirmed packs on All Ssdeep Pack + Ssdeep Site.
 *
 *   php reset_ssdeep_agent_queue.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\SsdeepFile;
use App\SsdeepFileSite;
use App\SsdeepFileSiteAgentDownload;
use Illuminate\Support\Facades\Schema;

echo "== Active ssdeep packs ==\n";
$packs = SsdeepFile::whereIn('status', ['Y', '1'])->orderBy('id', 'desc')->get();
foreach ($packs as $p) {
    echo "id={$p->id} version={$p->version} file={$p->file_name} sigs={$p->signature_count}\n";
}
if ($packs->isEmpty()) {
    fwrite(STDERR, "No active ssdeep packs. Upload via /agent_rule → All Ssdeep Pack first.\n");
    exit(1);
}

echo "== Site links ==\n";
$links = SsdeepFileSite::where('status', 'Y')->get();
foreach ($links as $l) {
    echo "site={$l->site_id} ssdeep_file_id={$l->ssdeep_file_id}\n";
}
if ($links->isEmpty()) {
    fwrite(STDERR, "No site linked. Assign packs on Ssdeep Site tab.\n");
    exit(1);
}

echo "== Reset agent ssdeep download transactions ==\n";
if (Schema::hasTable('ssdeep_file_site_agent_downloads')) {
    $n = SsdeepFileSiteAgentDownload::query()->update(['transaction_download_client' => 0]);
    echo "reset rows={$n}\n";
}

echo "DONE — agents will re-download on next Sync\n";
