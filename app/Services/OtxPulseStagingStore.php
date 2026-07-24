<?php

namespace App\Services;

/**
 * Shared staging store for OTX pulse + indicator pipelines.
 * Kind: 'pulses' | 'indicators'
 */
class OtxPulseStagingStore
{
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

    public static function createRun($query = 'modified:<12h', $kind = 'pulses')
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

        $manifest = [
            'run_id' => $runId,
            'kind' => $kind,
            'query' => $query,
            'status' => 'fetching',
            'created_at' => date('c'),
            'updated_at' => date('c'),
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
