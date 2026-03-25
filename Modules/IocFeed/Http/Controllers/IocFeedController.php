<?php

namespace Modules\IocFeed\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use MongoDB\Client;
use Illuminate\Support\Facades\Config;

class IocFeedController extends Controller
{
    protected $mongo;
    protected $database;

    public function __construct()
    {
        $mongo_uri = config('app.DB_MONGO_DEV'); // ใช้ URI จาก .env เดิมของระบบ
        $this->mongo = new Client($mongo_uri);
        $this->database = config('iocfeed.mongodb.database');
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
        // 1. ตรวจสอบหมวดหมู่ (IP, Domain, Hash)
        $validCategories = ['ip_address', 'domain', 'hashfile'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Invalid category'], 400);
        }

        // 2. ดึงข้อมูล Whitelist เพื่อนำมากรองออก
        $whitelists = $this->collection('whitelists')->find(['type' => $category])->toArray();
        $excludeList = array_column($whitelists, 'indicator');

        // 3. ดึงข้อมูลจาก MongoDB โดยกรอง Whitelist ออก ($nin)
        $query = [
            'type' => $category,
            'status' => 1
        ];
        
        if (!empty($excludeList)) {
            $query['indicator'] = ['$nin' => $excludeList];
        }

        $cursor = $this->collection('feeds')->find($query);
        $results = $cursor->toArray();

        // 4. สร้าง PSV (Pipe Separated Values) โดยเขียนลงไฟล์ temp
        $tmpFile = tempnam(sys_get_temp_dir(), 'ioc_');
        $handle = fopen($tmpFile, 'w');

        foreach ($results as $row) {
            $startTime = $this->formatToIso($row['ioc_timestamp'] ?? '');
            $endTime = $this->formatToIso($row['sending_timestamp'] ?? '');

            // รูปแบบ: IP,score|start_time|end_time|category|severity|indicator_id|event_id
            $ip = trim($row['indicator']);
            $score = $row['score'] ?? 0;
            $cat = trim($row['category'] ?? '-');
            $sev = trim($row['severity'] ?? 'Low');
            $indId = trim($row['indicator_id'] ?? '');
            $evId = trim($row['event_id'] ?? '');

            // สร้างบรรทัด: IP,score เป็นฟิลด์แรก (คั่นด้วย comma) ตามด้วย | คั่นฟิลด์ถัดไป
            $line = "{$ip},{$score}|{$startTime}|{$endTime}|{$cat}|{$sev}|{$indId}|{$evId}";

            // ทำความสะอาด: ลบ newline ที่อาจติดมา
            $line = str_replace(["\r", "\n"], ["", ""], $line);

            // เขียน 1 บรรทัด + ขึ้นบรรทัดใหม่
            fwrite($handle, $line . PHP_EOL);
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
        ]);

        $data = $request->only(['indicator', 'type', 'score', 'category', 'severity']);
        $data['status'] = 1;
        $data['ioc_timestamp'] = $request->ioc_timestamp ?? date('d/m/Y H:i');
        $data['sending_timestamp'] = date('d/m/Y H:i');
        $data['created_at'] = new \MongoDB\BSON\UTCDateTime();
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();

        $result = $this->collection('feeds')->insertOne($data);
        
        $this->auditLog('ADD_IOC', $data);

        return response()->json(['success' => true, 'id' => (string) $result->getInsertedId()]);
    }

    /**
     * Update IoC
     */
    public function update(Request $request, $id)
    {
        $data = $request->only(['score', 'category', 'severity', 'status']);
        $data['updated_at'] = new \MongoDB\BSON\UTCDateTime();

        $this->collection('feeds')->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
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
        $doc = $this->collection('feeds')->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        $this->collection('feeds')->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);

        $this->auditLog('DELETE_IOC', (array) $doc);

        return response()->json(['success' => true]);
    }

    /**
     * Store Whitelist
     */
    public function storeWhitelist(Request $request)
    {
        $request->validate([
            'indicator' => 'required',
            'type' => 'required|in:ip_address,domain,hashfile',
        ]);

        $data = $request->only(['indicator', 'type']);
        $data['created_at'] = new \MongoDB\BSON\UTCDateTime();

        $this->collection('whitelists')->updateOne(
            ['indicator' => $data['indicator'], 'type' => $data['type']],
            ['$set' => $data],
            ['upsert' => true]
        );

        $this->auditLog('ADD_WHITELIST', $data);

        return response()->json(['success' => true]);
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
            'created_at' => new \MongoDB\BSON\UTCDateTime()
        ]);
    }
}
