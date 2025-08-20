<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client as MongoClient;
use Illuminate\Support\Facades\Log;


class MispTagService
{
    public function update($pulseId, $tags)
    {
        if (empty($pulseId)) return false;

        $color = "#ffffff";

        // 1. ค้นหา document จาก MongoDB
        $client = new MongoClient(config("app.DB_MONGO_DEV"));
        $collection = $client->sosecure_threatintelligent->fx_otx_events;
        $document = $collection->findOne(['pulse_id' => $pulseId]);

        if (!$document) return false;

        // 2. อัปเดต tags ใน MongoDB
        $collection->updateOne(
            ['_id' => $document['_id']],
            ['$set' => ['tags' => $tags]]
        );

        // 3. เตรียม tags array (แม้จะว่าง)
        $tags = trim((string) $tags, ",");
        $tagArray = array_filter(array_map('trim', explode(",", $tags)));

        // 4. ถ้า source มาจาก MISP
        if ($document['source'] === "misp") {
            $parts = explode('.', $pulseId);
            $eventId = $parts[1] ?? null;

            if (!$eventId) return false;

            // ลบ tags เดิมทั้งหมด
            DB::connection('mysql_misp')->table('event_tags')
                ->where('event_id', $eventId)
                ->delete();

            // เพิ่มใหม่ถ้ามี
            foreach ($tagArray as $tag) {
                $tagId = $this->getOrCreateTagId($tag, $color);
                DB::connection('mysql_misp')->table('event_tags')->insert([
                    'event_id' => $eventId,
                    'tag_id' => $tagId
                ]);
            }
        }

        // 5. ถ้าเป็น OTX
        if ($document['source'] === "otx.alienvault") {
            $uuid = $document['mips_uuid'] ?? null;

            if ($uuid) {
                $event = DB::connection('mysql_misp')->table('events')
                    ->select('id')->where('uuid', $uuid)->first();

                if ($event) {
                    $eventId = $event->id;

                    DB::connection('mysql_misp')->table('event_tags')
                        ->where('event_id', $eventId)
                        ->delete();

                    // เพิ่ม 'OTX' เสมอ
                    $tagArray[] = 'OTX';

                    foreach ($tagArray as $tag) {
                        $tagId = $this->getOrCreateTagId($tag, $color);
                        DB::connection('mysql_misp')->table('event_tags')->insert([
                            'event_id' => $eventId,
                            'tag_id' => $tagId
                        ]);
                    }
                }
            }
        }

        return true;
    }

    private function getOrCreateTagId($tag, $color)
    {
        $tag = trim($tag);
        $existing = DB::connection('mysql_misp')->table('tags')
            ->select('id')->where('name', $tag)->first();

        if ($existing) return $existing->id;

        return DB::connection('mysql_misp')->table('tags')->insertGetId([
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
    }



    public function updateFromIndicator($pulseId, $indicatorId, $tags)
    {
        Log::info('[MISP ATTRIBUTE] ⬅️ Entered updateFromIndicator()', [
            'pulse_id' => $pulseId,
            'indicator_id' => $indicatorId,
            'tags' => $tags
        ]);

        $mongo = new \MongoDB\Client(config('app.DB_MONGO_DEV'));

        // 1. อัปเดต MongoDB (ทั้ง 2 คอลเลกชัน)
        $mongo->sosecure_threatintelligent->fx_otx_events_indicator_ref->updateOne(
            ['pulse_id' => $pulseId, 'indicator_id' => $indicatorId],
            ['$set' => ['tags' => $tags]]
        );

        $mongo->sosecure_threatintelligent->fx_otx_indicator_detail->updateOne(
            ['indicator_id' => $indicatorId],
            ['$set' => ['tags' => $tags]]
        );

        // 2. ดึง event
        $fxDoc = $mongo->sosecure_threatintelligent->fx_otx_events->findOne(['pulse_id' => $pulseId]);
        if (!$fxDoc) return false;

        $source = $fxDoc['source'] ?? null;
        $eventId = null;

        // 3. หา event_id
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

        // 4. ตรวจสอบ attribute
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

        // 5. เรียกอัปเดตหรือเคลียร์ tags
        $this->syncAttributeTags($eventId, $attribute->id, $tags);

        return true;
    }


    private function syncAttributeTags($eventId, $attributeId, $tags)
    {
        Log::info('[MISP ATTRIBUTE] ⬅️ Entered syncAttributeTags()', [
            'event_id' => $eventId,
            'attribute_id' => $attributeId,
            'tags' => $tags
        ]);

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
        $color = "#ffffff";

        foreach ($tagArray as $tag) {
            if ($tag === '') continue;

            // หา tag เดิม หรือสร้างใหม่
            $tagRow = DB::connection('mysql_misp')->table('tags')->where('name', $tag)->first();

            $tagId = $tagRow->id ?? DB::connection('mysql_misp')->table('tags')->insertGetId([
                'name' => $tag,
                'colour' => $color,
                'exportable' => 1,
                'org_id' => 0,
                'user_id' => 0,
                'hide_tag' => 0,
                'is_galaxy' => 0,
                'is_custom_galaxy' => 0,
                'local_only' => 0,
            ]);

            // insert tag ใหม่
            DB::connection('mysql_misp')->table('attribute_tags')->insert([
                'event_id' => $eventId,
                'attribute_id' => $attributeId,
                'tag_id' => $tagId,
                'local' => 0
            ]);
        }

        Log::info('[MISP ATTRIBUTE] ✅ Tags synced', [
            'event_id' => $eventId,
            'attribute_id' => $attributeId,
            'tags_applied' => $tagArray
        ]);
    }
}
