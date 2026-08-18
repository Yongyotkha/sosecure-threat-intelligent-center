<?php

namespace App\Console\Commands;

use App\Entities\TransactionBatchjob;
use App\FXTechniques;
use Exception;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

class MDAdversaries extends Command
{
    protected $signature = 'app:MDAdversaries
                            {--database= : Mongo database (default: MONGO_DATABASE / config mongodb.indicator.database)}';

    protected $description = 'Sync OTX adversaries → Mongo (actors, auto-campaigns, pulse relations, techniques)';

    protected $dbName;
    protected $apiKey;
    protected $savedAdversaries = 0;
    protected $savedRelations = 0;
    protected $savedCampaigns = 0;
    protected $techniqueMap = null;

    /** Max pulse-detail lookups per adversary (attack_ids only exist on the detail endpoint). */
    protected $techniqueDetailCap = 50;

    public function handle()
    {
        $this->apiKey = env('OTX_KEY', '') ?: env('otx_API_Key', '');
        if ($this->apiKey === '') {
            $this->error('OTX_KEY (or otx_API_Key) is empty');
            return 1;
        }

        $this->dbName = $this->option('database')
            ?: config('mongodb.indicator.database')
            ?: env('MONGO_DATABASE', '')
            ?: 'sosecure_threatintelligent_dev';
        $this->info('Mongo database: ' . $this->dbName);

        $job = TransactionBatchjob::where('mode', 'OTX_Adversaries')->first();
        if ($job) {
            $job->progress = 2;
            $job->transcation_date_start = date('Y-m-d H:i:s');
            $job->transcation_date = date('Y-m-d H:i:s');
            $job->save();
        }

        ini_set('memory_limit', '-1');

        $nextUrl = 'https://otx.alienvault.com/otxapi/adversaries/?limit=50&page=1&sort=value';
        $page = 0;

        while ($nextUrl) {
            $page++;
            $this->info("Fetching adversaries page {$page} ...");
            $payload = $this->otxGet($nextUrl);
            if ($payload === null) {
                $this->error('Adversary list fetch failed on page ' . $page);
                break;
            }

            $total = (int) ($payload['count'] ?? 0);
            if ($page === 1) {
                $this->info('OTX adversaries total: ' . number_format($total));
            }

            foreach ($payload['results'] ?? [] as $adversary) {
                $this->processAdversary($adversary);
            }

            $nextUrl = $payload['next'] ?? null;
        }

        if ($job) {
            $job->progress = 1;
            $job->transcation_date_end = date('Y-m-d H:i:s');
            $job->transcation_date = date('Y-m-d H:i:s');
            $job->save();
        }

        $this->info('=========================================');
        $this->info('ADVERSARIES SYNC SUMMARY');
        $this->info('Adversaries saved : ' . number_format($this->savedAdversaries));
        $this->info('Pulse relations     : ' . number_format($this->savedRelations));
        $this->info('Campaigns upserted  : ' . number_format($this->savedCampaigns));
        $this->info('=========================================');

        return 0;
    }

    protected function mongoClient()
    {
        $uri = config('app.DB_MONGO_DEV') ?: env('DB_MONGO_DEV', '') ?: env('DB_MONGO_STOREDATA', '');
        if ($uri === '') {
            throw new Exception('DB_MONGO_DEV / DB_MONGO_STOREDATA is empty');
        }

        return new \MongoDB\Client($uri);
    }

    protected function otxGet($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTPHEADER => [
                'X-OTX-API-KEY: ' . $this->apiKey,
                'Accept: application/json',
            ],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $code >= 400) {
            $this->error("OTX HTTP {$code}: {$url}");
            return null;
        }

        $payload = json_decode($body, true);
        return is_array($payload) ? $payload : null;
    }

    protected function fetchPulseAttackIds($pulseId)
    {
        if (!$pulseId) {
            return [];
        }

        $detail = $this->otxGet('https://otx.alienvault.com/otxapi/pulses/' . rawurlencode($pulseId) . '/');
        if (!is_array($detail) || empty($detail['attack_ids']) || !is_array($detail['attack_ids'])) {
            return [];
        }

        return $detail['attack_ids'];
    }

    protected function processAdversary(array $adversary)
    {
        $uuid = $adversary['uuid'] ?? null;
        $name = $adversary['value'] ?? null;
        if (!$uuid || !$name) {
            return;
        }

        $this->info('Adversary: ' . $name);
        $this->saveAdversary($adversary);
        $this->savedAdversaries++;

        $pulseUrl = 'https://otx.alienvault.com/otxapi/pulses/?limit=20&page=1&sort=-modified&q=adversary:'
            . rawurlencode($name);
        $pulseContext = [
            'tags' => [],
            'categories' => [],
            'locations' => [],
            'techniques' => [],
            'latestCreatedAt' => null,
        ];

        $detailFetched = 0;

        while ($pulseUrl) {
            $payload = $this->otxGet($pulseUrl);
            if ($payload === null) {
                break;
            }

            $this->info('  Pulses for ' . $name . ': ' . ($payload['count'] ?? 0));
            foreach ($payload['results'] ?? [] as $pulse) {
                $this->info('    Pulse: ' . ($pulse['name'] ?? $pulse['id'] ?? ''));
                $this->collectPulseContext($pulse, $pulseContext);

                // attack_ids is only populated on the pulse DETAIL endpoint,
                // so fetch details (capped) to derive real attack techniques.
                if ($detailFetched < $this->techniqueDetailCap) {
                    $attackIds = $this->fetchPulseAttackIds($pulse['id'] ?? null);
                    foreach ($attackIds as $attack) {
                        $this->collectTechnique($pulseContext['techniques'], $attack);
                    }
                    $detailFetched++;
                }

                $this->savePulseRelation($pulse, $uuid, $name);
                $this->savedRelations++;
            }

            $pulseUrl = $payload['next'] ?? null;
        }

        $this->enrichAdversaryFromPulseContext($uuid, $adversary, $pulseContext);
        $this->saveAutoCampaign($uuid, $adversary, $pulseContext);
    }

    protected function saveAdversary(array $adversary)
    {
        $clientMD = $this->mongoClient();
        $collection = $clientMD->{$this->dbName}->fx_otx_adversaries;
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);
        $nowString = date('Y-m-d H:i:s');

        $synonyms = [];
        if (!empty($adversary['meta']['synonyms']) && is_array($adversary['meta']['synonyms'])) {
            $synonyms = $adversary['meta']['synonyms'];
        }

        $collection->updateOne(
            ['adversary_uuid' => $adversary['uuid']],
            ['$set' => [
                'name' => $adversary['value'] ?? '',
                'description' => $adversary['description'] ?? '',
                'synonyms' => implode(',', $synonyms),
                'country' => $adversary['meta']['country'] ?? '',
                'location' => $adversary['meta']['country'] ?? '',
                'tags' => '',
                'category' => '',
                'create_at' => $nowString,
                'update_at' => $nowString,
                'status' => 1,
                'create_by' => 'system',
                'update_by' => 'system',
                'updated_at' => $date_now,
                'updated_by' => 'system',
                'source' => 'otx.alienvault',
            ],
                '$setOnInsert' => [
                    'created_by' => 'system',
                    'created_at' => $date_now,
                    'delete_at' => null,
                    // Same default avatar as manual create_actor (UI list).
                    'logo' => '/asset_salepage/images/AgentBasedDetection.png',
                ],
            ],
            ['upsert' => true]
        );
    }

    protected function collectPulseContext(array $pulse, array &$pulseContext)
    {
        foreach ($pulse['tags'] ?? [] as $tag) {
            $this->pushUniqueValue($pulseContext['tags'], $tag);
        }

        foreach ($pulse['industries'] ?? [] as $industry) {
            $this->pushUniqueValue($pulseContext['categories'], $industry);
        }

        foreach ($pulse['targeted_countries'] ?? [] as $country) {
            $this->pushUniqueValue($pulseContext['locations'], $country);
        }

        foreach ($pulse['attack_ids'] ?? [] as $attack) {
            $this->collectTechnique($pulseContext['techniques'], $attack);
        }

        $createdAt = $pulse['created'] ?? null;
        if ($createdAt && ($pulseContext['latestCreatedAt'] === null || strcmp($createdAt, $pulseContext['latestCreatedAt']) > 0)) {
            $pulseContext['latestCreatedAt'] = $createdAt;
        }
    }

    protected function enrichAdversaryFromPulseContext($uuid, array $adversary, array $pulseContext)
    {
        $clientMD = $this->mongoClient();
        $collection = $clientMD->{$this->dbName}->fx_otx_adversaries;
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);
        $nowString = date('Y-m-d H:i:s');

        $location = $adversary['meta']['country'] ?? '';
        if ($location === '' && !empty($pulseContext['locations'])) {
            $location = implode(', ', $pulseContext['locations']);
        }

        $category = !empty($pulseContext['categories']) ? implode(', ', $pulseContext['categories']) : '';
        $tags = !empty($pulseContext['tags']) ? implode(', ', $pulseContext['tags']) : '';
        $createAt = $pulseContext['latestCreatedAt'] ? date('Y-m-d H:i:s', strtotime($pulseContext['latestCreatedAt'])) : $nowString;

        $attackTechniques = array_values($pulseContext['techniques']);
        $attackTechniquesText = implode(', ', array_map(function ($technique) {
            return $technique['name'] !== '' ? $technique['code'] . ' - ' . $technique['name'] : $technique['code'];
        }, $attackTechniques));

        $collection->updateOne(
            ['adversary_uuid' => $uuid],
            ['$set' => [
                'tags' => $tags,
                'category' => $category,
                'location' => $location,
                'create_at' => $createAt,
                'attack_techniques' => $attackTechniques,
                'attack_techniques_text' => $attackTechniquesText,
                'update_at' => $nowString,
                'updated_at' => $date_now,
                'updated_by' => 'system',
            ]]
        );
    }

    protected function saveAutoCampaign($adversaryUuid, array $adversary, array $pulseContext)
    {
        $name = $adversary['value'] ?? '';
        if ($name === '') {
            return;
        }

        $clientMD = $this->mongoClient();
        $db = $clientMD->{$this->dbName};
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);
        $campaignUuid = $this->campaignUuidFromAdversary($adversaryUuid);
        $description = trim((string) ($adversary['description'] ?? ''));
        if ($description === '') {
            $description = 'Auto-generated campaign for adversary ' . $name . ' from OTX.';
        }

        $db->fx_otx_campaign->updateOne(
            [
                'source' => 'otx.alienvault',
                'source_adversary_uuid' => $adversaryUuid,
            ],
            [
                '$set' => [
                    'campainge_uuid' => $campaignUuid,
                    'name' => $name,
                    'description' => $description,
                    'status' => '1',
                    'update_at' => $date_now,
                    'update_by' => 'system',
                    'source' => 'otx.alienvault',
                    'source_adversary_uuid' => $adversaryUuid,
                    'delete_at' => null,
                ],
                '$setOnInsert' => [
                    'create_at' => $date_now,
                    'create_by' => 'system',
                ],
            ],
            ['upsert' => true]
        );

        // Link campaign <-> actor
        $db->fx_otx_adversaries_related->updateOne(
            [
                'adversary_uuid' => $campaignUuid,
                'actor_id' => $adversaryUuid,
                'mode' => 'campainge',
                'join' => 'actor',
            ],
            [
                '$set' => [
                    'adversary_name' => $name,
                    'pulse_id' => '',
                    'pulse_name' => '',
                    'tactics_id' => '',
                    'tactics_name' => '',
                    'techniques' => '',
                    'actor_id' => $adversaryUuid,
                    'actor_name' => $name,
                    'mode' => 'campainge',
                    'join' => 'actor',
                    'modified' => $date_now,
                    'updated_at' => $date_now,
                    'updated_by' => 'system',
                    'delete_at' => null,
                    'source' => 'otx.alienvault',
                ],
                '$setOnInsert' => [
                    'created_at' => $date_now,
                    'created_by' => 'system',
                ],
            ],
            ['upsert' => true]
        );

        // Replace technique links for this auto-campaign (keep actor/pulse links intact)
        $db->fx_otx_adversaries_related->deleteMany([
            'adversary_uuid' => $campaignUuid,
            'mode' => 'campainge',
            'join' => 'techniques',
            'source' => 'otx.alienvault',
        ]);

        foreach (array_values($pulseContext['techniques']) as $technique) {
            $code = $technique['code'] ?? '';
            if ($code === '') {
                continue;
            }

            $mapped = $this->resolveTechnique($code);
            $techId = $mapped['id'] ?? $code;
            $techName = $mapped['name'] !== '' ? $mapped['name'] : ($technique['name'] ?? $code);
            $tacticsId = $mapped['tactics_id'] ?? '';
            $tacticsName = $mapped['tactics_name'] ?? '';

            $db->fx_otx_adversaries_related->updateOne(
                [
                    'adversary_uuid' => $campaignUuid,
                    'mode' => 'campainge',
                    'join' => 'techniques',
                    'pulse_id' => (string) $techId,
                ],
                [
                    '$set' => [
                        'adversary_name' => $name,
                        'pulse_id' => (string) $techId,
                        'pulse_name' => '',
                        'tactics_id' => $tacticsId,
                        'tactics_name' => $tacticsName,
                        'techniques' => $techName,
                        'mode' => 'campainge',
                        'join' => 'techniques',
                        'modified' => $date_now,
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
                        'delete_at' => null,
                        'source' => 'otx.alienvault',
                        'technique_code' => $code,
                    ],
                    '$setOnInsert' => [
                        'created_at' => $date_now,
                        'created_by' => 'system',
                    ],
                ],
                ['upsert' => true]
            );
        }

        $this->savedCampaigns++;
        $this->info('  Campaign upserted: ' . $name . ' (' . count($pulseContext['techniques']) . ' techniques)');
    }

    protected function campaignUuidFromAdversary($adversaryUuid)
    {
        $hash = md5('otx-campaign:' . $adversaryUuid);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }

    protected function collectTechnique(array &$techniques, $attack)
    {
        $code = '';
        $name = '';

        if (is_array($attack)) {
            $code = trim((string) ($attack['id'] ?? $attack['code'] ?? ''));
            $name = trim((string) ($attack['name'] ?? $attack['display_name'] ?? ''));
            if (strpos($name, ' - ') !== false) {
                $parts = explode(' - ', $name, 2);
                if (isset($parts[1])) {
                    $name = trim($parts[1]);
                }
            }
        } else {
            $code = trim((string) $attack);
        }

        if ($code === '') {
            return;
        }

        $code = strtoupper($code);
        $mapped = $this->resolveTechnique($code);
        if ($name === '') {
            $name = $mapped['name'];
        }

        $techniques[$code] = [
            'code' => $code,
            'name' => $name,
        ];
    }

    protected function resolveTechniqueName($code)
    {
        return $this->resolveTechnique($code)['name'];
    }

    protected function resolveTechnique($code)
    {
        $this->loadTechniqueMap();
        $code = strtoupper(trim((string) $code));

        return $this->techniqueMap[$code] ?? [
            'id' => '',
            'code' => $code,
            'name' => '',
            'tactics_id' => '',
            'tactics_name' => '',
        ];
    }

    protected function loadTechniqueMap()
    {
        if ($this->techniqueMap !== null) {
            return;
        }

        $this->techniqueMap = [];
        try {
            foreach (FXTechniques::select('id', 'code', 'name', 'tactics_id', 'tactics_name')->get() as $technique) {
                $techCode = strtoupper(trim((string) $technique->code));
                if ($techCode === '') {
                    continue;
                }
                $this->techniqueMap[$techCode] = [
                    'id' => (string) $technique->id,
                    'code' => $techCode,
                    'name' => (string) $technique->name,
                    'tactics_id' => (string) $technique->tactics_id,
                    'tactics_name' => (string) $technique->tactics_name,
                ];
            }
        } catch (Exception $e) {
            $this->warn('Technique map load failed: ' . $e->getMessage());
        }
    }

    protected function pushUniqueValue(array &$target, $value)
    {
        if (!is_string($value)) {
            return;
        }

        $cleanValue = trim($value);
        if ($cleanValue === '' || in_array($cleanValue, $target, true)) {
            return;
        }

        $target[] = $cleanValue;
    }

    protected function savePulseRelation(array $pulse, $adversaryUuid, $adversaryName)
    {
        $pulseId = $pulse['id'] ?? null;
        if (!$pulseId) {
            return;
        }

        $clientMD = $this->mongoClient();
        $db = $clientMD->{$this->dbName};
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);

        // Same shape as IndicatorsController insert_tag (UI reads mode+join).
        // Re-run upgrades legacy auto-links that were saved without mode/join.
        $db->fx_otx_adversaries_related->updateOne(
            [
                'adversary_uuid' => $adversaryUuid,
                'pulse_id' => $pulseId,
            ],
            ['$set' => [
                'adversary_name' => $adversaryName,
                'pulse_name' => $pulse['name'] ?? '',
                'mode' => 'indicator',
                'join' => 'actor',
                'modified' => $date_now,
                'updated_at' => $date_now,
                'updated_by' => 'system',
                'delete_at' => null,
                'source' => 'otx.alienvault',
            ],
                '$setOnInsert' => [
                    'created_by' => 'system',
                    'created_at' => $date_now,
                ],
            ],
            ['upsert' => true]
        );

        // Backfill pulse stub only if main pulse feed has not imported it yet.
        $exists = $db->fx_otx_events->count(['pulse_id' => $pulseId]);
        if ($exists > 0) {
            return;
        }

        try {
            $references = implode(', ', $pulse['references'] ?? []);
            $tags = implode(', ', $pulse['tags'] ?? []);
            $industries = implode(', ', $pulse['industries'] ?? []);
            $malwareFamilies = implode(', ', array_column($pulse['malware_families'] ?? [], 'display_name'));

            $db->fx_otx_events->updateOne(
                ['pulse_id' => $pulseId],
                ['$set' => [
                    'name' => $pulse['name'] ?? '',
                    'description' => $pulse['description'] ?? '',
                    'modified' => $pulse['modified'] ?? '',
                    'created' => $pulse['created'] ?? '',
                    'public' => $pulse['public'] ?? '',
                    'TLP' => $pulse['TLP'] ?? '',
                    'indicator_count' => $pulse['indicator_count'] ?? '',
                    'is_modified' => $pulse['is_modified'] ?? '',
                    'indicator_type_counts' => $pulse['indicator_type_counts'] ?? '',
                    'references' => $references,
                    'tags' => $tags,
                    'industries' => $industries,
                    'malware_families' => $malwareFamilies,
                    'author_username' => $pulse['author']['username'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => 'system',
                ],
                    '$setOnInsert' => [
                        'transcation_id' => null,
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => 'system',
                        'deleted_at' => null,
                        'transaction_date' => date('Y-m-d'),
                    ],
                ],
                ['upsert' => true]
            );
        } catch (Exception $e) {
            $this->warn('Pulse stub save failed for ' . $pulseId . ': ' . $e->getMessage());
        }
    }
}
