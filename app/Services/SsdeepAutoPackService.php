<?php

namespace App\Services;

use App\SsdeepCandidate;
use App\SsdeepFile;
use App\SsdeepFileSite;
use App\SsdeepFileSiteAgentDownload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use ZipArchive;

/**
 * Promote detection candidates into NEW category ssdeep pack files (never overwrite an existing pack row).
 */
class SsdeepAutoPackService
{
    /**
     * @return string promoted|discarded|skipped_empty|skipped_disabled|failed
     */
    public function promoteCandidate(SsdeepCandidate $candidate)
    {
        if (!$this->autoPromoteEnabled()) {
            return 'skipped_disabled';
        }

        $this->purgeLegacyDuplicateCandidates();

        $hash = $this->normalizeHash($candidate->ssdeep);
        if ($hash === '') {
            $candidate->delete();
            return 'skipped_empty';
        }

        try {
            $category = $this->categorize(
                (string) $candidate->file_name,
                (string) $candidate->rule,
                (string) $candidate->path
            );
            $siteId = (int) $candidate->site_id;

            // Already known → delete candidate row (do not keep "duplicate" status).
            if ($this->isKnownHash($hash, (int) $candidate->id)) {
                $candidate->delete();
                return 'discarded';
            }

            // Carry forward signatures from latest pack so agents do not lose older hashes,
            // but always create a NEW pack file for this unique hash (never overwrite).
            $basePack = $this->findLatestCategoryPack($category);
            $entries = $basePack ? $this->loadPackEntries($basePack) : [];
            $entries = $this->uniqueEntriesByHash($entries);

            foreach ($entries as $entry) {
                if ($this->normalizeHash(isset($entry['ssdeep']) ? $entry['ssdeep'] : '') === $hash) {
                    $candidate->delete();
                    return 'discarded';
                }
            }

            $entries[] = [
                'name' => $this->signatureName($category, $candidate),
                'family' => $category,
                'ssdeep' => $hash,
            ];

            $pack = $this->createNewPackRow($category);
            $this->writePackFiles($pack, $category, $entries);

            if ($this->isGlobalAutoDistributeEnabled()) {
                $this->assignPackToAllSites($pack->id, $category);
            } else {
                $this->assignSiteToNewPackOnly($pack->id, $siteId, $category);
                $this->requeueAgents($pack->id, $siteId);
            }

            $candidate->status = 'promoted';
            $candidate->save();

            return 'promoted';
        } catch (\Exception $e) {
            Log::warning('SsdeepAutoPackService failed: '.$e->getMessage(), [
                'candidate_id' => $candidate->id,
            ]);
            try {
                $candidate->status = 'failed';
                $candidate->save();
            } catch (\Exception $ignore) {
            }
            return 'failed';
        }
    }

    public function promoteQueued($limit = 100)
    {
        $q = SsdeepCandidate::where('status', 'queued')
            ->whereNotNull('ssdeep')
            ->orderBy('id')
            ->limit((int) $limit)
            ->get();

        $stats = [
            'promoted' => 0,
            'discarded' => 0,
            'duplicate' => 0,
            'skipped_duplicate' => 0,
            'skipped_empty' => 0,
            'skipped_disabled' => 0,
            'failed' => 0,
        ];

        foreach ($q as $row) {
            $result = $this->promoteCandidate($row);
            if ($result === 'discarded') {
                $stats['discarded']++;
                // legacy alias for callers expecting "duplicate"
                $stats['duplicate']++;
            } elseif (!isset($stats[$result])) {
                $stats[$result] = 0;
                $stats[$result]++;
            } else {
                $stats[$result]++;
            }
        }

        return $stats;
    }

    public function autoPromoteEnabled()
    {
        $v = env('SSDEEP_AUTO_PROMOTE', 'true');
        if (is_bool($v)) {
            return $v;
        }
        $s = strtolower(trim((string) $v));
        return in_array($s, ['1', 'true', 'yes', 'on'], true);
    }

    protected function normalizeHash($hash)
    {
        return trim((string) $hash);
    }

    /**
     * True if this fuzzy hash was already promoted or appears in any active pack.
     */
    public function isKnownHash($hash, $excludeCandidateId = 0)
    {
        return $this->findDuplicateMatch($hash, $excludeCandidateId) !== null;
    }

    /**
     * @return array{type:string,note:string}|null
     */
    protected function findDuplicateMatch($hash, $excludeCandidateId = 0)
    {
        $hash = $this->normalizeHash($hash);
        if ($hash === '') {
            return null;
        }

        // Only promoted rows matter — duplicates are deleted, not retained.
        $candQ = SsdeepCandidate::where('status', 'promoted')->where('ssdeep', $hash);
        if ($excludeCandidateId > 0) {
            $candQ->where('id', '!=', $excludeCandidateId);
        }
        $other = $candQ->orderBy('id')->first();
        if ($other) {
            return ['type' => 'candidate', 'note' => 'already promoted #'.$other->id];
        }

        $packs = SsdeepFile::where('status', 'Y')->orderBy('id', 'desc')->get();
        foreach ($packs as $pack) {
            foreach ($this->loadPackEntries($pack) as $entry) {
                if ($this->normalizeHash(isset($entry['ssdeep']) ? $entry['ssdeep'] : '') === $hash) {
                    return ['type' => 'pack', 'note' => 'in pack #'.$pack->id];
                }
            }
        }

        return null;
    }

    /**
     * Light cleanup used on list load — only remove retired status rows.
     * Full dedupe (in-pack / hash dupes) runs via cleanupDuplicateCandidatesDetailed() on button click.
     *
     * @return int
     */
    public function purgeLegacyDuplicateCandidates()
    {
        if (!Schema::hasTable('ssdeep_candidate')) {
            return 0;
        }
        return (int) SsdeepCandidate::whereIn('status', [
            'duplicate', 'skipped_duplicate', 'skipped_empty',
        ])->delete();
    }

    /**
     * @return array{legacy:int,in_pack:int,hash_dupes:int,total:int,message:string}
     */
    public function cleanupDuplicateCandidatesDetailed()
    {
        $out = [
            'legacy' => 0,
            'in_pack' => 0,
            'hash_dupes' => 0,
            'total' => 0,
            'message' => '',
        ];
        if (!Schema::hasTable('ssdeep_candidate')) {
            $out['message'] = 'ssdeep_candidate table missing';
            return $out;
        }

        $out['legacy'] = $this->purgeLegacyDuplicateCandidates();

        // Hash already in any Master pack → remove all candidate rows for that hash.
        $packHashes = $this->collectActivePackHashes();
        if (!empty($packHashes)) {
            $rows = SsdeepCandidate::whereNotNull('ssdeep')->where('ssdeep', '!=', '')->get();
            foreach ($rows as $row) {
                $h = $this->normalizeHash($row->ssdeep);
                if ($h !== '' && isset($packHashes[$h])) {
                    $row->delete();
                    $out['in_pack']++;
                }
            }
        }

        // Same fuzzy hash left in table → keep oldest promoted (else oldest), delete the rest.
        $grouped = SsdeepCandidate::whereNotNull('ssdeep')
            ->where('ssdeep', '!=', '')
            ->orderBy('id')
            ->get()
            ->groupBy(function ($row) {
                return $this->normalizeHash($row->ssdeep);
            });

        foreach ($grouped as $hash => $rows) {
            if ($hash === '' || $rows->count() < 2) {
                continue;
            }
            $keep = $rows->firstWhere('status', 'promoted');
            if (!$keep) {
                $keep = $rows->first();
            }
            foreach ($rows as $row) {
                if ((int) $row->id === (int) $keep->id) {
                    continue;
                }
                $row->delete();
                $out['hash_dupes']++;
            }
        }

        $out['total'] = (int) $out['legacy'] + (int) $out['in_pack'] + (int) $out['hash_dupes'];
        $out['message'] = sprintf(
            'ลบ candidate ซ้ำแล้ว %d รายการ (legacy %d, มีใน pack แล้ว %d, hash ซ้ำในตาราง %d)',
            $out['total'],
            $out['legacy'],
            $out['in_pack'],
            $out['hash_dupes']
        );

        return $out;
    }

    /**
     * @return array<string,true> normalized ssdeep hash => true
     */
    protected function collectActivePackHashes()
    {
        $map = [];
        if (!Schema::hasTable('ssdeep_file')) {
            return $map;
        }
        foreach (SsdeepFile::where('status', 'Y')->orderBy('id', 'desc')->get() as $pack) {
            foreach ($this->loadPackEntries($pack) as $entry) {
                $h = $this->normalizeHash(isset($entry['ssdeep']) ? $entry['ssdeep'] : '');
                if ($h !== '') {
                    $map[$h] = true;
                }
            }
        }
        return $map;
    }

    /**
     * @return int total deleted candidate rows
     */
    public function cleanupDuplicateCandidates()
    {
        $r = $this->cleanupDuplicateCandidatesDetailed();
        return (int) $r['total'];
    }

    protected function uniqueEntriesByHash(array $entries)
    {
        $out = [];
        $seen = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $h = $this->normalizeHash(isset($entry['ssdeep']) ? $entry['ssdeep'] : '');
            if ($h === '' || isset($seen[$h])) {
                continue;
            }
            $seen[$h] = true;
            $out[] = $entry;
        }
        return $out;
    }

    protected function categorize($fileName, $rule, $path)
    {
        $blob = strtolower(trim($fileName.' '.$rule.' '.$path));

        if (strpos($blob, 'ransom') !== false || strpos($blob, 'lockbit') !== false || strpos($blob, 'wannacry') !== false) {
            return 'ransomware';
        }
        if (strpos($blob, 'webshell') !== false || strpos($blob, 'c99') !== false || strpos($blob, 'r57') !== false) {
            return 'webshells';
        }
        if (strpos($blob, 'trojan') !== false || strpos($blob, 'backdoor') !== false || strpos($blob, '(c2)') !== false) {
            return 'trojan';
        }

        $ext = strtolower(pathinfo($fileName ?: $path, PATHINFO_EXTENSION));
        if ($ext !== '' && $ext[0] !== '.') {
            $ext = '.'.$ext;
        }

        switch ($ext) {
            case '.php':
            case '.phtml':
            case '.php3':
            case '.php4':
            case '.php5':
            case '.php7':
            case '.phps':
            case '.phar':
            case '.asp':
            case '.aspx':
            case '.ashx':
            case '.asmx':
            case '.jsp':
            case '.jspx':
            case '.cfm':
                return 'webshells';
            case '.exe':
            case '.dll':
            case '.sys':
            case '.scr':
            case '.com':
            case '.msi':
            case '.cpl':
            case '.elf':
            case '.so':
            case '.apk':
            case '.jar':
                return 'malware';
            case '.ps1':
            case '.psm1':
            case '.bat':
            case '.cmd':
            case '.vbs':
            case '.vbe':
            case '.js':
            case '.jse':
            case '.wsf':
            case '.wsh':
            case '.hta':
            case '.sh':
            case '.py':
            case '.pl':
            case '.rb':
                return 'mixed';
            default:
                return 'other';
        }
    }

    protected function categoryLabel($category)
    {
        switch ($category) {
            case 'webshells':
                return 'Webshell';
            case 'ransomware':
                return 'Ransomware';
            case 'trojan':
                return 'Trojan';
            case 'malware':
                return 'Malware';
            case 'mixed':
                return 'Mixed';
            default:
                return 'Other';
        }
    }

    protected function signatureName($category, SsdeepCandidate $candidate)
    {
        $label = $this->categoryLabel($category);
        $base = pathinfo((string) ($candidate->file_name ?: 'sample'), PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) $base);
        $base = trim($base, '._-');
        if ($base === '') {
            $base = 'sample';
        }
        if (strlen($base) > 48) {
            $base = substr($base, 0, 48);
        }
        return $label.'.'.$base;
    }

    /**
     * Latest active pack for category (prefer linked to site). Used only as read-base for signatures.
     */
    protected function findLatestPack($category, $siteId)
    {
        if (!Schema::hasColumn('ssdeep_file', 'category')) {
            return null;
        }

        $linkedIds = SsdeepFileSite::where('site_id', $siteId)
            ->where('status', 'Y')
            ->pluck('ssdeep_file_id')
            ->toArray();
        if (!empty($linkedIds)) {
            $pack = SsdeepFile::whereIn('id', $linkedIds)
                ->where('status', 'Y')
                ->where('category', $category)
                ->orderBy('id', 'desc')
                ->first();
            if ($pack) {
                return $pack;
            }
        }

        return $this->findLatestCategoryPack($category);
    }

    /**
     * Single living auto pack per category (newest active row).
     */
    protected function findLatestCategoryPack($category)
    {
        if (!Schema::hasColumn('ssdeep_file', 'category')) {
            return null;
        }
        return SsdeepFile::where('status', 'Y')
            ->where('category', $category)
            ->orderBy('id', 'desc')
            ->first();
    }

    protected function createNewPackRow($category)
    {
        $stamp = date('Y.m.d.His');
        $pack = new SsdeepFile();
        $pack->version = $category.'-'.$stamp;
        $pack->path = '';
        $pack->file_name = 'ssdeep_'.$category.'_auto.zip';
        if (Schema::hasColumn('ssdeep_file', 'category')) {
            $pack->title = $this->categoryLabel($category).' fuzzy hashes (auto)';
            $pack->category = $category;
            $pack->description = 'Auto-promoted from agent detections (new file)';
        }
        $pack->format = 'json';
        $pack->sha256 = null;
        $pack->size_bytes = 0;
        $pack->signature_count = 0;
        $pack->status = 'Y';
        if (Schema::hasColumn('ssdeep_file', 'source')) {
            $pack->source = 'auto';
        }
        $pack->save();

        return $pack;
    }

    /**
     * Ensure ssdeep_settings exists (lazy create — no artisan migrate required).
     */
    public function ensureSsdeepSettingsTable()
    {
        if (!Schema::hasTable('ssdeep_settings')) {
            Schema::create('ssdeep_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key', 64)->unique();
                $table->string('value', 255)->nullable();
                $table->timestamps();
            });
        }

        $exists = DB::table('ssdeep_settings')->where('key', 'auto_distribute_sites')->exists();
        if (!$exists) {
            $now = date('Y-m-d H:i:s');
            DB::table('ssdeep_settings')->insert([
                'key' => 'auto_distribute_sites',
                'value' => 'N',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Optional: ensure source column on packs for Auto/Master label.
        if (Schema::hasTable('ssdeep_file') && !Schema::hasColumn('ssdeep_file', 'source')) {
            Schema::table('ssdeep_file', function (Blueprint $table) {
                $table->string('source', 16)->default('master')->after('status');
            });
        }

        return true;
    }

    /**
     * Global Master Ssdeep toggle: new auto packs fan out to all sites.
     */
    public function isGlobalAutoDistributeEnabled()
    {
        try {
            $this->ensureSsdeepSettingsTable();
        } catch (\Exception $e) {
            Log::warning('ssdeep_settings ensure failed: '.$e->getMessage());
            return false;
        }
        $v = DB::table('ssdeep_settings')->where('key', 'auto_distribute_sites')->value('value');
        return strtoupper(trim((string) $v)) === 'Y';
    }

    /**
     * @param bool $enabled
     */
    public function setGlobalAutoDistribute($enabled)
    {
        try {
            $this->ensureSsdeepSettingsTable();
        } catch (\Exception $e) {
            Log::warning('ssdeep_settings ensure failed: '.$e->getMessage());
            return false;
        }
        $value = $enabled ? 'Y' : 'N';
        $row = DB::table('ssdeep_settings')->where('key', 'auto_distribute_sites')->first();
        $now = date('Y-m-d H:i:s');
        if ($row) {
            DB::table('ssdeep_settings')->where('key', 'auto_distribute_sites')->update([
                'value' => $value,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('ssdeep_settings')->insert([
                'key' => 'auto_distribute_sites',
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        return true;
    }

    /**
     * Assign pack to every site and requeue agent downloads.
     */
    public function assignPackToAllSites($packId, $category = null)
    {
        $siteIds = DB::table('site')->pluck('id')->toArray();
        if (empty($siteIds)) {
            return;
        }

        if ($category && Schema::hasColumn('ssdeep_file', 'category')) {
            $oldIds = SsdeepFile::where('category', $category)
                ->where('id', '!=', $packId)
                ->pluck('id')
                ->toArray();
            if (!empty($oldIds)) {
                SsdeepFileSite::whereIn('site_id', $siteIds)
                    ->whereIn('ssdeep_file_id', $oldIds)
                    ->update(['status' => 'N']);
            }
        }

        foreach ($siteIds as $siteId) {
            $siteId = (int) $siteId;
            $row = SsdeepFileSite::where('site_id', $siteId)
                ->where('ssdeep_file_id', $packId)
                ->first();
            if (!$row) {
                $row = new SsdeepFileSite();
                $row->site_id = $siteId;
                $row->ssdeep_file_id = $packId;
            }
            $row->status = 'Y';
            $row->transaction_download_client = 1;
            $row->save();

            $this->requeueAgents($packId, $siteId);
        }
    }

    protected function loadPackEntries(SsdeepFile $pack)
    {
        $relative = $this->relativePublicPath($pack->path);
        if ($relative === '') {
            return [];
        }
        $full = public_path($relative);
        if (!is_file($full)) {
            $jsonSide = preg_replace('/\.zip$/i', '.json', $full);
            if (is_file($jsonSide)) {
                $data = json_decode(@file_get_contents($jsonSide), true);
                return is_array($data) ? $data : [];
            }
            return [];
        }

        $lower = strtolower($full);
        if (substr($lower, -5) === '.json') {
            $data = json_decode(@file_get_contents($full), true);
            return is_array($data) ? $data : [];
        }

        if (substr($lower, -4) === '.zip' && class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($full) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if ($name && preg_match('/\.json$/i', $name)) {
                        $raw = $zip->getFromIndex($i);
                        $zip->close();
                        $data = json_decode($raw, true);
                        return is_array($data) ? $data : [];
                    }
                }
                $zip->close();
            }
        }

        return [];
    }

    protected function writePackFiles(SsdeepFile $pack, $category, array $entries)
    {
        $dir = public_path('ssdeep_files');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }

        // Unique filename per promote (new file for each unique hash).
        $stamp = date('Ymd_His').'_'.substr((string) microtime(true), -4);
        $base = 'ssdeep_'.$category.'_auto_'.$stamp;
        $jsonName = $base.'.json';
        $zipName = $base.'.zip';
        $jsonPath = $dir.DIRECTORY_SEPARATOR.$jsonName;
        $zipPath = $dir.DIRECTORY_SEPARATOR.$zipName;

        $json = json_encode(array_values($entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($jsonPath, $json);

        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive extension required for ssdeep auto packs');
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create zip '.$zipPath);
        }
        $zip->addFile($jsonPath, $jsonName);
        $zip->close();

        $publicUrl = url('/ssdeep_files/'.$zipName);
        $version = $category.'-'.date('Y.m.d.His');

        $pack->version = $version;
        $pack->path = $publicUrl;
        $pack->file_name = $zipName;
        $pack->format = 'json';
        $pack->sha256 = hash_file('sha256', $zipPath);
        $pack->size_bytes = @filesize($zipPath) ?: 0;
        $pack->signature_count = count($entries);
        $pack->status = 'Y';
        if (Schema::hasColumn('ssdeep_file', 'category')) {
            $pack->category = $category;
            $pack->title = $this->categoryLabel($category).' fuzzy hashes (auto)';
            $pack->description = 'Auto-promoted new pack file '.$stamp;
        }
        if (Schema::hasColumn('ssdeep_file', 'source')) {
            $pack->source = 'auto';
        }
        $pack->save();
    }

    protected function requeueLinkedSites($packId)
    {
        $siteIds = SsdeepFileSite::where('ssdeep_file_id', $packId)
            ->where('status', 'Y')
            ->pluck('site_id')
            ->toArray();
        foreach ($siteIds as $siteId) {
            $row = SsdeepFileSite::where('ssdeep_file_id', $packId)
                ->where('site_id', $siteId)
                ->first();
            if ($row) {
                $row->transaction_download_client = 1;
                $row->save();
            }
            $this->requeueAgents($packId, (int) $siteId);
        }
    }

    /**
     * Link site to the new pack; unlink older packs of the same category for this site
     * (old pack files/rows remain for history, status unchanged).
     */
    protected function assignSiteToNewPackOnly($newPackId, $siteId, $category)
    {
        if (Schema::hasColumn('ssdeep_file', 'category')) {
            $oldIds = SsdeepFile::where('category', $category)
                ->where('id', '!=', $newPackId)
                ->pluck('id')
                ->toArray();
            if (!empty($oldIds)) {
                SsdeepFileSite::where('site_id', $siteId)
                    ->whereIn('ssdeep_file_id', $oldIds)
                    ->update(['status' => 'N']);
            }
        }

        $row = SsdeepFileSite::where('site_id', $siteId)
            ->where('ssdeep_file_id', $newPackId)
            ->first();
        if (!$row) {
            $row = new SsdeepFileSite();
            $row->site_id = $siteId;
            $row->ssdeep_file_id = $newPackId;
        }
        $row->status = 'Y';
        $row->transaction_download_client = 1;
        $row->save();
    }

    protected function requeueAgents($packId, $siteId)
    {
        SsdeepFileSiteAgentDownload::where('site_id', $siteId)
            ->where('ssdeep_file_id', $packId)
            ->update(['transaction_download_client' => 0]);
    }

    /**
     * Permanently remove duplicate Master Ssdeep packs (DB rows + files):
     * - Auto packs (any status): keep 1 per category
     * - Identical sha256: keep 1 (prefer master)
     * Also purges duplicate Ssdeep Candidates.
     *
     * @return array
     */
    public function cleanupDuplicatePacks()
    {
        $stats = [
            'kept' => 0,
            'deleted' => 0,
            'files_deleted' => 0,
            'sites_moved' => 0,
            'orphans_deleted' => 0,
            'candidates_deleted' => 0,
        ];

        if (!Schema::hasTable('ssdeep_file')) {
            return $stats + ['message' => 'ssdeep_file table missing'];
        }

        // Include inactive rows from previous soft-delete runs.
        $all = SsdeepFile::orderBy('id', 'desc')->get();
        $keepIds = [];
        $deleteMap = []; // loserId => winnerId (0 = no migrate)

        // 1) Auto packs → one survivor per category (prefer active + most signatures)
        $autoByCat = [];
        foreach ($all as $pack) {
            if (!$this->isAutoPack($pack)) {
                continue;
            }
            $cat = strtolower(trim((string) $pack->category));
            if ($cat === '') {
                $cat = '_uncategorized';
            }
            if (!isset($autoByCat[$cat])) {
                $autoByCat[$cat] = [];
            }
            $autoByCat[$cat][] = $pack;
        }

        foreach ($autoByCat as $packs) {
            usort($packs, function ($a, $b) {
                $ya = $a->status === 'Y' ? 1 : 0;
                $yb = $b->status === 'Y' ? 1 : 0;
                if ($ya !== $yb) {
                    return $yb - $ya;
                }
                $sa = (int) $a->signature_count;
                $sb = (int) $b->signature_count;
                if ($sa !== $sb) {
                    return $sb - $sa;
                }
                return (int) $b->id - (int) $a->id;
            });
            $winner = $packs[0];
            $keepIds[(int) $winner->id] = true;
            // Ensure keeper is active
            if ($winner->status !== 'Y') {
                $winner->status = 'Y';
                $winner->save();
            }
            for ($i = 1; $i < count($packs); $i++) {
                $deleteMap[(int) $packs[$i]->id] = (int) $winner->id;
            }
        }

        // Non-auto packs stay unless sha256-collapsed below.
        foreach ($all as $pack) {
            if ($this->isAutoPack($pack)) {
                continue;
            }
            $keepIds[(int) $pack->id] = true;
        }

        // 2) Identical sha256 among remaining keepers → hard-delete losers
        $bySha = [];
        foreach ($all as $pack) {
            $id = (int) $pack->id;
            if (isset($deleteMap[$id])) {
                continue;
            }
            $sha = strtolower(trim((string) $pack->sha256));
            if ($sha === '') {
                continue;
            }
            if (!isset($bySha[$sha])) {
                $bySha[$sha] = [];
            }
            $bySha[$sha][] = $pack;
        }
        foreach ($bySha as $packs) {
            if (count($packs) < 2) {
                continue;
            }
            usort($packs, function ($a, $b) {
                $sa = method_exists($a, 'packSource') ? $a->packSource() : 'master';
                $sb = method_exists($b, 'packSource') ? $b->packSource() : 'master';
                // Prefer master over auto
                if ($sa !== $sb) {
                    return $sa === 'master' ? -1 : 1;
                }
                $ya = $a->status === 'Y' ? 1 : 0;
                $yb = $b->status === 'Y' ? 1 : 0;
                if ($ya !== $yb) {
                    return $yb - $ya;
                }
                $ca = (int) $a->signature_count;
                $cb = (int) $b->signature_count;
                if ($ca !== $cb) {
                    return $cb - $ca;
                }
                return (int) $b->id - (int) $a->id;
            });
            $winner = $packs[0];
            $keepIds[(int) $winner->id] = true;
            for ($i = 1; $i < count($packs); $i++) {
                $loserId = (int) $packs[$i]->id;
                unset($keepIds[$loserId]);
                $deleteMap[$loserId] = (int) $winner->id;
            }
        }

        // 3) Migrate sites then HARD DELETE losers (DB + files)
        foreach ($deleteMap as $loserId => $winnerId) {
            if ($winnerId > 0) {
                $stats['sites_moved'] += $this->migratePackSites($loserId, $winnerId);
            }
            $stats['files_deleted'] += $this->hardDeletePack((int) $loserId, $keepIds);
            $stats['deleted']++;
        }

        $stats['kept'] = count($keepIds);
        $stats['orphans_deleted'] = $this->deleteOrphanAutoStampFiles($keepIds);
        $stats['candidates_deleted'] = $this->cleanupDuplicateCandidates();

        $stats['message'] = sprintf(
            'ลบถาวรแล้ว: pack %d รายการ, ไฟล์ %d, orphan %d, candidate ซ้ำ %d, ย้าย site %d (เหลือ %d)',
            $stats['deleted'],
            $stats['files_deleted'],
            $stats['orphans_deleted'],
            $stats['candidates_deleted'],
            $stats['sites_moved'],
            $stats['kept']
        );

        return $stats;
    }

    protected function isAutoPack($pack)
    {
        if (method_exists($pack, 'packSource')) {
            return $pack->packSource() === 'auto';
        }
        $blob = strtolower(trim(
            (string) @$pack->file_name.' '.
            (string) @$pack->title.' '.
            (string) @$pack->description.' '.
            (string) @$pack->source
        ));
        return strpos($blob, '_auto') !== false
            || strpos($blob, '(auto)') !== false
            || strpos($blob, 'auto-promoted') !== false
            || $blob === 'auto'
            || strpos($blob, ' auto ') !== false;
    }

    /**
     * Hard-delete pack row + site links + downloads + disk files (if unreferenced).
     */
    protected function hardDeletePack($packId, array $keepIds)
    {
        $pack = SsdeepFile::find((int) $packId);
        if (!$pack) {
            return 0;
        }

        $relative = $this->relativePublicPath($pack->path);
        $fileName = $pack->file_name;

        SsdeepFileSiteAgentDownload::where('ssdeep_file_id', $packId)->delete();
        SsdeepFileSite::where('ssdeep_file_id', $packId)->delete();
        SsdeepFile::where('id', $packId)->delete();

        $deleted = 0;
        if ($relative !== '') {
            $keepIdList = array_keys($keepIds);
            $stillUsed = false;
            if (!empty($keepIdList)) {
                foreach (SsdeepFile::whereIn('id', $keepIdList)->get() as $other) {
                    if ($this->relativePublicPath($other->path) === $relative) {
                        $stillUsed = true;
                        break;
                    }
                }
            }
            if (!$stillUsed) {
                $full = public_path($relative);
                if (is_file($full)) {
                    @unlink($full);
                    $deleted++;
                }
                $jsonSide = preg_replace('/\.zip$/i', '.json', $full);
                if ($jsonSide && $jsonSide !== $full && is_file($jsonSide)) {
                    @unlink($jsonSide);
                    $deleted++;
                }
            }
        }

        // Also try file_name under ssdeep_files if path was empty/broken
        if ($deleted === 0 && $fileName) {
            $base = basename((string) $fileName);
            if ($base !== '' && preg_match('/_auto/i', $base)) {
                $full = public_path('ssdeep_files/'.$base);
                $keepIdList = array_keys($keepIds);
                $stillUsed = false;
                if (!empty($keepIdList)) {
                    foreach (SsdeepFile::whereIn('id', $keepIdList)->get() as $other) {
                        if (basename((string) $other->file_name) === $base
                            || basename($this->relativePublicPath($other->path)) === $base) {
                            $stillUsed = true;
                            break;
                        }
                    }
                }
                if (!$stillUsed && is_file($full)) {
                    @unlink($full);
                    $deleted++;
                    $jsonSide = preg_replace('/\.zip$/i', '.json', $full);
                    if ($jsonSide && is_file($jsonSide)) {
                        @unlink($jsonSide);
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    protected function migratePackSites($fromPackId, $toPackId)
    {
        $fromPackId = (int) $fromPackId;
        $toPackId = (int) $toPackId;
        if ($fromPackId <= 0 || $toPackId <= 0 || $fromPackId === $toPackId) {
            return 0;
        }

        // Include previously deactivated links so sites are not lost.
        $siteIds = SsdeepFileSite::where('ssdeep_file_id', $fromPackId)
            ->pluck('site_id')
            ->unique()
            ->toArray();
        $moved = 0;
        foreach ($siteIds as $siteId) {
            $siteId = (int) $siteId;
            $row = SsdeepFileSite::where('ssdeep_file_id', $toPackId)
                ->where('site_id', $siteId)
                ->first();
            if (!$row) {
                $row = new SsdeepFileSite();
                $row->ssdeep_file_id = $toPackId;
                $row->site_id = $siteId;
            }
            $row->status = 'Y';
            $row->transaction_download_client = 1;
            $row->save();
            $this->requeueAgents($toPackId, $siteId);
            $moved++;
        }
        return $moved;
    }

    /**
     * Delete pack file(s) on disk if no remaining active pack points at the same path.
     * @deprecated use hardDeletePack
     */
    protected function deletePackFilesIfUnreferenced($packId, array $keepIds)
    {
        return $this->hardDeletePack($packId, $keepIds);
    }

    /**
     * Remove leftover uniquely-named auto packs: ssdeep_{cat}_auto_{stamp}.zip/.json
     * Keep stable ssdeep_{cat}_auto.zip/.json and any path still referenced by active packs.
     */
    protected function deleteOrphanAutoStampFiles(array $keepIds)
    {
        $dir = public_path('ssdeep_files');
        if (!is_dir($dir)) {
            return 0;
        }

        $referenced = [];
        $keepIdList = array_keys($keepIds);
        if (!empty($keepIdList)) {
            foreach (SsdeepFile::whereIn('id', $keepIdList)->where('status', 'Y')->get() as $pack) {
                $rel = $this->relativePublicPath($pack->path);
                if ($rel !== '') {
                    $referenced[strtolower(basename($rel))] = true;
                    $json = preg_replace('/\.zip$/i', '.json', basename($rel));
                    if ($json) {
                        $referenced[strtolower($json)] = true;
                    }
                }
            }
        }

        $deleted = 0;
        foreach (scandir($dir) as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            // Timestamped auto artifacts only (not stable *_auto.zip)
            if (!preg_match('/^ssdeep_[A-Za-z0-9_-]+_auto_\d.+\.(zip|json)$/i', $name)) {
                continue;
            }
            if (isset($referenced[strtolower($name)])) {
                continue;
            }
            $full = $dir.DIRECTORY_SEPARATOR.$name;
            if (is_file($full)) {
                @unlink($full);
                $deleted++;
            }
        }
        return $deleted;
    }

    protected function relativePublicPath($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#ssdeep_files/(.+)$#i', $path, $m)) {
            return 'ssdeep_files/'.$m[1];
        }
        if (strpos($path, 'ssdeep_files/') === 0) {
            return $path;
        }
        return '';
    }
}
