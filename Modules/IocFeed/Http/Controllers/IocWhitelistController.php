<?php

namespace Modules\IocFeed\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;
use Log;

class IocWhitelistController extends Controller
{
    protected $client;
    protected $dbName;
    protected $whitelistColl;
    protected $feedColl;

    public function __construct()
    {
        $mongo_uri = config('app.DB_MONGO');
        $this->client = new Client($mongo_uri);
        $this->dbName = config('iocfeed.mongodb.database', 'sosecure_threatintelligent');
        $this->whitelistColl = $this->client->{$this->dbName}->fx_ioc_whitelist;
        $this->feedColl = $this->client->{$this->dbName}->fx_ioc_feeds;
    }

    /**
     * ค้นหารายการ Whitelist ทั้งหมด (พร้อมการค้นหาและแบ่งหน้า)
     */
    public function index(Request $request)
    {
        try {
            Log::channel('ioc_feed')->info("IoC Whitelist Access [GET]: Listing whitelists. Client IP: " . $request->ip());
            $query = [];
            
            // ค้นหาตาม type (เช่น ip_address, domain)
            if ($request->has('type') && !empty($request->type)) {
                $query['type'] = $request->type;
            }

            // ค้นหาตาม indicator (เช่น 1.1.1.1)
            if ($request->has('indicator') && !empty($request->indicator)) {
                $query['indicator'] = ['$regex' => $request->indicator, '$options' => 'i'];
            }

            $options = [
                'sort' => ['created_at' => -1]
            ];

            // Pagination แบบง่ายๆ
            $limit = (int) $request->input('limit', 50);
            $page = max(1, (int) $request->input('page', 1));
            $options['limit'] = $limit;
            $options['skip'] = ($page - 1) * $limit;

            $total = $this->whitelistColl->countDocuments($query);
            $documents = $this->whitelistColl->find($query, $options)->toArray();

            $data = [];
            foreach ($documents as $doc) {
                $data[] = [
                    'id' => (string) $doc['_id'],
                    'indicator' => $doc['indicator'] ?? '-',
                    'type' => $doc['type'] ?? '-',
                    'reason' => $doc['reason'] ?? '',
                    'client_ip' => $doc['client_ip'] ?? '-',
                    'created_by' => $doc['created_by'] ?? 'System',
                    'created_at' => isset($doc['created_at']) ? $doc['created_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('IocWhitelistController@index Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error: '.$e->getMessage()], 500);
        }
    }

    /**
     * เพิ่มเข้า Whitelist และลบออกจากตาราง Export (Real-time Filtering)
     */
    public function store(Request $request)
    {
        try {
            Log::channel('ioc_feed')->info("IoC Whitelist Access [POST]: Adding new whitelist. Client IP: " . $request->ip());
            
            $hasItems = $request->has('items') && is_array($request->input('items'));
            
            if ($hasItems) {
                $request->validate([
                    'items.*.indicator' => 'required|string',
                    'items.*.type' => 'required|string',
                    'items.*.reason' => 'nullable|string'
                ]);
                $items = $request->input('items');
            } else {
                $request->validate([
                    'indicator' => 'required|string',
                    'type' => 'required|string',
                    'reason' => 'nullable|string'
                ]);
                $items = [$request->only(['indicator', 'type', 'reason'])];
            }

            $apiToken = $request->attributes->get('api_token');
            $createdBy = $apiToken ? "Token: " . $apiToken->name : ($request->user() ? $request->user()->name : ($request->input('created_by') ?? 'Anonymous'));
            
            $bulkOps = [];
            $txBatch = [];
            $indicatorsByType = [];

            foreach ($items as $item) {
                $indicator = trim($item['indicator']);
                $type = trim($item['type']);
                $reason = trim($item['reason'] ?? '');

                // เตรียมคำสั่ง Upsert สำหรับ Whitelist (ถ้ามีแล้วก็ช่างมัน ถ้าไม่มีก็เพิ่มใหม่)
                $bulkOps[] = [
                    'updateOne' => [
                        ['indicator' => $indicator, 'type' => $type],
                        ['$set' => [
                            'indicator' => $indicator,
                            'type' => $type,
                            'reason' => $reason,
                            'client_ip' => $request->ip(),
                            'created_by' => $createdBy,
                        ], '$setOnInsert' => [
                            'created_at' => new UTCDateTime()
                        ]],
                        ['upsert' => true]
                    ]
                ];

                // จัดกลุ่ม Indicator ตาม Type เพื่อไป Flag ข้อมูลใน feedColl รวดเดียว
                if (!isset($indicatorsByType[$type])) {
                    $indicatorsByType[$type] = [];
                }
                $indicatorsByType[$type][] = $indicator;

                // เตรียมข้อมูล Transaction Log
                $txBatch[] = [
                    'indicator' => $indicator,
                    'type' => $type,
                    'action' => 'REMOVE',
                    'reason' => 'Moved to Whitelist (' . $reason . ')',
                    'created_at' => new UTCDateTime()
                ];
            }

            if (!empty($bulkOps)) {
                $this->whitelistColl->bulkWrite($bulkOps);
                
                // 3. Real-time Flagging: วิ่งไปติดป้ายกากบาทให้ข้อมูลในตารางส่งออกทันที! (แยกตาม Type)
                $totalFlagged = 0;
                foreach ($indicatorsByType as $type => $inds) {
                    // แบ่ง Chunk การ Update ทีละ 5000 เพื่อป้องกัน Array ใหญ่เกินขีดจำกัด MongoDB
                    $chunks = array_chunk($inds, 5000);
                    foreach ($chunks as $chunk) {
                        $updateRes = $this->feedColl->updateMany(
                            [
                                'indicator' => ['$in' => $chunk],
                                'type' => $type
                            ],
                            ['$set' => ['is_whitelisted' => true]]
                        );
                        $totalFlagged += $updateRes->getModifiedCount();
                    }
                }

                // 📝 บันทึกประวัติการคัดออก (REMOVE)
                if (!empty($txBatch)) {
                    $txColl = $this->client->{$this->dbName}->fx_ioc_feed_transactions;
                    $txColl->insertMany($txBatch);
                }

                // 📝 สั่ง Rebuild CSV ใหม่ทันที
                \Artisan::call('ioc-feed:update', ['--export-only' => true]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added/updated ' . count($items) . ' items in whitelist. ' . $totalFlagged . ' cached records flagged.',
                ], 201);
            }

            return response()->json(['status' => 'error', 'message' => 'No items to process'], 400);

        } catch (\Exception $e) {
            Log::error('IocWhitelistController@store Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error: '.$e->getMessage()], 500);
        }
    }

    /**
     * อัปโหลดไฟล์ CSV เพื่อเพิ่ม Whitelist จำนวนมาก
     */
    public function upload(Request $request)
    {
        try {
            Log::channel('ioc_feed')->info("IoC Whitelist Access [POST/Upload]: Bulk uploading whitelists. Client IP: " . $request->ip());
            $validator = \Validator::make($request->all(), [
                'file' => 'required|file',
                'type' => 'required|string',
                'reason' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                Log::error('IocWhitelistController@upload Validation Failed: ', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'txt'])) {
                return response()->json(['status' => 'error', 'message' => 'The file must be a file of type: csv, txt.'], 422);
            }

            $type = trim($request->type);
            $reason = trim($request->reason);
            $apiToken = $request->attributes->get('api_token');
            $createdBy = $apiToken ? "Token: " . $apiToken->name : ($request->user() ? $request->user()->name : ($request->input('created_by') ?? 'Anonymous'));

            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), "r");
            
            $indicators = [];
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!empty($data[0])) {
                    $indicatorName = trim(preg_replace('/[\xEF\xBB\xBF]/', '', $data[0])); // Strip BOM if present
                    // Skip header rows if they exist
                    if ($indicatorName !== '' && strtolower($indicatorName) !== 'indicator' && strtolower($indicatorName) !== 'ip') {
                        $indicators[] = $indicatorName;
                    }
                }
            }
            fclose($handle);

            if (empty($indicators)) {
                return response()->json(['status' => 'error', 'message' => 'No valid indicators found in file'], 400);
            }

            $indicators = array_unique($indicators);

            $insertData = [];
            foreach ($indicators as $ind) {
                // ตรวจสอบตัวซ้ำใน DB
                $exists = $this->whitelistColl->findOne(['indicator' => $ind, 'type' => $type]);
                if (!$exists) {
                    $insertData[] = [
                        'indicator' => $ind,
                        'type' => $type,
                        'reason' => $reason,
                        'client_ip' => $clientIp,
                        'created_by' => $createdBy,
                        'created_at' => new UTCDateTime()
                    ];
                }
            }

            if (!empty($insertData)) {
                $this->whitelistColl->insertMany($insertData);
            }

            // Real-time Flagging: ลบทิ้งไม่ได้แล้ว ต้องใช้การป้ายสี (Flag) ว่าไอพีกลุ่มนี้โดนแบน!
            $updateRes = $this->feedColl->updateMany(
                [
                    'indicator' => ['$in' => array_values($indicators)],
                    'type' => $type
                ],
                ['$set' => ['is_whitelisted' => true]]
            );

            // 📝 บันทึกประวัติการคัดออก (REMOVE) จากการอัปโหลดไฟล์
            $txColl = $this->client->{$this->dbName}->fx_ioc_feed_transactions;
            $txBatch = [];
            foreach ($indicators as $ind) {
                $txBatch[] = [
                    'indicator' => $ind,
                    'type' => $type,
                    'action' => 'REMOVE',
                    'reason' => 'Moved to Whitelist via CSV Upload (' . $reason . ')',
                    'created_at' => new UTCDateTime()
                ];
            }
            if (!empty($txBatch)) {
                $txColl->insertMany($txBatch);
            }

            // 📝 สั่ง Rebuild CSV ใหม่ทันที เพื่อไม่ต้องรอ Cronjob
            \Artisan::call('ioc-feed:update', ['--export-only' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully processed ' . count($indicators) . ' indicators. Added ' . count($insertData) . ' new to whitelist. Flagged ' . $updateRes->getModifiedCount() . ' cached records.',
                'total_processed' => count($indicators),
                'newly_added' => count($insertData),
                'cached_flagged' => $updateRes->getModifiedCount()
            ]);

        } catch (\Exception $e) {
            Log::error('IocWhitelistController@upload Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error: '.$e->getMessage()], 500);
        }
    }

    /**
     * ลบออกจาก Whitelist (ปลดแบน)
     */
    public function destroy($id)
    {
        try {
            Log::channel('ioc_feed')->info("IoC Whitelist Access [DELETE]: Removing whitelist ID or IP {$id}. Client IP: " . request()->ip());
            $indicatorStr = '';

            // ตรวจสอบว่า $id ที่ส่งมาคือ MongoDB ObjectId หรือเป็นชื่อ IP ตรงๆ
            if (preg_match('/^[a-f\d]{24}$/i', $id)) {
                $query = ['_id' => new ObjectId($id)];
                // ต้องหาชื่อ Indicator จะได้เอาไปปลดแบนในคลังส่งออกได้ถูกตัว
                $doc = $this->whitelistColl->findOne($query);
                if ($doc) {
                    $indicatorStr = $doc['indicator'] ?? '';
                }
            } else {
                // ถ้าไม่ใช่ ID แปลว่าต้องเป็นชื่อ IP/Domain ชัวร์ (ให้ Decode เผื่อมีอักขระพิเศษติดมากับ URL)
                $indicatorStr = urldecode($id);
                $query = ['indicator' => $indicatorStr];
            }

            // สั่งลบข้อมูลออกจากตาราง Whitelist จริงๆ (ลบกากบาทตัวแม่)
            $result = $this->whitelistColl->deleteMany($query);

            if ($result->getDeletedCount() > 0) {
                // Real-time Unflagging: วิ่งไป "ปลดป้ายแบน" ในตาราง Export คืนความโปร่งใส!
                if ($indicatorStr !== '') {
                    $this->feedColl->updateMany(
                        ['indicator' => $indicatorStr],
                        ['$set' => [
                            'is_whitelisted' => false,
                            'timestamp_val' => time(), // Reset timestamp so it appears as "just updated"
                            'sending_timestamp' => date('d/m/Y H:i'),
                            'updated_at' => new UTCDateTime()
                        ]]
                    );

                    // 📝 บันทึกประวัติการปลดแบน (UN-WHITELIST)
                    $txColl = $this->client->{$this->dbName}->fx_ioc_feed_transactions;
                    
                    // หา type ของ indicator ก่อนเพื่อความแม่นยำ
                    $feedDoc = $this->feedColl->findOne(['indicator' => $indicatorStr]);
                    $type = $feedDoc ? ($feedDoc['type'] ?? 'ip_address') : 'ip_address';

                    $txColl->insertOne([
                        'indicator' => $indicatorStr,
                        'type' => $type,
                        'action' => 'UN-WHITELIST',
                        'reason' => 'Removed from Whitelist manually',
                        'created_at' => new UTCDateTime()
                    ]);
                }

                // 📝 สั่ง Rebuild CSV ใหม่ทันที เพื่อไม่ต้องรอ Cronjob
                \Artisan::call('ioc-feed:update', ['--export-only' => true]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully removed ' . $result->getDeletedCount() . ' matching item(s) from whitelist and unflagged from cached.'
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Whitelist record not found'], 404);

        } catch (\Exception $e) {
            Log::error('IocWhitelistController@destroy Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Invalid ID or Internal error: '.$e->getMessage()], 500);
        }
    }
    /**
     * ลบออกจาก Whitelist (ปลดแบน) ทีละหลายตัวแบบ Bulk
     */
    public function bulkDestroy(Request $request)
    {
        try {
            Log::channel('ioc_feed')->info("IoC Whitelist Access [DELETE]: Bulk removing whitelists. Client IP: " . $request->ip());
            
            $hasItems = $request->has('items') && is_array($request->input('items'));
            
            if (!$hasItems) {
                return response()->json(['status' => 'error', 'message' => 'Missing items array'], 400);
            }

            $request->validate([
                'items.*.indicator' => 'required|string',
                'items.*.type' => 'required|string'
            ]);
            
            $items = $request->input('items');
            $indicatorsByType = [];
            $txBatch = [];

            foreach ($items as $item) {
                $indicator = trim($item['indicator']);
                $type = trim($item['type']);
                
                if (!isset($indicatorsByType[$type])) {
                    $indicatorsByType[$type] = [];
                }
                $indicatorsByType[$type][] = $indicator;

                $txBatch[] = [
                    'indicator' => $indicator,
                    'type' => $type,
                    'action' => 'UN-WHITELIST',
                    'reason' => 'Removed from Whitelist via Bulk API',
                    'created_at' => new UTCDateTime()
                ];
            }

            $totalDeleted = 0;
            $totalUnflagged = 0;

            foreach ($indicatorsByType as $type => $inds) {
                $chunks = array_chunk($inds, 5000);
                foreach ($chunks as $chunk) {
                    // 1. ลบออกจากตาราง Whitelist
                    $delRes = $this->whitelistColl->deleteMany([
                        'indicator' => ['$in' => $chunk],
                        'type' => $type
                    ]);
                    $totalDeleted += $delRes->getDeletedCount();

                    // 2. ปลดป้ายแบนในตาราง Export
                    $updateRes = $this->feedColl->updateMany(
                        [
                            'indicator' => ['$in' => $chunk],
                            'type' => $type
                        ],
                        ['$set' => [
                            'is_whitelisted' => false,
                            'timestamp_val' => time(),
                            'sending_timestamp' => date('d/m/Y H:i'),
                            'updated_at' => new UTCDateTime()
                        ]]
                    );
                    $totalUnflagged += $updateRes->getModifiedCount();
                }
            }

            if (!empty($txBatch)) {
                $txColl = $this->client->{$this->dbName}->fx_ioc_feed_transactions;
                $txColl->insertMany($txBatch);
            }

            // 📝 สั่ง Rebuild CSV ใหม่ทันที เพื่อไม่ต้องรอ Cronjob
            \Artisan::call('ioc-feed:update', ['--export-only' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully removed ' . $totalDeleted . ' item(s) from whitelist and unflagged ' . $totalUnflagged . ' cached records.'
            ]);

        } catch (\Exception $e) {
            Log::error('IocWhitelistController@bulkDestroy Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error: '.$e->getMessage()], 500);
        }
    }
}
