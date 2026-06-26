<?php
/**
 * สคริปต์สร้าง MongoDB Index สำหรับ fx_otx_events_indicator_ref
 * 
 * วิธีรัน: php create_mongodb_index.php
 */

require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

// ใช้ Laravel config แทนการอ่าน .env โดยตรง
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mongoUri = config('app.DB_MONGO_DEV');

echo "🔗 Connecting to MongoDB...\n";
echo "URI: " . substr($mongoUri, 0, 30) . "...\n\n";

try {
    $client = new Client($mongoUri);
    $collection = $client->sosecure_threatintelligent->fx_otx_events_indicator_ref;
    
    echo "📊 Collection: fx_otx_events_indicator_ref\n\n";
    
    // ตรวจสอบ indexes ที่มีอยู่
    echo "=== Existing Indexes ===\n";
    $existingIndexes = $collection->listIndexes();
    foreach ($existingIndexes as $index) {
        echo "- " . $index['name'] . ": " . json_encode($index['key']) . "\n";
    }
    echo "\n";
    
    // นับจำนวน documents
    echo "📈 Total documents: " . $collection->countDocuments([]) . "\n\n";
    
    // สร้าง index สำหรับ indicator (Search internal_events)
    echo "🔨 Creating index: idx_indicator...\n";
    try {
        $result0 = $collection->createIndex(
            ['indicator' => 1],
            [
                'name' => 'idx_indicator',
                'background' => true
            ]
        );
        echo "✅ Created: $result0\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ️  Index already exists\n\n";
        } else {
            throw $e;
        }
    }

    $detailCollection = $client->sosecure_threatintelligent->fx_otx_indicator_detail;
    echo "🔨 Creating index: idx_indicator_name on fx_otx_indicator_detail...\n";
    try {
        $resultDetail = $detailCollection->createIndex(
            ['indicator_name' => 1],
            [
                'name' => 'idx_indicator_name',
                'background' => true
            ]
        );
        echo "✅ Created: $resultDetail\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ️  Index already exists\n\n";
        } else {
            throw $e;
        }
    }

    // สร้าง index สำหรับ pulse_id
    echo "🔨 Creating index: idx_pulse_id...\n";
    try {
        $result1 = $collection->createIndex(
            ['pulse_id' => 1],
            [
                'name' => 'idx_pulse_id',
                'background' => true
            ]
        );
        echo "✅ Created: $result1\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ️  Index already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // สร้าง compound index สำหรับ pulse_id + updated_at
    echo "🔨 Creating index: idx_pulse_id_updated_at...\n";
    try {
        $result2 = $collection->createIndex(
            ['pulse_id' => 1, 'updated_at' => -1],
            [
                'name' => 'idx_pulse_id_updated_at',
                'background' => true
            ]
        );
        echo "✅ Created: $result2\n\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ️  Index already exists\n\n";
        } else {
            throw $e;
        }
    }
    
    // แสดง indexes ทั้งหมดหลังสร้าง
    echo "=== All Indexes (After Creation) ===\n";
    $allIndexes = $collection->listIndexes();
    foreach ($allIndexes as $index) {
        echo "- " . $index['name'] . ": " . json_encode($index['key']) . "\n";
    }
    echo "\n";
    
    // ทดสอบ query performance
    echo "🧪 Testing query performance...\n";
    
    // หา pulse_id ที่มีจริงในระบบ
    $sampleDoc = $collection->findOne([], ['projection' => ['pulse_id' => 1]]);
    if ($sampleDoc) {
        $testPulseId = $sampleDoc['pulse_id'];
        echo "Testing with pulse_id: $testPulseId\n";
        
        $startTime = microtime(true);
        $cursor = $collection->find(
            ['pulse_id' => $testPulseId],
            [
                'projection' => [
                    '_id' => 0,
                    'indicator_id' => 1,
                    'indicator_name' => 1,
                    'type' => 1,
                    'pulse_id' => 1,
                    'updated_at' => 1,
                    'tags' => 1
                ],
                'sort' => ['updated_at' => -1],
                'limit' => 25
            ]
        );
        $results = $cursor->toArray();
        $endTime = microtime(true);
        
        $queryTime = round(($endTime - $startTime) * 1000, 2);
        echo "⏱️  Query time: {$queryTime}ms\n";
        echo "📝 Results found: " . count($results) . "\n\n";
    }
    
    echo "✅ Index creation completed successfully!\n";
    echo "\n";
    echo "📌 Next steps:\n";
    echo "1. Go to IndicatorsController.php line ~1671\n";
    echo "2. Uncomment: 'hint' => ['pulse_id' => 1],\n";
    echo "3. Clear cache: php artisan cache:clear\n";
    echo "4. Test the application again\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
