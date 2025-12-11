<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client as MongoClient;
use Illuminate\Support\Facades\Log;


class MispTagService
{
    // Tag cache เพื่อลด query ซ้ำ
    private $tagCache = [];
    
    // Preload tags ทั้งหมดเพื่อลด query
    public function preloadTags()
    {
        if (!empty($this->tagCache)) return;
        
        $tags = DB::connection('mysql_misp')->table('tags')
            ->select('id', 'name')
            ->get();
            
        foreach ($tags as $tag) {
            $this->tagCache[$tag->name] = $tag->id;
        }
        
        Log::info('[MISP TAG] 📦 Preloaded tags', ['count' => count($this->tagCache)]);
    }

    /**
     * อัพเดท event tags (รับ document จาก Command แทนการ query ซ้ำ)
     * 
     * @param string $pulseId
     * @param string $tags - comma separated tags
     * @param array|null $document - document จาก MongoDB (optional, ถ้าไม่ส่งมาจะ query เอง)
     */
    public function update($pulseId, $tags, $document = null)
    {
        if (empty($pulseId)) return false;

        // ถ้าไม่ได้ส่ง document มา ให้ query เอง (backward compatible)
        if ($document === null) {
            $client = new MongoClient(config("app.DB_MONGO_DEV"));
            $collection = $client->sosecure_threatintelligent->fx_otx_events;
            $document = $collection->findOne(['pulse_id' => $pulseId]);
            
            if (!$document) return false;
            
            // อัปเดต tags ใน MongoDB
            $collection->updateOne(
                ['_id' => $document['_id']],
                ['$set' => ['tags' => $tags]]
            );
        }

        // เตรียม tags array
        $tags = trim((string) $tags, ",");
        $tagArray = array_filter(array_map('trim', explode(",", $tags)));

        $source = $document['source'] ?? null;
        $eventId = null;

        // หา event_id ตาม source
        if ($source === "misp") {
            $parts = explode('.', $pulseId);
            $eventId = $parts[1] ?? null;
        } elseif ($source === "otx.alienvault") {
            $uuid = $document['mips_uuid'] ?? null;
            if ($uuid) {
                $event = DB::connection('mysql_misp')->table('events')
                    ->select('id')->where('uuid', $uuid)->first();
                if ($event) {
                    $eventId = $event->id;
                    // เพิ่ม 'OTX' tag เสมอ
                    if (!in_array('OTX', $tagArray)) {
                        $tagArray[] = 'OTX';
                    }
                }
            }
        }

        if (!$eventId) return false;

        // ใช้ helper method ที่ optimize แล้ว
        $this->syncEventTags($eventId, $tagArray);

        return true;
    }

    /**
     * อัพเดท indicator tags (รับ document จาก Command)
     * 
     * @param string $pulseId
     * @param string $indicatorId
     * @param string $tags
     * @param array|null $fxDoc - document จาก fx_otx_events (optional)
     */
    public function updateFromIndicator($pulseId, $indicatorId, $tags, $fxDoc = null)
    {
        Log::info('[MISP ATTRIBUTE] ⬅️ Entered updateFromIndicator()', [
            'pulse_id' => $pulseId,
            'indicator_id' => $indicatorId,
            'tags' => $tags
        ]);

        // ถ้าไม่ได้ส่ง document มา ให้ query เอง (backward compatible)
        if ($fxDoc === null) {
            $mongo = new \MongoDB\Client(config('app.DB_MONGO_DEV'));
            $fxDoc = $mongo->sosecure_threatintelligent->fx_otx_events->findOne(['pulse_id' => $pulseId]);
        }
        
        if (!$fxDoc) return false;

        $source = $fxDoc['source'] ?? null;
        $eventId = null;

        // หา event_id
        if ($source === 'misp') {
            $eventId = explode('.', $pulseId)[1] ?? null;
        } elseif ($source === 'otx.alienvault') {
            $uuid = $fxDoc['mips_uuid'] ?? null;
            $event = DB::connection('mysql_misp')->table('events')->select('id')->where('uuid', $uuid)->first();
            if ($event) {
                $eventId = $event->id;
                $tags = trim($tags ?? '') !== '' ? $tags . ',OTX' : 'OTX';
            }
        }

        if (!$eventId) return false;

        $numericAttributeId = is_string($indicatorId) ? explode('.', $indicatorId)[1] ?? $indicatorId : $indicatorId;

        // ตรวจสอบ attribute
        $attribute = DB::connection('mysql_misp')->table('attributes')
            ->where('event_id', $eventId)
            ->where('id', '=', $numericAttributeId)
            ->first();

        if (!$attribute) {
            Log::warning('[MISP ATTRIBUTE] ⚠️ Attribute not found in MISP', [
                'event_id' => $eventId,
                'attribute_id' => $numericAttributeId
            ]);
            return false;
        }

        // เรียกอัปเดตหรือเคลียร์ tags
        $this->syncAttributeTags($eventId, $attribute->id, $tags);

        return true;
    }

    /**
     * Sync event tags (optimized with batch insert)
     */
    private function syncEventTags($eventId, array $tagArray)
    {
        DB::connection('mysql_misp')->transaction(function () use ($eventId, $tagArray) {
            // ลบ tags เดิมทั้งหมด
            DB::connection('mysql_misp')->table('event_tags')
                ->where('event_id', $eventId)
                ->delete();

            if (empty($tagArray)) return;

            // กำจัด duplicate tags (case-sensitive)
            $tagArray = array_unique(array_values($tagArray));

            // เตรียม tag IDs
            $tagIds = [];
            foreach ($tagArray as $tag) {
                $tagIds[] = $this->getOrCreateTagId($tag, "#ffffff");
            }

            // กำจัด duplicate tag IDs (กรณี tag ชื่อต่างกันแต่ชี้ไปที่ tag_id เดียวกัน)
            $tagIds = array_unique($tagIds);

            // Batch insert
            $inserts = [];
            foreach ($tagIds as $tagId) {
                $inserts[] = [
                    'event_id' => $eventId,
                    'tag_id' => $tagId
                ];
            }

            if (!empty($inserts)) {
                try {
                    DB::connection('mysql_misp')->table('event_tags')->insert($inserts);
                } catch (\Exception $e) {
                    // ถ้ามี unique constraint error, ลอง insert ทีละตัว
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        foreach ($inserts as $insert) {
                            DB::connection('mysql_misp')->table('event_tags')
                                ->insertOrIgnore($insert);
                        }
                    } else {
                        throw $e;
                    }
                }
            }
        });
    }

    /**
     * Sync attribute tags (optimized with batch insert)
     */
    private function syncAttributeTags($eventId, $attributeId, $tags)
    {
        Log::info('[MISP ATTRIBUTE] ⬅️ Entered syncAttributeTags()', [
            'event_id' => $eventId,
            'attribute_id' => $attributeId,
            'tags' => $tags
        ]);

        DB::connection('mysql_misp')->transaction(function () use ($eventId, $attributeId, $tags) {
            // เคลียร์ tag เก่าออกก่อน
            DB::connection('mysql_misp')->table('attribute_tags')
                ->where('event_id', $eventId)
                ->where('attribute_id', $attributeId)
                ->delete();

            // ถ้าไม่มี tags ใหม่ → จบแค่นี้
            if (empty(trim($tags))) {
                Log::info('[MISP ATTRIBUTE] 🧹 Tags cleared only (no new tags)', [
                    'event_id' => $eventId,
                    'attribute_id' => $attributeId
                ]);
                return;
            }

            // แยก tag เป็น array
            $tagArray = array_filter(array_map('trim', explode(',', $tags)));
            
            // กำจัด duplicate tags
            $tagArray = array_unique(array_values($tagArray));

            // เตรียม tag IDs
            $tagIds = [];
            foreach ($tagArray as $tag) {
                if ($tag === '') continue;
                $tagIds[] = $this->getOrCreateTagId($tag, "#ffffff");
            }
            
            // กำจัด duplicate tag IDs
            $tagIds = array_unique($tagIds);

            // Batch insert
            $inserts = [];
            foreach ($tagIds as $tagId) {
                $inserts[] = [
                    'event_id' => $eventId,
                    'attribute_id' => $attributeId,
                    'tag_id' => $tagId,
                    'local' => 0
                ];
            }

            if (!empty($inserts)) {
                try {
                    DB::connection('mysql_misp')->table('attribute_tags')->insert($inserts);
                } catch (\Exception $e) {
                    // ถ้ามี unique constraint error, ลอง insert ทีละตัว
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        foreach ($inserts as $insert) {
                            DB::connection('mysql_misp')->table('attribute_tags')
                                ->insertOrIgnore($insert);
                        }
                    } else {
                        throw $e;
                    }
                }
            }

            Log::info('[MISP ATTRIBUTE] ✅ Tags synced', [
                'event_id' => $eventId,
                'attribute_id' => $attributeId,
                'tags_applied' => $tagArray
            ]);
        });
    }

    /**
     * Get or create tag ID (with cache)
     */
    private function getOrCreateTagId($tag, $color)
    {
        $tag = trim($tag);
        
        // ตรวจสอบ cache ก่อน
        if (isset($this->tagCache[$tag])) {
            return $this->tagCache[$tag];
        }

        // Query DB
        $existing = DB::connection('mysql_misp')->table('tags')
            ->select('id')->where('name', $tag)->first();

        if ($existing) {
            $this->tagCache[$tag] = $existing->id;
            return $existing->id;
        }

        // สร้างใหม่
        $id = DB::connection('mysql_misp')->table('tags')->insertGetId([
            'name' => $tag,
            'colour' => $color,
            'exportable' => 1,
            'org_id' => 0,
            'user_id' => 0,
            'hide_tag' => 0,
            'numerical_value' => null,
            'is_galaxy' => 0,
            'is_custom_galaxy' => 0,
            'local_only' => 0
        ]);

        $this->tagCache[$tag] = $id;
        return $id;
    }
}
