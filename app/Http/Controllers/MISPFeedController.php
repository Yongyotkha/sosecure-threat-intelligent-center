<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Str;
use App\ApiToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class MISPFeedController extends Controller
{

    private $feeds = [
        'b0b64d93-4cb6-42d4-bff6-dcdfbc85d299' => [
            'info' => 'OSINT - Domain Abuse',
            'date' => '2025-07-13',
            'threat_level_id' => '3',
            'analysis' => '2',
            'distribution' => '1',
            'published' => true,
            'Orgc' => ['name' => 'Threat inSights'],
            'Org' => ['name' => 'SOSECURE-TH'],
            'Attribute' => [
                [
                    'type' => 'domain',
                    'category' => 'Network activity',
                    'value' => 'suspicious-domain.xyz',
                    'comment' => 'Known abusive domain',
                    'to_ids' => true
                ]
            ],
            'Tag' => [
                ['name' => 'type:OSINT'],
                ['name' => 'type:OTX'],
                ['name' => 'category:abuse'],
                ['name' => 'tlp:white']
            ]
        ],

        'c3d24e90-a113-4fa9-8e5e-e7b2bd5b6a77' => [
            'info' => 'OSINT - Malware C2 IPs',
            'date' => '2025-07-13',
            'threat_level_id' => '1',
            'analysis' => '2',
            'distribution' => '1',
            'published' => true,
            'Orgc' => ['name' => 'Threat inSights'],
            'Org' => ['name' => 'SOSECURE-TH'],
            'Attribute' => [
                [
                    'type' => 'ip-dst',
                    'category' => 'Payload delivery',
                    'value' => '103.56.207.55',
                    'comment' => 'C2 server for RAT',
                    'to_ids' => true
                ]
            ],
            'Tag' => [
                ['name' => 'type:OSINT'],
                ['name' => 'type:OTX'],
                ['name' => 'malware:rat'],
                ['name' => 'tlp:green']
            ]
        ],

        'd47f1907-62de-4e82-bdd6-78d0b99d21ef' => [
            'info' => 'OSINT - Phishing URL',
            'date' => '2025-07-13',
            'threat_level_id' => '2',
            'analysis' => '2',
            'distribution' => '1',
            'published' => false,
            'Orgc' => ['name' => 'Threat inSights'],
            'Org' => ['name' => 'SOSECURE-TH'],
            'Attribute' => [
                [
                    'type' => 'url',
                    'category' => 'Network activity',
                    'value' => 'http://login-facebook-check.tk/',
                    'comment' => 'Phishing campaign',
                    'to_ids' => true
                ]
            ],
            'Tag' => [
                ['name' => 'type:OSINT'],
                ['name' => 'type:OTX'],
                ['name' => 'category:phishing'],
                ['name' => 'tlp:amber']
            ]
        ],

        'e99ba347-758e-4cb3-9ed1-11a0b6d2d94e' => [
            'info' => 'OSINT - Leaked Emails',
            'date' => '2025-07-13',
            'threat_level_id' => '3',
            'analysis' => '2',
            'distribution' => '1',
            'published' => true,
            'Orgc' => ['name' => 'Threat inSights'],
            'Org' => ['name' => 'SOSECURE-TH'],
            'Attribute' => [
                [
                    'type' => 'email-dst',
                    'category' => 'Person',
                    'value' => 'john.doe@example.com',
                    'comment' => 'Exposed email from breach',
                    'to_ids' => true
                ]
            ],
            'Tag' => [
                ['name' => 'type:OSINT'],
                ['name' => 'type:OTX'],
                ['name' => 'data-leak'],
                ['name' => 'tlp:white']
            ]
        ],

        'f412ba10-80dc-4728-a8a8-96d1f87f84f0' => [
            'info' => 'OSINT - Suspicious Hashes',
            'date' => '2025-07-13',
            'threat_level_id' => '2',
            'analysis' => '2',
            'distribution' => '1',
            'published' => false,
            'Orgc' => ['name' => 'Threat inSights'],
            'Org' => ['name' => 'SOSECURE-TH'],
            'Attribute' => [
                [
                    'type' => 'sha256',
                    'category' => 'Payload delivery',
                    'value' => '3b5d5c3712955042212316173ccf37be8007bff7ac08beedf4c1e80a5fcf77df',
                    'comment' => 'Hash of malicious file',
                    'to_ids' => true
                ]
            ],
            'Tag' => [
                ['name' => 'type:OSINT'],
                ['name' => 'type:OTX'],
                ['name' => 'malware:generic'],
                ['name' => 'tlp:green']
            ]
        ]

    ];


    /**
     * Serve individual event as MISP JSON
     */
    public function generateJsonFeed_bkk($uuid)
    {
        if (!isset($this->feeds[$uuid])) {
            return response()->json(['error' => 'Feed not found'], 404);
        }

        $event = $this->feeds[$uuid];

        // เพิ่มค่าที่ MISP ต้องการให้อยู่ใน $event
        $event['uuid'] = $uuid;
        $event['threat_level_id'] = $event['threat_level_id'] ?? '2';
        $event['analysis'] = $event['analysis'] ?? '2';
        $event['distribution'] = $event['distribution'] ?? '1';
        $event['published'] = $event['published'] ?? false;
        $event['timestamp'] = strtotime($event['date']);

        // แทรก Orgc / Org ถ้ายังไม่มี
        $event['Orgc'] = $event['Orgc'] ?? ['name' => 'Threat inSights'];
        $event['Org'] = $event['Org'] ?? ['name' => 'SOSECURE-TH'];



        return response()->json([
            'Event' => $event
        ], 200, [], JSON_PRETTY_PRINT);
    }
    public function generateJsonFeed($uuid)
    {



        try {


            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $options = [
                'projection' => [
                    '_id' => 0,

                    'name' => 1,
                    'groups' => 1,
                    'tags' => 1,
                    'industries' => 1,
                    'public' => 1,
                    'is_modified' => 1,
                    'modified' => 1,
                    'count_view' => 1,
                    'indicator_count' => 1,
                    'pulse_id' => 1,
                    'creator_org' => 1,
                    'mips_uuid' => 1

                ],
                //'limit' => 10,
                'sort' => ['modified' => -1], // เรียงจากใหม่ไปเก่า (ถ้าต้องการ)
            ];

            $query = array(
                'status' => ['$in' => [1, "1", true]],
                '$or' => [
                    ['deleted_at' => null],
                    ['deleted_at' => ['$exists' => false]],
                ],
            );
            // วันที่เริ่มต้น: ย้อนหลัง 24 ชม. เป๊ะๆ (Rolling 24 Hours)
            $start = strtotime('-1 day') * 1000;


            // เวลาเริ่มของเมื่อวาน (00:00:00)
            // $start = strtotime('-3 days midnight') * 1000;

            // วันที่สิ้นสุด: เวลาปัจจุบัน
            $end = round(microtime(true) * 1000);

            $query['modified'] = [
                '$gt' => new UTCDateTime($start),
                '$lte' => new UTCDateTime($end)
            ];
            $query['mips_uuid'] = $uuid;
            $query['public'] = ['$in' => [1, "1", true]];
            $query['indicator_count'] = ['$ne' => 0];
            $cursor = $col_fx_otx_events->find($query, $options);
            $cursor = $cursor->toArray();


            $data = array();
            $order_number = $start;
            if (!empty($cursor)) {
                foreach ($cursor as $document) {

                    $mips_uuid = isset($document['mips_uuid']) ? $document['mips_uuid'] : null;


                    // กำหนด default timestamp (หรือจะ parse date เป็น timestamp ก็ได้)

                    $timestamp = $this->change_datetime_utc_to_thai_custom($document['modified']);
                    // จัดรูปแบบ tag ใหม่
                    $tagArray = [];
                    $tags = explode(',', $document['tags']);

                    foreach ($tags as $tag) {
                        if (trim($tag)) {
                            $tagArray[] = [
                                'name' => 'type:' . trim($tag), // เผื่อมีช่องว่าง
                                'colour' => '#004646',
                                'local' => false,
                                'relationship_type' => ''
                            ];
                        }
                    }

                    array_unshift($tagArray, [
                        'name' => 'OTX',
                        'colour' => '#004646',
                        'local' => false,
                        'relationship_type' => ''
                    ]);


                    // เพิ่มค่าที่ MISP ต้องการให้อยู่ใน $event
                    $event = [];
                    $event['uuid'] = $mips_uuid;

                    $event['threat_level_id'] = '3';
                    $event['analysis'] = '2';
                    $event['distribution'] = '';
                    $event['date'] = $this->change_date_utc_to_thai_custom($document['modified']);
                    $event['published'] = $document["public"];
                    $event['timestamp'] = $timestamp;
                    $event['publish_timestamp'] = $timestamp;


                    // แทรก Orgc / Org ถ้ายังไม่มี
                    $event['Orgc'] = ['name' => 'Threat inSights'];
                    $event['Org'] = ['name' => 'SOSECURE-TH'];
                    $event['Tag'] = $tagArray;
                    $event['info']  =  $document["name"] ?? '';
                    $event['pulse_id']  =  $document["pulse_id"];







                    $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                    $query = [
                        'pulse_id' => $document["pulse_id"],
                        'updated_at' => [
                            '$gt' => new UTCDateTime($start),
                            '$lte' => new UTCDateTime($end)
                        ]

                    ];



                    $options = [
                        'sort' => [
                            // $order => $dir
                        ],
                        // 'skip' => $start,
                        'sort' => ['updated_at' => -1], // เรียงจากใหม่ไปเก่า (ถ้าต้องการ)
                    ];
                    $event['Attribute'] = [];
                    $cursor_indicator = $col_fx_otx_events_indicator_ref->find($query, $options);
                    $cursor_indicator_document_all = $cursor_indicator->toArray();
                    $Array_indicator = [];
                    if (!empty($cursor_indicator_document_all)) {
                        foreach ($cursor_indicator_document_all as $cursor_indicator_data) {

                            // จัดรูปแบบ tag ใหม่ $cursor_indicator_data['tags'])
                            $tagArray_indicator = [];
                            $indicator_tags = explode(',',  isset($cursor_indicator_data['tags']) ? $cursor_indicator_data['tags'] : '');



                            foreach ($indicator_tags as $tag_indicator) {
                                if (trim($tag_indicator)) {
                                    $tagArray_indicator[] = [
                                        'name' => 'type:' . trim($tag_indicator), // เผื่อมีช่องว่าง
                                        'colour' => '#004646',
                                        'local' => false,
                                        'relationship_type' => '',

                                    ];
                                }
                            }

                            array_unshift($tagArray_indicator, [
                                'name' => 'type:OTX',
                                'colour' => '#004646',
                                'local' => false,
                                'relationship_type' => ''
                            ]);


                            $indicator_category = "Network activity";
                            $indicator_type = "ip-dst";
                            if (trim($cursor_indicator_data['type']) == "IPV4") {
                                $indicator_category = "Network activity";
                                $indicator_type = "ip-dst";
                            } else if (trim($cursor_indicator_data['type']) == "Domain" || trim($cursor_indicator_data['type']) == "domain") {
                                $indicator_category = "Network activity";
                                $indicator_type = "domain";
                            } else if (trim($cursor_indicator_data['type']) == "Url") {
                                $indicator_category = "Network activity";
                                $indicator_type = "url";
                            } else if (trim($cursor_indicator_data['type']) == "FileHash-MD5") {
                                $indicator_category = "Payload delivery";
                                $indicator_type = "md5";
                            } else if (trim($cursor_indicator_data['type']) == "FileHash-SHA1") {
                                $indicator_category = "Payload delivery";
                                $indicator_type = "sha1";
                            } else if (trim($cursor_indicator_data['type']) == "FileHash-SHA256") {
                                $indicator_category = "Payload delivery";
                                $indicator_type = "sha256";
                            } else if (trim($cursor_indicator_data['type']) == "Imphash") {
                                $indicator_category = "Payload delivery";
                                $indicator_type = "imphash";
                            } else if (trim($cursor_indicator_data['type']) == "Hostname") {
                                $indicator_category = "Network activity";
                                $indicator_type = "hostname";
                            } else if (trim($cursor_indicator_data['type']) == "Email") {
                                $indicator_category = "Phishing";
                                $indicator_type = "email-src";
                            }

                            // $indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail->findOne(['indicator_name' => trim($cursor_indicator_data['indicator'])]);
                            // $indicator_score = '';
                            // $indicator_severity = '';

                            // if ($indicator_detail && isset($indicator_detail['allrow']['Score'])) {
                            //     $indicator_score = $indicator_detail['allrow']['Score'];
                            //     // Map score to severity
                            //     if ($indicator_score >= 9) {
                            //         $indicator_severity = 'Critical';
                            //     } elseif ($indicator_score >= 7) {
                            //         $indicator_severity = 'High';
                            //     } elseif ($indicator_score >= 4) {
                            //         $indicator_severity = 'Medium';
                            //     } elseif ($indicator_score >= 2) {
                            //         $indicator_severity = 'Low';
                            //     } elseif ($indicator_score == 1) {
                            //         $indicator_severity = 'Very Low';
                            //     } else {
                            //         $indicator_severity = 'Information';
                            //     }
                            // }

                            $Array_indicator[] = [
                                'value' => trim($cursor_indicator_data['indicator']),
                                'type' => $indicator_type,
                                'local' => false,
                                'relationship_type' => '',
                                'category' => $indicator_category,
                                'Tag' => $tagArray_indicator,
                                // 'score' => $indicator_score,
                                // 'severity' => $indicator_severity,
                                'score' => isset($cursor_indicator_data['attribute_score']) ? $cursor_indicator_data['attribute_score'] : '',
                                'severity' => isset($cursor_indicator_data['attribute_serverity']) ? $cursor_indicator_data['attribute_serverity'] : '',
                                'to_ids' => true
                            ];
                        }
                    }




                    $event['Attribute'] = $Array_indicator;

                    // --- START LOGGING OUTBOUND DATA ---
                    try {
                        $logCol = $clientMD->sosecure_threatintelligent->fx_feed_published_logs;
                        $logType = 'misp_pull_event';
                        $todayRegex = '^' . date('Y-m-d');
                        $existingLog = $logCol->findOne(['type' => $logType, 'action_time' => ['$regex' => $todayRegex]]);

                        $mergedEvents = [];
                        if ($existingLog && isset($existingLog['events'])) {
                            foreach ($existingLog['events'] as $e) {
                                $mergedEvents[(string)$e['uuid']] = (array)$e;
                            }
                        }

                        // เพิ่ม Event ปัจจุบันเข้าไป (ถ้า uuid ซ้ำจะโดนอัปเดตเป็นล่าสุด)
                        $currentEvent = [
                            'uuid' => $mips_uuid,
                            'pulse_id' => $document["pulse_id"] ?? null,
                            'name' => $document["name"] ?? '',
                            'indicator_count' => count($Array_indicator),
                            'pulled_at' => date('Y-m-d H:i:s')
                        ];
                        $mergedEvents[(string)$mips_uuid] = $currentEvent;

                        $finalEvents = array_values($mergedEvents);
                        $finalTotalEvents = count($finalEvents);
                        
                        // คำนวณยอด indicators รวมจากทุก events ในวันนี้
                        $finalTotalIndicators = 0;
                        foreach ($finalEvents as $e) {
                            $finalTotalIndicators += (int)($e['indicator_count'] ?? 0);
                        }

                        $logCol->updateOne(
                            ['type' => $logType, 'action_time' => ['$regex' => $todayRegex]],
                            ['$set' => [
                                'timestamp' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000),
                                'action_time' => date('Y-m-d H:i:s'),
                                'type' => $logType,
                                'total_public_events' => $finalTotalEvents,
                                'total_indicators' => $finalTotalIndicators,
                                'events' => $finalEvents,
                                'source' => 'misp_controller_event'
                            ]],
                            ['upsert' => true]
                        );
                    } catch (\Exception $e) {
                        \Log::error("Failed to log feed access: " . $e->getMessage());
                    }
                    // --- END LOGGING OUTBOUND DATA ---

                    return response()->json([
                        'Event' => $event
                    ], 200, [], JSON_PRETTY_PRINT);
                }
            }

        } catch (\Throwable $e) {

            return response()->json(['error' => 'Unable to load feed data'], 500);
        }
    }


    /**
     * Generate a manifest listing all feeds
     */

    public function generateDirectoryManifest()
    {
        $eventList = [];

        foreach (array_keys($this->feeds) as $uuid) {
            $eventList[] = "$uuid.json";
        }

        return response()->json([
            "EventList" => $eventList
        ], 200, [], JSON_PRETTY_PRINT);
    }



    public function listFeedsForManifest()
    {
        // บันทึก Log เป็นโหมด Manifest สำหรับ URL .json
        Artisan::call('app:AuditPublishedFeeds', ['--mode' => 'manifest']);
        return $this->processListFeeds();
    }

    public function listFeeds()
    {
        // บันทึก Log เป็นโหมด List สำหรับหน้าเว็บทั่วไป
        Artisan::call('app:AuditPublishedFeeds', ['--mode' => 'list']);
        return $this->processListFeeds();
    }

    private function processListFeeds()
    {
        try {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $options = [
                'projection' => [
                    '_id' => 0,
                    'name' => 1,
                    'groups' => 1,
                    'tags' => 1,
                    'industries' => 1,
                    'public' => 1,
                    'is_modified' => 1,
                    'modified' => 1,
                    'count_view' => 1,
                    'indicator_count' => 1,
                    'pulse_id' => 1,
                    'creator_org' => 1,
                    'mips_uuid' => 1
                ],
                'sort' => ['modified' => -1],
            ];

            $query = array(
                'status' => ['$in' => [1, "1", true]],
                '$or' => [
                    ['deleted_at' => null],
                    ['deleted_at' => ['$exists' => false]],
                ],
            );
            
            // วันที่เริ่มต้น: ย้อนหลัง 24 ชม. เป๊ะๆ (Rolling 24 Hours)
            $start = strtotime('-1 day') * 1000;
            $end = round(microtime(true) * 1000);

            $query['modified'] = [
                '$gt' => new UTCDateTime($start),
                '$lte' => new UTCDateTime($end)
            ];
            
            $query['public'] = ['$in' => [1, "1", true]];
            $query['creator_org'] = "OTX";
            $query['indicator_count'] = ['$ne' => 0];
            $cursor = $col_fx_otx_events->find($query, $options);
            $cursor = $cursor->toArray();

            $feeds = [];
            if (!empty($cursor)) {
                foreach ($cursor as $document) {
                    $mips_uuid = isset($document['mips_uuid']) ? $document['mips_uuid'] : null;
                    if (!$mips_uuid) {
                        $mips_uuid = $this->generate_uuid_v4();
                        $col_fx_otx_events->updateOne(
                            ['pulse_id' => $document["pulse_id"]],
                            ['$set' => ['mips_uuid' => $mips_uuid]]
                        );
                    }

                    $timestamp = $this->change_datetime_utc_to_thai_custom($document['modified']);
                    $tagArray = [];
                    $tags = explode(',', $document['tags']);

                    foreach ($tags as $tag) {
                        if (trim($tag)) {
                            $tagArray[] = [
                                'name' => trim($tag),
                                'colour' => '#004646',
                                'local' => false,
                                'relationship_type' => ''
                            ];
                        }
                    }

                    array_unshift($tagArray, [
                        'name' => 'OTX',
                        'colour' => '#004646',
                        'local' => false,
                        'relationship_type' => ''
                    ]);

                    $feeds[$mips_uuid] = [
                        'Org' => ['name' => 'SOSECURE-TH'],
                        'Orgc' => ['name' => 'SOSECURE-TH'],
                        'Tag' => $tagArray,
                        'info' =>  $document["name"] ?? '',
                        'date' => $this->change_date_utc_to_thai_custom($document['modified']),
                        'analysis' => 2,
                        'threat_level_id' => '',
                        'timestamp' => $timestamp,
                    ];
                }
            } else {
                $feeds = ['No Data'];
            }

            // --- START LOGGING OUTBOUND MANIFEST ---
            try {
                $logCol = $clientMD->sosecure_threatintelligent->fx_feed_published_logs;
                $logType = 'misp_pull_manifest';
                $todayRegex = '^' . date('Y-m-d');
                $existingLog = $logCol->findOne(['type' => $logType, 'action_time' => ['$regex' => $todayRegex]]);

                $mergedEvents = [];
                if ($existingLog && isset($existingLog['events'])) {
                    foreach ($existingLog['events'] as $e) {
                        $uuidKey = $e['uuid'] ?? ($e['mips_uuid'] ?? null);
                        if ($uuidKey) {
                            $mergedEvents[(string)$uuidKey] = (array)$e;
                        }
                    }
                }

                // นำข้อมูล manifest รอบล่าสุดไปรวม
                foreach ($feeds as $uuid => $m) {
                    if ($uuid === 'No Data' || $uuid === 0) continue;
                    $mergedEvents[(string)$uuid] = [
                        'uuid' => $uuid,
                        'info' => $m['info'] ?? '',
                        'published' => 1
                    ];
                }

                $finalEvents = array_values($mergedEvents);
                $finalTotalEvents = count($finalEvents);

                $logCol->updateOne(
                    ['type' => $logType, 'action_time' => ['$regex' => $todayRegex]],
                    ['$set' => [
                        'timestamp' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000),
                        'action_time' => date('Y-m-d H:i:s'),
                        'type' => $logType,
                        'total_public_events' => $finalTotalEvents,
                        'events' => $finalEvents,
                        'source' => 'misp_controller_manifest'
                    ]],
                    ['upsert' => true]
                );
            } catch (\Exception $e) {
                \Log::error("Failed to log feed access (manifest processListFeeds): " . $e->getMessage());
            }
            // --- END LOGGING OUTBOUND MANIFEST ---

            return response()->json($feeds, 200, [], JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unable to load feed data'], 500);
        }
    }





    /**
     * Generate per-feed manifest
     */
    public function generateManifest($uuid)
    {
        if (!isset($this->feeds[$uuid])) {
            return response()->json(['error' => 'No Data'], 404);
        }

        $feed = $this->feeds[$uuid];

        return response()->json([
            "EventList" => [
                $uuid . ".json"
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }
    function generate_uuid_v4()
    {
        // สร้างค่ารandom 16 bytes
        $data = openssl_random_pseudo_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // แปลงเป็น UUID format
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    function change_date_utc_to_thai_custom($utcDateTime)
    {
        if ($utcDateTime instanceof \MongoDB\BSON\UTCDateTime) {
            $datetime = $utcDateTime->toDateTime();
        } else {
            $datetime = new \DateTime($utcDateTime);
        }

        // แปลงเวลาเป็น Asia/Bangkok (UTC+7)
        $datetime->setTimezone(new \DateTimeZone('Asia/Bangkok'));

        return $datetime->format('Y-m-d'); // หรือ return $datetime->getTimestamp(); สำหรับ timestamp
    }
    function change_datetime_utc_to_thai_custom($utcDateTime)
    {
        if ($utcDateTime instanceof \MongoDB\BSON\UTCDateTime) {
            $datetime = $utcDateTime->toDateTime();
        } else {
            $datetime = new \DateTime($utcDateTime);
        }

        // แปลงเวลาเป็น Asia/Bangkok (UTC+7)
        $datetime->setTimezone(new \DateTimeZone('Asia/Bangkok'));

        return $datetime->getTimestamp();
    }


    public function generateToken(Request $request)
    {
        $name = $request->input('name', 'Feed Token');

        // ลองอ่าน expires_at ก่อน ถ้าไม่มีลองใช้ days
        $expiresAt = $request->input('expires_at') ?: $request->input('days');
        $siteId    = $request->site;
        // dd($siteId);

        $siteId = $request->attributes->get('site_id')
            ?? $request->input('site_id')
            ?? $request->input('site'); // เผื่อคุณส่งชื่อฟิลด์นี้มา

        // (ถ้ารับจาก <input type="datetime-local"> จะเป็นรูป 2025-09-10T14:30)
        if ($expiresAt && strpos($expiresAt, 'T') !== false) {
            $expiresAt = str_replace('T', ' ', $expiresAt);
            if (strlen($expiresAt) === 16) {
                $expiresAt .= ':00';
            } // เติมวินาที
        }

        do {
            $plain = Str::random(60);
        } while (ApiToken::where('token', $plain)->exists());

        $token = ApiToken::create([
            'name'       => $name . ($expiresAt ? ' (Expires: ' . $expiresAt . ')' : ''),
            'token'      => $plain,
            'expires_at' => $expiresAt ?: null,
            'site_id'    => $siteId ?: null,
        ]);

        return response()->json([
            'id'         => $token->id,
            'name'       => $token->name,
            'token'      => $plain,
            'expires_at' => $token->expires_at,
        ], 201);
    }

    public function manifest(Request $request)
    {
        Artisan::call('app:AuditPublishedFeeds', ['--mode' => 'manifest']);
        
        try {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            if (empty($DB_MONGO_KEY)) {
                Log::error('FEED manifest: DB_MONGO_DEV missing');
                return response()->json(['error' => 'Feed backend misconfigured'], 500);
            }

            $client = new \MongoDB\Client($DB_MONGO_KEY);
            $col    = $client->sosecure_threatintelligent->fx_otx_events;

            // ช่วงเวลาเอาเป๊ะๆ 24 ชม. ย้อนหลัง
            $startMs = strtotime('-1 day') * 1000;
            $endMs   = (int) round(microtime(true) * 1000);

            // เงื่อนไขยืดหยุ่นกัน type (1/true) และ deleted_at ไม่มีฟิลด์
            $query = [
                'status'          => ['$in' => [1, "1", true]],
                '$or'             => [['deleted_at' => null], ['deleted_at' => ['$exists' => false]]],
                'public'          => ['$in' => [1, "1", true]],
                'indicator_count' => ['$gt' => 0],
                'modified'        => [
                    '$gte' => new \MongoDB\BSON\UTCDateTime($startMs),
                    '$lte' => new \MongoDB\BSON\UTCDateTime($endMs),
                ],
                'creator_org'     => 'OTX',
            ];

            $options = [
                'projection' => [
                    '_id' => 0,
                    'name' => 1,
                    'public' => 1,
                    'modified' => 1,
                    'pulse_id' => 1,
                    'mips_uuid' => 1,
                    'tags' => 1,
                ],
                'sort' => ['modified' => -1],
                'limit' => 500,
            ];

            $docs = $col->find($query, $options)->toArray();

            // manifest ของ MISP: แนะนำให้เป็น object ที่ key เป็น "<uuid>.json"
            $manifest = [];
            foreach ($docs as $d) {
                $uuid = $d['mips_uuid'] ?? null;
                if (!$uuid) continue;

                $ts = $this->change_datetime_utc_to_thai_custom($d['modified'] ?? null) ?? time();

                $manifest["{$uuid}.json"] = [
                    'uuid'      => $uuid,
                    'path'      => "{$uuid}.json",
                    'timestamp' => $ts,
                    'info'      => (string)($d['name'] ?? ''),
                    'published' => (int)($d['public'] ?? 0),
                    // จะใส่ sha256/size ถ้ามีที่มา ก็เพิ่มได้
                ];
            }

            // --- START LOGGING OUTBOUND MANIFEST ---
            try {
                $logCol = $client->sosecure_threatintelligent->fx_feed_published_logs;
                $logType = 'misp_pull_manifest';
                $todayRegex = '^' . date('Y-m-d');
                $existingLog = $logCol->findOne(['type' => $logType, 'action_time' => ['$regex' => $todayRegex]]);

                $mergedEvents = [];
                if ($existingLog && isset($existingLog['events'])) {
                    foreach ($existingLog['events'] as $e) {
                        $mergedEvents[(string)$e['uuid']] = (array)$e;
                    }
                }

                // นำข้อมูล manifest รอบล่าสุดไปรวม
                foreach ($manifest as $m) {
                    $mergedEvents[(string)$m['uuid']] = $m;
                }

                $finalEvents = array_values($mergedEvents);
                $finalTotalEvents = count($finalEvents);

                $logCol->updateOne(
                    ['type' => $logType, 'action_time' => ['$regex' => $todayRegex]],
                    ['$set' => [
                        'timestamp' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000),
                        'action_time' => date('Y-m-d H:i:s'),
                        'type' => $logType,
                        'total_public_events' => $finalTotalEvents,
                        'events' => $finalEvents,
                        'source' => 'misp_controller_manifest'
                    ]],
                    ['upsert' => true]
                );
            } catch (\Exception $e) {
                \Log::error("Failed to log feed access (manifest): " . $e->getMessage());
            }
            // --- END LOGGING OUTBOUND MANIFEST ---

            return response()->json($manifest, 200, [], JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            Log::error('FEED manifest failed', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['error' => 'Unable to load manifest'], 500);
        }
    }

    public function event(Request $request, string $uuid)
    {
        try {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            if (empty($DB_MONGO_KEY)) {
                Log::error('FEED event: DB_MONGO_DEV missing');
                return response()->json(['error' => 'Feed backend misconfigured'], 500);
            }

            $client = new \MongoDB\Client($DB_MONGO_KEY);

            // 1) ดึงหัว event ตาม uuid
            $events = $client->sosecure_threatintelligent->fx_otx_events;
            $startMs = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day'))) * 1000; // เผื่อย้อนหลัง 7 วัน
            $endMs   = (int) round(microtime(true) * 1000);

            $qEvent = [
                'status'          => ['$in' => [1, true]],
                '$or'             => [['deleted_at' => null], ['deleted_at' => ['$exists' => false]]],
                'public'          => ['$in' => [1, true]],
                'indicator_count' => ['$gt' => 0],
                'mips_uuid'       => $uuid,
                'modified'        => [
                    '$gte' => new \MongoDB\BSON\UTCDateTime($startMs),
                    '$lte' => new \MongoDB\BSON\UTCDateTime($endMs),
                ],
            ];

            $optEvent = [
                'projection' => [
                    '_id' => 0,
                    'name' => 1,
                    'tags' => 1,
                    'public' => 1,
                    'indicator_count' => 1,
                    'pulse_id' => 1,
                    'mips_uuid' => 1,
                    'modified' => 1,
                ],
                'sort' => ['modified' => -1],
                'limit' => 1,
            ];

            $doc = $events->findOne($qEvent, $optEvent);
            if (!$doc) {
                return response()->json(['error' => 'Feed not found'], 404);
            }

            // 2) แปลงเป็นโครง MISP Event
            $timestamp = $this->change_datetime_utc_to_thai_custom($doc['modified'] ?? null) ?? time();

            // Tag ของ Event
            $tagsRaw = (string)($doc['tags'] ?? '');
            $tags = array_filter(array_map('trim', explode(',', $tagsRaw)));
            $tagArray = [[
                'name' => 'OTX',
                'colour' => '#004646',
                'local' => false,
                'relationship_type' => '',
            ]];
            foreach ($tags as $t) {
                $tagArray[] = ['name' => 'type:' . $t, 'colour' => '#004646', 'local' => false, 'relationship_type' => ''];
            }

            $event = [
                'uuid'              => $uuid,
                'info'              => (string)($doc['name'] ?? ''),
                'date'              => $this->change_date_utc_to_thai_custom($doc['modified'] ?? null) ?? date('Y-m-d'),
                'published'         => (bool)($doc['public'] ?? false),
                'threat_level_id'   => '3',
                'analysis'          => '2',
                'distribution'      => '1',
                'timestamp'         => $timestamp,
                'publish_timestamp' => $timestamp,
                'Orgc'              => ['name' => 'Threat inSights'],
                'Org'               => ['name' => 'SOSECURE-TH'],
                'Tag'               => $tagArray,
                'Attribute'         => [],
            ];

            // 3) ดึง indicators
            $indCol = $client->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $qInd = [
                'pulse_id'   => $doc['pulse_id'] ?? null,
                'updated_at' => [
                    '$gte' => new \MongoDB\BSON\UTCDateTime($startMs),
                    '$lte' => new \MongoDB\BSON\UTCDateTime($endMs),
                ],
            ];
            $indDocs = $indCol->find($qInd, ['sort' => ['updated_at' => -1]])->toArray();

            $map = [
                'IPV4'            => ['Network activity', 'ip-dst'],
                'Domain'          => ['Network activity', 'domain'],
                'domain'          => ['Network activity', 'domain'],
                'Url'             => ['Network activity', 'url'],
                'FileHash-MD5'    => ['Payload delivery', 'md5'],
                'FileHash-SHA1'   => ['Payload delivery', 'sha1'],
                'FileHash-SHA256' => ['Payload delivery', 'sha256'],
                'Imphash'         => ['Payload delivery', 'imphash'],
                'Hostname'        => ['Network activity', 'hostname'],
                'Email'           => ['Phishing', 'email-src'],
            ];

            $attributes = [];
            foreach ($indDocs as $r) {
                $typeStr = trim((string)($r['type'] ?? ''));
                [$cat, $typ] = $map[$typeStr] ?? ['Network activity', 'text'];

                $indTagsRaw = (string)($r['tags'] ?? '');
                $indTagArray = [[
                    'name' => 'type:OTX',
                    'colour' => '#004646',
                    'local' => false,
                    'relationship_type' => ''
                ]];
                foreach (array_filter(array_map('trim', explode(',', $indTagsRaw))) as $it) {
                    $indTagArray[] = ['name' => 'type:' . $it, 'colour' => '#004646', 'local' => false, 'relationship_type' => ''];
                }

                $indicator_detail = $client->sosecure_threatintelligent->fx_otx_indicator_detail->findOne(['indicator_name' => trim((string)($r['indicator'] ?? ''))]);
                $indicator_score = '';
                $indicator_severity = '';

                if ($indicator_detail && isset($indicator_detail['allrow']['Score'])) {
                    $indicator_score = $indicator_detail['allrow']['Score'];
                    if ($indicator_score >= 9) {
                        $indicator_severity = 'Critical';
                    } elseif ($indicator_score >= 7) {
                        $indicator_severity = 'High';
                    } elseif ($indicator_score >= 4) {
                        $indicator_severity = 'Medium';
                    } elseif ($indicator_score >= 2) {
                        $indicator_severity = 'Low';
                    } elseif ($indicator_score == 1) {
                        $indicator_severity = 'Very Low';
                    } else {
                        $indicator_severity = 'Information';
                    }
                }

                $attributes[] = [
                    'value' => trim((string)($r['indicator'] ?? '')),
                    'type'  => $typ,
                    'category' => $cat,
                    'to_ids' => true,
                    'local'  => false,
                    'relationship_type' => '',
                    'Tag'   => $indTagArray,
                    'score' => $indicator_score,
                    'severity' => $indicator_severity,
                ];
            }

            $event['Attribute'] = $attributes;

            // --- START LOGGING OUTBOUND DATA ---
            try {
                $logCol = $client->sosecure_threatintelligent->fx_feed_published_logs;
                $logType = 'misp_pull_event';
                $todayRegex = '^' . date('Y-m-d');
                $existingLog = $logCol->findOne(['type' => $logType, 'action_time' => ['$regex' => $todayRegex]]);

                $mergedEvents = [];
                if ($existingLog && isset($existingLog['events'])) {
                    foreach ($existingLog['events'] as $e) {
                        $mergedEvents[(string)$e['uuid']] = (array)$e;
                    }
                }

                // เพิ่ม Event ปัจจุบันเข้าไป (ถ้า uuid ซ้ำจะโดนอัปเดตเป็นล่าสุด)
                $currentEvent = [
                    'uuid' => $uuid,
                    'pulse_id' => $doc['pulse_id'] ?? null,
                    'name' => $doc['name'] ?? '',
                    'indicator_count' => count($attributes),
                    'pulled_at' => date('Y-m-d H:i:s')
                ];
                $mergedEvents[(string)$uuid] = $currentEvent;

                $finalEvents = array_values($mergedEvents);
                $finalTotalEvents = count($finalEvents);
                
                // คำนวณยอด indicators รวมจากทุก events ในวันนี้
                $finalTotalIndicators = 0;
                foreach ($finalEvents as $e) {
                    $finalTotalIndicators += (int)($e['indicator_count'] ?? 0);
                }

                $logCol->updateOne(
                    ['type' => $logType, 'action_time' => ['$regex' => $todayRegex]],
                    ['$set' => [
                        'timestamp' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000),
                        'action_time' => date('Y-m-d H:i:s'),
                        'type' => $logType,
                        'total_public_events' => $finalTotalEvents,
                        'total_indicators' => $finalTotalIndicators,
                        'events' => $finalEvents,
                        'source' => 'misp_controller_event'
                    ]],
                    ['upsert' => true]
                );
            } catch (\Exception $e) {
                \Log::error("Failed to log feed access (event): " . $e->getMessage());
            }
            // --- END LOGGING OUTBOUND DATA ---

            return response()->json(['Event' => $event], 200, [], JSON_PRETTY_PRINT);

        } catch (\Throwable $e) {
            Log::error('FEED event failed', ['uuid' => $uuid, 'msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['error' => 'Unable to load feed data'], 500);
        }
    }
}
