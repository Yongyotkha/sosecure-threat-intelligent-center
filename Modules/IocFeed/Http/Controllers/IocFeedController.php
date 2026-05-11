<?php

namespace Modules\IocFeed\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use MongoDB\Client;
use Illuminate\Support\Facades\Config;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Log;

class IocFeedController extends Controller
{
    protected $mongo;
    protected $database;

    public function __construct()
    {
        $mongo_uri = config('app.DB_MONGO'); // ใช้ URI จาก .env เดิมของระบบ
        $this->mongo = new Client($mongo_uri);
        $this->database = config('iocfeed.mongodb.database');
    }

    /**
     * Display the IoC Feed Dashboard
     */
    public function index()
    {
        $data['page'] = 'IoC Feed Dashboard';
        
        // Stats from MongoDB
        $data['stats'] = [
            'total_feeds'     => $this->collection('feeds')->countDocuments(['status' => 1]),
            'ip_count'        => $this->collection('feeds')->countDocuments(['type' => 'ip_address', 'status' => 1]),
            'domain_count'    => $this->collection('feeds')->countDocuments(['type' => 'domain', 'status' => 1]),
            'hash_count'      => $this->collection('feeds')->countDocuments(['type' => 'hashfile', 'status' => 1]),
            'whitelist_count' => $this->collection('whitelists')->countDocuments([]),
        ];
        
        // Active API tokens
        $data['tokens'] = \App\ApiToken::where('type', 'ioc_feed')->get();

        return view('iocfeed::index', $data);
    }

    /**
     * Helper to get a collection
     */
    protected function collection($name)
    {
        $collectionName = config("iocfeed.mongodb.collections.{$name}");
        return $this->mongo->{$this->database}->selectCollection($collectionName);
    }
    /**
     * Export IoC as CSV
     */
    public function exportCsv(Request $request, $category)
    {
        // 0. ตรวจสอบ Token & IP Whitelist
        $tokenStr = $request->input('token');
        if (!$tokenStr) {
            return response()->json(['error' => 'API Token is required'], 401);
        }

        $token = \App\ApiToken::where('token', $tokenStr)
            ->where('type', 'ioc_feed')
            ->first();

        if (!$token) {
            return response()->json(['error' => 'Invalid or expired API Token'], 401);
        }

        // ตรวจสอบ IP Whitelist (ต้องมีการระบุไว้เสมอ)
        if (empty($token->whitelist_ips)) {
            Log::channel('ioc_feed')->warning("IoC Feed Access Blocked: No IP Whitelist configured for token [{$token->name}]");
            return response()->json(['error' => 'Forbidden: No Permission'], 403);
        }

        $allowedIps = array_filter(array_map('trim', explode(',', str_replace(["\r\n", "\n"], ',', $token->whitelist_ips))));
        $clientIp = $request->ip();
        
        if (!in_array($clientIp, $allowedIps)) {
            Log::channel('ioc_feed')->warning("IoC Feed Access Blocked: IP {$clientIp} not whitelisted for token [{$token->name}]");
            return response()->json(['error' => 'Forbidden: No Permission'], 403);
        }

        // บันทึกข้อมูลการใช้งานล่าสุด
        $token->update([
            'last_used_at' => now(),
            'last_ip' => $request->ip()
        ]);

        // 1. ตรวจสอบหมวดหมู่ (IP, Domain, Hash, All)
        $validCategories = ['ip_address', 'domain', 'hashfile', 'all'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Invalid category'], 400);
        }

        // 🚀 Optimization: หากไม่มี Filter พิเศษ ให้ดึงไฟล์ Static ที่เตรียมไว้มาส่งออกทันที
        $hasFilters = $request->has('timeframe') || 
                      $request->has('is_public') || 
                      ($request->has('with_whitelist') && $request->input('with_whitelist') == '1');

        if (!$hasFilters) {
            $staticFile = base_path("Modules/IocFeed/Exports/{$category}.csv");
            if (file_exists($staticFile)) {
                Log::channel('ioc_feed')->info("IoC Feed Export: [{$category}] served from STATIC FILE. Client IP: " . $request->ip());
                return response()->download($staticFile, $category . '.csv', [
                    'Content-Type' => 'text/plain; charset=utf-8',
                ]);
            }
        }

        Log::channel('ioc_feed')->info("IoC Feed Export: [{$category}] generated from DATABASE. Client IP: " . $request->ip());

        // 2. ดึงข้อมูลจาก MongoDB โดยใช้ Flag 'is_whitelisted' ในการกรองขยะออก (Fallback)
        $query = [
            'status' => 1
        ];

        // ถ้าไม่ใช่ 'all' ให้ระบุประเภทที่ต้องการดึง
        if ($category !== 'all') {
            $query['type'] = $category;
        }

        // 3. กรองตามสถานะ Public/Private ของ Event (ถ้าระบุมา)
        if ($request->has('is_public')) {
            $isPublic = $request->input('is_public');
            if ($isPublic !== 'all') {
                $query['is_public'] = (int)$isPublic;
            }
        }

        // 4. ถ้า User ไม่ได้ส่ง ?with_whitelist=1 มา ระบบจะกรองไอพีที่ติดธง Whitelist ทิ้งไปโดยอัตโนมัติ (Default Behavior)
        if (!$request->has('with_whitelist') || $request->input('with_whitelist') != '1') {
            $query['is_whitelisted'] = ['$ne' => true];
        }

        // รองรับ parameter ?timeframe=6h, 1d, 1w
        if ($request->has('timeframe')) {
            $tf = $request->input('timeframe');
            $seconds = 0;
            if (preg_match('/^(\d+)(h|d|w|m)$/', $tf, $matches)) {
                $val = (int)$matches[1];
                $unit = $matches[2];
                if ($unit === 'h') $seconds = $val * 3600;
                elseif ($unit === 'd') $seconds = $val * 86400;
                elseif ($unit === 'w') $seconds = $val * 604800;
                elseif ($unit === 'm') $seconds = $val * 2592000;
            }
            if ($seconds > 0) {
                // หาค่า timestamp จุดตัดเวลา (ปัจจุบันลบจำนวนวินาที)
                $cutoff = time() - $seconds;
                $query['timestamp_val'] = ['$gte' => $cutoff];
            }
        }
        
        $cursor = $this->collection('feeds')->find($query, ['sort' => ['timestamp_val' => -1]]);
        $results = $cursor->toArray();

        // 5. สร้างไฟล์ CSV ชั่วคราว (กรณีกรองแบบพิเศษ)
        $tmpFile = tempnam(sys_get_temp_dir(), 'ioc_');
        $handle = fopen($tmpFile, 'w');

        $count = 1;
        foreach ($results as $row) {
            $ip = trim($row['indicator'] ?? '');
            $score = $row['score'] ?? 0;
            $startTime = $this->formatToIso($row['ioc_timestamp'] ?? '');
            $endTime = $this->formatToIso($row['sending_timestamp'] ?? '');
            $cat = trim($row['category'] ?? ($row['type'] ?? '-'));
            $sev = trim($row['severity'] ?? 'Low');
            $indId = trim($row['indicator_id'] ?? '');
            $evId = trim($row['event_id'] ?? '');
            $evName = str_replace(['|', "\r", "\n"], [' ', '', ''], $row['event_name'] ?? '');

            // รูปแบบ: Count,IP,score|start_time|end_time|category|severity|indicator_id|event_id|event_name
            $line = "{$count},{$ip},{$score}|{$startTime}|{$endTime}|{$cat}|{$sev}|{$indId}|{$evId}|{$evName}";
            $line = str_replace(["\r", "\n"], ["", ""], $line);

            fwrite($handle, $line . PHP_EOL);
            $count++;
        }

        fclose($handle);

        return response()->download($tmpFile, $category . '.csv', [
            'Content-Type' => 'text/plain; charset=utf-8',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Helper to format dates to readable format (d/m/Y H.i)
     */
    private function formatToIso($dateStr)
    {
        if (!$dateStr || $dateStr == '-') return date('j/n/Y H:i');
        
        try {
            // ลอง parse จาก d/m/Y H:i
            $date = \DateTime::createFromFormat('d/m/Y H:i', $dateStr);
            if ($date) return $date->format('j/n/Y H:i');
            
            // ถ้าไม่ใช่ ให้ลอง parse ปกติ
            return date('j/n/Y H:i', strtotime($dateStr));
        } catch (\Exception $e) {
            return $dateStr;
        }
    }

    /**
     * Store a newly created IoC
     */
    public function store(Request $request)
    {
        $request->validate([
            'indicator' => 'required',
            'type' => 'required|in:ip_address,domain,hashfile',
            'score' => 'required_without:severity',
            'severity' => 'required_without:score',
        ]);

        $data = $request->only(['indicator', 'type', 'score', 'category', 'severity']);
        $data['status'] = 1;
        $data['score'] = $request->has('score') ? (int)$request->score : 0;
        $data['ioc_timestamp'] = $request->ioc_timestamp ?? date('d/m/Y H:i');
        $data['sending_timestamp'] = date('d/m/Y H:i');
        $data['timestamp_val'] = time();
        $data['updated_at'] = new UTCDateTime();

        // Use upsert to maintain uniqueness on indicator field
        $this->collection('feeds')->updateOne(
            ['indicator' => $data['indicator']],
            ['$set' => $data, '$setOnInsert' => ['created_at' => new UTCDateTime()]],
            ['upsert' => true]
        );
        
        $this->auditLog('ADD_IOC', $data);

        return response()->json(['success' => true]);
    }

    /**
     * Update IoC
     */
    public function update(Request $request, $id)
    {
        $data = $request->only(['score', 'category', 'severity', 'status']);
        $data['updated_at'] = new UTCDateTime();
        
        // Update timestamps so it appears in recently active feeds (timeframe filter)
        $data['timestamp_val'] = time();
        $data['sending_timestamp'] = date('d/m/Y H:i');

        $this->collection('feeds')->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );

        $this->auditLog('UPDATE_IOC', array_merge(['id' => $id], $data));

        return response()->json(['success' => true]);
    }

    /**
     * Remove IoC
     */
    public function destroy($id)
    {
        $doc = $this->collection('feeds')->findOne(['_id' => new ObjectId($id)]);
        $this->collection('feeds')->deleteOne(['_id' => new ObjectId($id)]);

        $this->auditLog('DELETE_IOC', (array) $doc);

        return response()->json(['success' => true]);
    }

    /**
     * Store Whitelist
     */
    public function storeWhitelist(Request $request)
    {
        $hasItems = $request->has('items') && is_array($request->input('items'));
        
        if ($hasItems) {
            $request->validate([
                'items.*.indicator' => 'required',
                'items.*.type' => 'required|in:ip_address,domain,hashfile',
            ]);
            $items = $request->input('items');
        } else {
            $request->validate([
                'indicator' => 'required',
                'type' => 'required|in:ip_address,domain,hashfile',
            ]);
            $items = [$request->only(['indicator', 'type'])];
        }

        $now = new UTCDateTime();
        $insertedCount = 0;
        $bulkOps = [];

        foreach ($items as $item) {
            $item['created_at'] = $now;
            
            $bulkOps[] = [
                'updateOne' => [
                    ['indicator' => $item['indicator'], 'type' => $item['type']],
                    ['$set' => $item],
                    ['upsert' => true]
                ]
            ];
            $insertedCount++;
        }

        if (!empty($bulkOps)) {
            $this->collection('whitelists')->bulkWrite($bulkOps);
        }

        $this->auditLog('ADD_WHITELIST', $hasItems ? ['count' => $insertedCount] : $items[0]);

        return response()->json(['success' => true, 'message' => "Added/Updated {$insertedCount} items in whitelist."]);
    }

    /**
     * Remove from Whitelist
     */
    public function destroyWhitelist(Request $request)
    {
        $hasItems = $request->has('items') && is_array($request->input('items'));
        
        if ($hasItems) {
            $request->validate([
                'items.*.indicator' => 'required',
                'items.*.type' => 'required|in:ip_address,domain,hashfile',
            ]);
            $items = $request->input('items');
        } else {
            $request->validate([
                'indicator' => 'required',
                'type' => 'required|in:ip_address,domain,hashfile',
            ]);
            $items = [$request->only(['indicator', 'type'])];
        }

        $bulkOps = [];

        foreach ($items as $item) {
            $bulkOps[] = [
                'deleteOne' => [
                    ['indicator' => $item['indicator'], 'type' => $item['type']]
                ]
            ];
        }

        $deletedCount = 0;
        if (!empty($bulkOps)) {
            $result = $this->collection('whitelists')->bulkWrite($bulkOps);
            $deletedCount = $result->getDeletedCount();
        }

        $this->auditLog('DELETE_WHITELIST', $hasItems ? ['count' => $deletedCount] : $items[0]);

        return response()->json(['success' => true, 'message' => "Deleted {$deletedCount} items from whitelist."]);
    }

    /**
     * Audit Log Helper
     */
    private function auditLog($action, $data)
    {
        $this->collection('audit_logs')->insertOne([
            'action' => $action,
            'data' => $data,
            'user' => auth()->user()->username ?? 'system',
            'ip' => request()->ip(),
            'created_at' => new UTCDateTime()
        ]);
    }
}
