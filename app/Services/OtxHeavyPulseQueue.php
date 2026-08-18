<?php

namespace App\Services;

/**
 * Persistent queue for OTX pulses skipped by main fetch due to high indicator_count.
 * Processed later by app:OTXMDFetchPulseHeavy (slow lane).
 */
class OtxHeavyPulseQueue
{
    public static function root()
    {
        return storage_path('app/otx/heavy-queue');
    }

    public static function path($pulseId)
    {
        return self::root() . DIRECTORY_SEPARATOR . $pulseId . '.json';
    }

    public static function enqueue(array $listItem, $sourceRunId = null, $skipReason = null)
    {
        $pulseId = $listItem['id'] ?? null;
        if (!$pulseId) {
            return false;
        }

        $existing = self::get($pulseId);
        // Do not re-queue if already staged/imported or actively processing.
        if ($existing && in_array($existing['status'] ?? '', ['pending', 'processing', 'staged', 'imported'], true)) {
            return false;
        }

        $entry = [
            'pulse_id' => $pulseId,
            'status' => 'pending',
            'indicator_count' => (int) ($listItem['indicator_count'] ?? 0),
            'name' => $listItem['name'] ?? '',
            'modified' => $listItem['modified'] ?? null,
            'created' => $listItem['created'] ?? null,
            'skip_reason' => $skipReason,
            'source_run_id' => $sourceRunId,
            'staging_run_id' => null,
            'enqueued_at' => date('c'),
            'updated_at' => date('c'),
            'list_item' => $listItem,
        ];

        OtxPulseStagingStore::writeJson(self::path($pulseId), $entry);
        return true;
    }

    public static function get($pulseId)
    {
        return OtxPulseStagingStore::readJson(self::path($pulseId));
    }

    public static function save($pulseId, array $entry)
    {
        $entry['updated_at'] = date('c');
        OtxPulseStagingStore::writeJson(self::path($pulseId), $entry);
    }

    /**
     * @return array<int, array>
     */
    public static function listByStatus($status = 'pending')
    {
        $root = self::root();
        if (!is_dir($root)) {
            return [];
        }

        $out = [];
        foreach (scandir($root) as $file) {
            if ($file === '.' || $file === '..' || substr($file, -5) !== '.json') {
                continue;
            }
            $entry = OtxPulseStagingStore::readJson($root . DIRECTORY_SEPARATOR . $file);
            if (!$entry) {
                continue;
            }
            if (($entry['status'] ?? null) === $status) {
                $out[] = $entry;
            }
        }

        usort($out, function ($a, $b) {
            return strcmp($b['enqueued_at'] ?? '', $a['enqueued_at'] ?? '');
        });

        return $out;
    }

    public static function countByStatus($status = 'pending')
    {
        return count(self::listByStatus($status));
    }

    public static function markProcessing($pulseId, $stagingRunId)
    {
        $entry = self::get($pulseId);
        if (!$entry) {
            return;
        }
        $entry['status'] = 'processing';
        $entry['staging_run_id'] = $stagingRunId;
        self::save($pulseId, $entry);
    }

    public static function markStaged($pulseId, $stagingRunId)
    {
        $entry = self::get($pulseId);
        if (!$entry) {
            return;
        }
        $entry['status'] = 'staged';
        $entry['staging_run_id'] = $stagingRunId;
        self::save($pulseId, $entry);
    }

    public static function markFailed($pulseId, $error = null)
    {
        $entry = self::get($pulseId);
        if (!$entry) {
            return;
        }
        $entry['status'] = 'failed';
        if ($error) {
            $entry['error'] = $error;
        }
        self::save($pulseId, $entry);
    }

    public static function markImported($pulseId)
    {
        $entry = self::get($pulseId);
        if (!$entry) {
            return;
        }
        $entry['status'] = 'imported';
        self::save($pulseId, $entry);
    }

    public static function markImportedByRun($stagingRunId)
    {
        $root = self::root();
        if (!is_dir($root)) {
            return 0;
        }
        $n = 0;
        foreach (scandir($root) as $file) {
            if ($file === '.' || $file === '..' || substr($file, -5) !== '.json') {
                continue;
            }
            $entry = OtxPulseStagingStore::readJson($root . DIRECTORY_SEPARATOR . $file);
            if (!$entry) {
                continue;
            }
            // Only successfully staged rows — never promote failed/processing.
            if (($entry['staging_run_id'] ?? null) === $stagingRunId
                && ($entry['status'] ?? '') === 'staged') {
                $entry['status'] = 'imported';
                self::save($entry['pulse_id'], $entry);
                $n++;
            }
        }
        return $n;
    }

    /**
     * Recover queue rows left behind when a process died.
     * Leave processing/staged alone if their staging run still exists and is incomplete
     * (the leftover run will finish them).
     *
     * @return int number of rows reset to pending
     */
    public static function reclaimStale($maxAgeMinutes = 30)
    {
        $root = self::root();
        if (!is_dir($root)) {
            return 0;
        }
        $cutoff = time() - ($maxAgeMinutes * 60);
        $n = 0;
        foreach (scandir($root) as $file) {
            if ($file === '.' || $file === '..' || substr($file, -5) !== '.json') {
                continue;
            }
            $entry = OtxPulseStagingStore::readJson($root . DIRECTORY_SEPARATOR . $file);
            if (!$entry) {
                continue;
            }
            $status = $entry['status'] ?? '';
            if (!in_array($status, ['processing', 'staged'], true)) {
                continue;
            }
            $runId = $entry['staging_run_id'] ?? null;
            $staging = $runId ? OtxPulseStagingStore::loadManifest($runId) : null;
            $stagingAlive = $staging && OtxPulseStagingStore::isIncomplete($staging);

            if ($stagingAlive) {
                continue;
            }

            $updated = strtotime($entry['updated_at'] ?? $entry['enqueued_at'] ?? 'now');
            $stale = $updated === false || $updated < $cutoff;
            $orphan = !$staging;
            if (!$stale && !$orphan) {
                continue;
            }

            $entry['status'] = 'pending';
            $entry['reclaimed_from'] = $status;
            $entry['reclaimed_at'] = date('c');
            self::save($entry['pulse_id'], $entry);
            $n++;
        }
        return $n;
    }

    /**
     * Remove imported/failed entries older than N days.
     */
    public static function cleanup($keepDays = 7)
    {
        $root = self::root();
        if (!is_dir($root)) {
            return 0;
        }
        $cutoff = time() - ($keepDays * 86400);
        $removed = 0;
        foreach (scandir($root) as $file) {
            if ($file === '.' || $file === '..' || substr($file, -5) !== '.json') {
                continue;
            }
            $path = $root . DIRECTORY_SEPARATOR . $file;
            $entry = OtxPulseStagingStore::readJson($path);
            if (!$entry) {
                continue;
            }
            $status = $entry['status'] ?? '';
            if (!in_array($status, ['imported', 'failed'], true)) {
                continue;
            }
            $updated = strtotime($entry['updated_at'] ?? $entry['enqueued_at'] ?? 'now');
            if ($updated !== false && $updated < $cutoff) {
                @unlink($path);
                $removed++;
            }
        }
        return $removed;
    }
}
