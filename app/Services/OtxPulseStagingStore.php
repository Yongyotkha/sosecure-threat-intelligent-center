<?php

namespace App\Services;

/**
 * Shared staging store for OTX pulse + indicator pipelines.
 * Kind: 'pulses' | 'indicators'
 */
class OtxPulseStagingStore
{
    const AUDIT_NOTE = 'api_total = OTX catalog size (not this run). save_intended = planned this run. save_actual = written to Mongo.';

    public static function root($kind = 'pulses')
    {
        return storage_path('app/otx/' . $kind);
    }

    public static function runPath($runId, $kind = 'pulses')
    {
        return self::root($kind) . DIRECTORY_SEPARATOR . $runId;
    }

    public static function pulsePath($runId, $pulseId)
    {
        return self::runPath($runId, 'pulses') . DIRECTORY_SEPARATOR . 'pulses' . DIRECTORY_SEPARATOR . $pulseId;
    }

    public static function indicatorPath($runId, $indicatorId)
    {
        return self::runPath($runId, 'indicators') . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $indicatorId;
    }

    public static function createRun($query = 'modified:<12h', $kind = 'pulses', $limit = null)
    {
        $runId = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 8);
        $path = self::runPath($runId, $kind);
        if (!is_dir($path . '/list')) {
            mkdir($path . '/list', 0755, true);
        }
        $sub = $kind === 'indicators' ? 'items' : 'pulses';
        if (!is_dir($path . '/' . $sub)) {
            mkdir($path . '/' . $sub, 0755, true);
        }

        $now = date('c');
        $manifest = [
            'run_id' => $runId,
            'kind' => $kind,
            'query' => $query,
            'limit' => $limit,
            'status' => 'fetching',
            'created_at' => $now,
            'updated_at' => $now,
            'started_at' => $now,
            'last_started_at' => $now,
            'resumed_at' => null,
            'resume_count' => 0,
            'fetch_finished_at' => null,
            'import_started_at' => null,
            'import_finished_at' => null,
            'finished_at' => null,
            'complete' => false,
            'api_total' => 0,
            'api_total_pulses' => 0,
            'pages_fetched' => 0,
            'pulses_fetched' => 0,
            'pulses_skipped' => 0,
            'pulses_failed' => 0,
            'items_fetched' => 0,
            'items_skipped' => 0,
            'items_failed' => 0,
            'pulses' => [],
            'items' => [],
            'checkpoint' => [
                'list_next_url' => null,
                'list_page' => 0,
                'list_done' => false,
            ],
            'import_status' => 'pending',
        ];
        self::writeJson($path . '/manifest.json', $manifest);

        return $runId;
    }

    public static function writeJson($path, $data)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            $path,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    public static function readJson($path)
    {
        if (!file_exists($path)) {
            return null;
        }
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        return json_decode($raw, true);
    }

    public static function loadManifest($runId, $kind = 'pulses')
    {
        return self::readJson(self::runPath($runId, $kind) . '/manifest.json');
    }

    public static function saveManifest($runId, array $manifest, $kind = null)
    {
        $kind = $kind ?: ($manifest['kind'] ?? 'pulses');
        $manifest['kind'] = $kind;
        $manifest['updated_at'] = date('c');
        self::writeJson(self::runPath($runId, $kind) . '/manifest.json', $manifest);
    }

    public static function latestRunId($preferIncomplete = true, $kind = 'pulses')
    {
        $root = self::root($kind);
        if (!is_dir($root)) {
            return null;
        }
        $dirs = array_values(array_filter(scandir($root), function ($d) use ($root) {
            return $d !== '.' && $d !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $d);
        }));
        rsort($dirs);

        if (!$preferIncomplete) {
            return $dirs[0] ?? null;
        }

        foreach ($dirs as $runId) {
            $manifest = self::loadManifest($runId, $kind);
            if (!$manifest) {
                continue;
            }
            if (in_array($manifest['status'] ?? '', ['fetching', 'partial'], true)) {
                return $runId;
            }
        }

        return $dirs[0] ?? null;
    }

    public static function latestFetchedRunId($kind = 'pulses')
    {
        $root = self::root($kind);
        if (!is_dir($root)) {
            return null;
        }
        $dirs = array_values(array_filter(scandir($root), function ($d) use ($root) {
            return $d !== '.' && $d !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $d);
        }));
        rsort($dirs);

        foreach ($dirs as $runId) {
            $manifest = self::loadManifest($runId, $kind);
            if (!$manifest) {
                continue;
            }
            $status = $manifest['status'] ?? '';
            $import = $manifest['import_status'] ?? 'pending';
            if (in_array($status, ['fetched', 'partial'], true) && $import !== 'done') {
                return $runId;
            }
        }

        foreach ($dirs as $runId) {
            $manifest = self::loadManifest($runId, $kind);
            if ($manifest && in_array($manifest['status'] ?? '', ['fetched', 'partial'], true)) {
                return $runId;
            }
        }

        return null;
    }

    public static function deleteRun($runId, $kind = 'pulses')
    {
        $path = self::runPath($runId, $kind);
        if (!is_dir($path)) {
            return false;
        }
        self::deleteDirectory($path);
        return !is_dir($path);
    }

    /**
     * Incomplete = fetch not done, or fetch done but import not done.
     * Oldest first so leftover data is finished before a new window starts.
     *
     * @param string $kind pulses|indicators
     * @param string|null $queue null = main pulse runs (skip heavy), 'heavy' = heavy only
     */
    public static function findIncompleteRunId($kind = 'pulses', $queue = null)
    {
        $root = self::root($kind);
        if (!is_dir($root)) {
            return null;
        }
        $dirs = array_values(array_filter(scandir($root), function ($d) use ($root) {
            return $d !== '.' && $d !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $d);
        }));
        sort($dirs);

        foreach ($dirs as $runId) {
            $manifest = self::loadManifest($runId, $kind);
            if (!$manifest) {
                continue;
            }
            $runQueue = $manifest['queue'] ?? '';
            if ($queue === 'heavy') {
                if ($runQueue !== 'heavy') {
                    continue;
                }
            } elseif ($kind === 'pulses' && $runQueue === 'heavy') {
                continue;
            }
            if (self::isIncomplete($manifest)) {
                return $runId;
            }
        }

        return null;
    }

    public static function isIncomplete(array $manifest)
    {
        $status = $manifest['status'] ?? '';
        $import = $manifest['import_status'] ?? 'pending';
        $kind = $manifest['kind'] ?? 'pulses';
        $items = $kind === 'indicators' ? ($manifest['items'] ?? []) : ($manifest['pulses'] ?? []);

        $pending = 0;
        $fetched = 0;
        foreach ($items as $meta) {
            $st = $meta['status'] ?? 'pending';
            if ($st === 'pending' || $st === '') {
                $pending++;
            }
            if ($st === 'fetched') {
                $fetched++;
            }
        }

        $listDone = !empty($manifest['checkpoint']['list_done']);
        if (!$listDone && in_array($status, ['fetching', 'partial', ''], true)) {
            return true;
        }
        if ($pending > 0) {
            return true;
        }
        if ($fetched > 0 && $import !== 'done') {
            return true;
        }

        return false;
    }

    public static function stampSessionStart(array &$manifest, $isResume = false)
    {
        $now = date('c');
        if (empty($manifest['started_at'])) {
            $manifest['started_at'] = $now;
        }
        $manifest['last_started_at'] = $now;
        $manifest['complete'] = false;
        $manifest['finished_at'] = null;
        if ($isResume) {
            $manifest['resumed_at'] = $now;
            $manifest['resume_count'] = (int) ($manifest['resume_count'] ?? 0) + 1;
        }
    }

    public static function stampFetchFinish(array &$manifest, $complete)
    {
        $manifest['fetch_finished_at'] = date('c');
        $manifest['fetch_complete'] = (bool) $complete;
    }

    public static function stampImportStart(array &$manifest)
    {
        if (empty($manifest['import_started_at'])) {
            $manifest['import_started_at'] = date('c');
        }
    }

    public static function stampImportFinish(array &$manifest, $complete)
    {
        $manifest['import_finished_at'] = date('c');
        $manifest['import_complete'] = (bool) $complete;
    }

    public static function stampFinish(array &$manifest, $complete)
    {
        $now = date('c');
        $manifest['finished_at'] = $now;
        $manifest['complete'] = (bool) $complete;
        if ($complete) {
            $manifest['completed_at'] = $now;
        }
    }

    public static function elapsed($start, $end = null)
    {
        $t0 = $start ? strtotime($start) : false;
        $t1 = $end ? strtotime($end) : time();
        if ($t0 === false || $t1 === false) {
            return '-';
        }
        $sec = max(0, $t1 - $t0);
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        $s = $sec % 60;
        if ($h > 0) {
            return sprintf('%dh %dm %ds', $h, $m, $s);
        }
        if ($m > 0) {
            return sprintf('%dm %ds', $m, $s);
        }
        return sprintf('%ds', $s);
    }

    /**
     * Non-blocking lock so two cron ticks cannot corrupt the same staging tree.
     *
     * @return resource|false
     */
    public static function acquireLock($lockName)
    {
        $dir = storage_path('app/otx');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fp = fopen($dir . DIRECTORY_SEPARATOR . $lockName . '.lock', 'c');
        if (!$fp) {
            return false;
        }
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return false;
        }
        ftruncate($fp, 0);
        fwrite($fp, (string) getmypid() . "\n" . date('c'));
        fflush($fp);
        return $fp;
    }

    public static function releaseLock($fp)
    {
        if (is_resource($fp)) {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Fetch-phase counts from staging manifest (OTX catalog vs this run).
     */
    public static function fetchAudit(array $manifest)
    {
        $s = $manifest['stats'] ?? [];
        return [
            'api_total' => (int) ($s['api_total'] ?? $s['apiTotal'] ?? $manifest['api_total'] ?? $manifest['api_total_pulses'] ?? 0),
            'limit' => array_key_exists('limit', $manifest) ? $manifest['limit'] : null,
            'intended' => (int) ($s['expected'] ?? 0),
            'fetched' => (int) ($s['fetched'] ?? 0),
            'skipped' => (int) ($s['skipped'] ?? 0),
            'failed' => (int) ($s['failed'] ?? 0),
        ];
    }

    /**
     * Mongo stamp audit block — same shape on pulse + indicator stamps.
     *
     * @param array $import [api_total, limit, intended, saved, skipped, failed]
     */
    public static function mongoAudit(array $manifest, array $import, array $extra = [])
    {
        $audit = [
            'note' => self::AUDIT_NOTE,
            'fetch' => self::fetchAudit($manifest),
            'import' => [
                'api_total' => (int) ($import['api_total'] ?? 0),
                'limit' => array_key_exists('limit', $import) ? $import['limit'] : ($manifest['limit'] ?? null),
                'save_intended' => (int) ($import['intended'] ?? 0),
                'save_actual' => (int) ($import['saved'] ?? 0),
                'skipped' => (int) ($import['skipped'] ?? 0),
                'failed' => (int) ($import['failed'] ?? 0),
            ],
        ];
        foreach ($extra as $key => $value) {
            $audit[$key] = $value;
        }
        return $audit;
    }

    protected static function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($full)) {
                self::deleteDirectory($full);
            } else {
                @unlink($full);
            }
        }
        @rmdir($dir);
    }
}
