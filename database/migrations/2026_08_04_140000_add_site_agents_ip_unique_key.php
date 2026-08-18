<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-delete-friendly unique IP per site:
 * ip_unique_key = ip_private when deleted_at IS NULL, else NULL.
 * MySQL allows multiple NULLs in a UNIQUE index.
 *
 * App DB prefix is often `fx_`, so Laravel logical table is `site_agents`
 * (physical: `fx_site_agents`). Do NOT pass `fx_site_agents` to Schema/DB::table.
 */
class AddSiteAgentsIpUniqueKey extends Migration
{
    public function up()
    {
        $logical = $this->resolveAgentsLogicalTable();
        if ($logical === null) {
            return;
        }

        if (!$this->hasColumnPhysical($logical, 'ip_unique_key')) {
            Schema::table($logical, function (Blueprint $blueprint) {
                $blueprint->string('ip_unique_key', 64)->nullable();
            });
        }

        DB::table($logical)
            ->whereNull('deleted_at')
            ->whereNotNull('ip_private')
            ->where('ip_private', '!=', '')
            ->update(['ip_unique_key' => DB::raw('ip_private')]);

        DB::table($logical)
            ->whereNotNull('deleted_at')
            ->update(['ip_unique_key' => null]);

        $dups = DB::table($logical)
            ->select('site_id', 'ip_unique_key', DB::raw('COUNT(*) as c'), DB::raw('MIN(id) as keep_id'))
            ->whereNull('deleted_at')
            ->whereNotNull('ip_unique_key')
            ->groupBy('site_id', 'ip_unique_key')
            ->having('c', '>', 1)
            ->get();

        foreach ($dups as $dup) {
            DB::table($logical)
                ->where('site_id', $dup->site_id)
                ->where('ip_unique_key', $dup->ip_unique_key)
                ->whereNull('deleted_at')
                ->where('id', '!=', $dup->keep_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'ip_unique_key' => null,
                ]);
        }

        if (!$this->hasIndexPhysical($logical, 'site_agents_site_ip_unique')) {
            Schema::table($logical, function (Blueprint $blueprint) {
                $blueprint->unique(['site_id', 'ip_unique_key'], 'site_agents_site_ip_unique');
            });
        }
    }

    public function down()
    {
        $logical = $this->resolveAgentsLogicalTable();
        if ($logical === null) {
            return;
        }

        if ($this->hasIndexPhysical($logical, 'site_agents_site_ip_unique')) {
            Schema::table($logical, function (Blueprint $blueprint) {
                $blueprint->dropUnique('site_agents_site_ip_unique');
            });
        }

        if ($this->hasColumnPhysical($logical, 'ip_unique_key')) {
            Schema::table($logical, function (Blueprint $blueprint) {
                $blueprint->dropColumn('ip_unique_key');
            });
        }
    }

    private function tablePrefix()
    {
        return (string) Schema::getConnection()->getTablePrefix();
    }

    private function physicalName($logical)
    {
        return $this->tablePrefix().$logical;
    }

    /**
     * Return Laravel logical table name (`site_agents`) if the physical base table exists.
     */
    private function resolveAgentsLogicalTable()
    {
        // Prefer model name; physical is usually fx_site_agents via prefix.
        if ($this->isBaseTablePhysical('site_agents')) {
            return 'site_agents';
        }
        // Rare: no prefix and table literally named fx_site_agents
        if ($this->tablePrefix() === '' && $this->isBaseTablePhysicalRaw('fx_site_agents')) {
            return 'fx_site_agents';
        }
        return null;
    }

    private function isBaseTablePhysical($logical)
    {
        return $this->isBaseTablePhysicalRaw($this->physicalName($logical));
    }

    private function isBaseTablePhysicalRaw($physical)
    {
        $row = DB::selectOne(
            'SELECT TABLE_TYPE AS table_type
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$physical]
        );
        if (!$row) {
            return false;
        }
        $type = isset($row->table_type) ? $row->table_type : (isset($row->TABLE_TYPE) ? $row->TABLE_TYPE : '');
        return strtoupper((string) $type) === 'BASE TABLE';
    }

    private function hasColumnPhysical($logical, $column)
    {
        $row = DB::selectOne(
            'SELECT 1 AS ok
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
             LIMIT 1',
            [$this->physicalName($logical), $column]
        );
        return !empty($row);
    }

    private function hasIndexPhysical($logical, $indexName)
    {
        $row = DB::selectOne(
            'SELECT 1 AS ok
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
             LIMIT 1',
            [$this->physicalName($logical), $indexName]
        );
        return !empty($row);
    }
}
