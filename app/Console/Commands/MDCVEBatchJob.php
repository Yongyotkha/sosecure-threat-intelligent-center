<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\SiteSettings\Entities\DataCveven;
use App\Entities\TransactionBatchjob;
use App\CveAssetsProgress;

class MDCVEBatchJob extends Command
{
    protected $signature = 'app:MDCVEBatchJob';
    protected $description = 'Full CVE Data Feed Processor (NVD v2.0, same flow as legacy)';

    public function handle()
    {
        $this->info("=== 🟢 MDCVEBatchJob started ===");
        date_default_timezone_set("Asia/Bangkok");
        $jobStartTime = microtime(true);
        @set_time_limit(0);
        ini_set('memory_limit', '4096M');

        // อัปเดตสถานะ Batch
        $TransactionBatchjob = TransactionBatchjob::where('mode', 'MDCVEBatchJob')->first();
        if ($TransactionBatchjob) {
            $TransactionBatchjob->progress = 2;
            $TransactionBatchjob->transcation_date_start = now();
            $TransactionBatchjob->transcation_date = now();
            $TransactionBatchjob->save();
        }

        // เชื่อมต่อฐานข้อมูล
        $conn = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT'));
        if (!$conn) {
            $this->error("❌ Connection failed: " . mysqli_connect_error());
            return;
        }

        // === ดาวน์โหลดและแตกไฟล์ NVD Feed v2.0 ===
        $path = app_path() . "/Console/Commands/temp/adddatabase/";
        if (!file_exists($path)) mkdir($path, 0777, true);
        $zipFile = $path . "nvdcve-2.0-modified.json.zip";
        $jsonFile = $path . "nvdcve-2.0-modified.json";
        $url = "https://nvd.nist.gov/feeds/json/cve/2.0/nvdcve-2.0-modified.json.zip";

        try {
            $this->info("⬇️  Downloading NVD Feed (v2.0)...");
            $downloadStart = microtime(true);
            $data = @file_get_contents($url);
            if ($data === false || strlen($data) === 0) {
                throw new Exception("Failed to download NVD feed");
            }
            file_put_contents($zipFile, $data);
            $this->info("✅ Downloaded: ~" . round(strlen($data) / 1024 / 1024, 2) . " MB in " . round((microtime(true) - $downloadStart), 2) . "s");

            $this->info("📦 Extracting zip...");
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo($path);
                $zip->close();
                $this->info("✅ Extracted to: {$jsonFile}");
            } else {
                throw new Exception("Cannot open zip file");
            }

            $this->info("🧩 Parsing JSON (in-memory like original; progress logging enabled)...");
            $parseStart = microtime(true);
            $jsonRaw = @file_get_contents($jsonFile);
            if ($jsonRaw === false) {
                throw new Exception("Cannot read JSON file after extract");
            }
            $json = json_decode($jsonRaw, true, 512, JSON_INVALID_UTF8_IGNORE);
            unset($jsonRaw); // ลด RAM
            if (!is_array($json) || !isset($json['vulnerabilities'])) {
                throw new Exception("Invalid JSON structure or no 'vulnerabilities' key");
            }
            $this->info("✅ JSON ready in " . round((microtime(true) - $parseStart), 2) . "s");

            $today = strtotime('-90 days'); // ดึงเฉพาะ CVE ล่าสุด 90 วัน
            $count_new = 0;
            $count_update = 0;
            $processed = 0;
            $batchSize = 500; // commit ทุก 500 รายการเพื่อลด I/O

            // เปิด transaction สำหรับการ import
            mysqli_query($conn, "START TRANSACTION");
            $chunkStart = microtime(true);

            foreach ($json['vulnerabilities'] as $vuln) {
                $processed++;

                $cve = $vuln['cve'] ?? null;
                $CVE_Code = $cve['id'] ?? null;
                if (!$CVE_Code) continue;

                $publishedDate = explode('T', $cve['published'] ?? '')[0];
                if (!$publishedDate || strtotime($publishedDate) < $today) continue;
                $lastModifiedDate = explode('T', $cve['lastModified'] ?? '')[0];

                // คำอธิบายภาษาอังกฤษ
                $description_data = '';
                foreach ($cve['descriptions'] ?? [] as $desc) {
                    if (($desc['lang'] ?? '') === 'en') {
                        // เก็บแบบ escape ป้องกัน quote
                        $description_data .= htmlspecialchars($desc['value'], ENT_QUOTES);
                    }
                }

                // CVSS Metric (v3.1 → v3.0 → v2)
                $baseScore = "";
                $baseSeverity = "";

                if (isset($cve['metrics']['cvssMetricV40'][0]['cvssData'])) {
                    // ✅ CVSS v4.0
                    $cvss = $cve['metrics']['cvssMetricV40'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                    // Log::info("{$CVE_Code} [CVSS v4.0] - Score: {$baseScore} - Severity: {$baseSeverity}");
                } elseif (isset($cve['metrics']['cvssMetricV31'][0]['cvssData'])) {
                    // ✅ CVSS v3.1
                    $cvss = $cve['metrics']['cvssMetricV31'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                    // Log::info("{$CVE_Code} [CVSS v3.1] - Score: {$baseScore} - Severity: {$baseSeverity}");
                } elseif (isset($cve['metrics']['cvssMetricV30'][0]['cvssData'])) {
                    // ✅ CVSS v3.0
                    $cvss = $cve['metrics']['cvssMetricV30'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                    // Log::info("{$CVE_Code} [CVSS v3.0] - Score: {$baseScore} - Severity: {$baseSeverity}");
                } elseif (isset($cve['metrics']['cvssMetricV2'][0]['cvssData'])) {
                    // ✅ CVSS v2.0
                    $cvss = $cve['metrics']['cvssMetricV2'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                    // Log::info("{$CVE_Code} [CVSS v2.0] - Score: {$baseScore} - Severity: {$baseSeverity}");
                }

                // CPE Configurations
                $cpeMatches = [];
                foreach ($cve['configurations'] ?? [] as $config) {
                    foreach ($config['nodes'] ?? [] as $node) {
                        foreach ($node['cpeMatch'] ?? [] as $cpeMatch) {
                            if (!empty($cpeMatch['vulnerable']) && isset($cpeMatch['criteria'])) {
                                $cpeMatches[] = $cpeMatch['criteria'];
                            }
                        }
                    }
                }

                // === Insert fx_data_cveven === (แบบเดิม แต่ commit เป็นช่วง)
                foreach ($cpeMatches as $uri) {
                    $parts = explode(':', $uri);
                    $vendor_name  = mysqli_real_escape_string($conn, $parts[3] ?? '');
                    $product_name = mysqli_real_escape_string($conn, $parts[4] ?? '');
                    $version      = mysqli_real_escape_string($conn, $parts[5] ?? '');
                    $edition      = mysqli_real_escape_string($conn, ($parts[6] ?? '') === '*' ? '' : ($parts[6] ?? ''));

                    $query_check = "
                        SELECT 1 FROM fx_data_cveven
                        WHERE namecve='$CVE_Code' AND vendor='$vendor_name' AND title='$product_name' AND version='$version'
                    ";
                    $exists = mysqli_query($conn, $query_check);
                    if ($exists && mysqli_num_rows($exists) == 0) {
                        $query_insert = "
                            INSERT INTO fx_data_cveven(namecve, vendor, title, version, edition, created_at)
                            VALUES ('$CVE_Code', '$vendor_name', '$product_name', '$version', '$edition', NOW())
                        ";
                        mysqli_query($conn, $query_insert);
                    }
                }

                // === Insert/Update fx_data_datacve ===
                $exists = mysqli_query($conn, "SELECT 1 FROM fx_data_datacve WHERE namecve='" . mysqli_real_escape_string($conn, $CVE_Code) . "'");
                if ($exists && mysqli_num_rows($exists) == 0) {
                    $this->insert_nvd($conn, $CVE_Code, $publishedDate, $lastModifiedDate, mysqli_real_escape_string($conn, $description_data), $baseScore, $baseSeverity);
                    $count_new++;
                } else {
                    $this->update_nvd($conn, $CVE_Code, $publishedDate, $lastModifiedDate, mysqli_real_escape_string($conn, $description_data), $baseScore, $baseSeverity);
                    $count_update++;
                }

                // === Mirror fx_data_datacve -> fx_data_datacve_mapping (no-duplicate version) ===
                $CVE_Code_esc = mysqli_real_escape_string($conn, $CVE_Code);
                $published_esc = mysqli_real_escape_string($conn, $publishedDate);
                $modified_esc  = mysqli_real_escape_string($conn, $lastModifiedDate);
                $desc_esc      = mysqli_real_escape_string($conn, $description_data);
                $score_esc     = mysqli_real_escape_string($conn, (string)$baseScore);
                $sev_esc       = mysqli_real_escape_string($conn, (string)$baseSeverity);

                // 1️⃣ อัปเดตถ้ามีอยู่แล้ว
                $update_sql = "
                    UPDATE fx_data_datacve_mapping
                    SET 
                        published='{$published_esc}',
                        modified='{$modified_esc}',
                        description='{$desc_esc}',
                        cvss_score='{$score_esc}',
                        severity='{$sev_esc}',
                        updated_at=NOW()
                    WHERE namecve='{$CVE_Code_esc}' AND site_id=0
                ";
                mysqli_query($conn, $update_sql);

                // 2️⃣ ถ้าไม่มี record เดิม → insert ใหม่
                if (mysqli_affected_rows($conn) === 0) {
                    $insert_sql = "
                        INSERT INTO fx_data_datacve_mapping
                            (namecve, published, modified, description, cvss_score, severity, site_id, updated_at, created_at)
                        VALUES
                            ('{$CVE_Code_esc}', '{$published_esc}', '{$modified_esc}', '{$desc_esc}', '{$score_esc}', '{$sev_esc}', 0, NOW(), NOW())
                    ";
                    mysqli_query($conn, $insert_sql);
                }


                // === Progress / Commit เป็นช่วง ===
                if ($processed % $batchSize === 0) {
                    mysqli_query($conn, "COMMIT");
                    mysqli_query($conn, "START TRANSACTION");
                    $mins = round((microtime(true) - $chunkStart) / 60, 2);
                    $this->info("⏳ Processed: {$processed} CVEs | New={$count_new} / Update={$count_update} | Chunk time={$mins} min");
                    $chunkStart = microtime(true);
                    gc_collect_cycles();
                }
            }

            // ปิดท้าย import
            mysqli_query($conn, "COMMIT");
            $importMins = round((microtime(true) - $parseStart) / 60, 2);
            $this->info("✅ Inserted new: {$count_new}, Updated: {$count_update} (Import time ~ {$importMins} min)");

            // ขั้นตอน Mapping + Summary (คงโค้ดเดิม เพิ่ม log)
            $this->processMappingAndSummary($conn);

            // อัปเดตสถานะ Batch เสร็จสมบูรณ์
            if ($TransactionBatchjob) {
                $TransactionBatchjob->progress = 1;
                $TransactionBatchjob->transcation_date_end = now();
                $TransactionBatchjob->transcation_date = now();
                $TransactionBatchjob->save();
            }

            $totalMins = round((microtime(true) - $jobStartTime) / 60, 2);
            $this->info("🎯 MDCVEBatchJob finished successfully in ~{$totalMins} minutes.");
        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    // ---------------- ฟังก์ชันย่อย ----------------
    private function insert_nvd($conn, $name, $pub, $mod, $desc, $score, $sev)
    {
        $sql = "INSERT INTO fx_data_datacve (namecve, published, modified, description, cvss_score, severity, updated_at, created_at)
                VALUES ('$name','$pub','$mod','$desc','$score','$sev',NOW(),NOW())";
        mysqli_query($conn, $sql);
    }

    private function update_nvd($conn, $name, $pub, $mod, $desc, $score, $sev)
    {
        $sql = "UPDATE fx_data_datacve SET 
                published='$pub', modified='$mod', description='$desc',
                cvss_score='$score', severity='$sev', updated_at=NOW()
                WHERE namecve='$name'";
        mysqli_query($conn, $sql);
    }

    private function processMappingAndSummary($conn)
    {
        $this->info("🔁 Starting CVE Mapping & Summary...");
        $stepStart = microtime(true);

        // 📦 อ่าน progress ล่าสุด
        $progress = CveAssetsProgress::first();
        $lastAssetId = $progress ? $progress->last_asset_id : 0;
        $lastCveName = $progress ? $progress->last_cve_name : null;

        $this->info("▶️ Resume from asset_id={$lastAssetId}, cve={$lastCveName}");

        // 📂 ดึง assets ที่ยังไม่ได้ทำ หรือค้างไว้
        $assets = mysqli_query($conn, "SELECT * FROM fx_cve_assets WHERE id >= $lastAssetId ORDER BY id ASC");
        if (!$assets) {
            $this->error("❌ Cannot query fx_cve_assets");
            return;
        }

        $mapped = 0;
        $mapBatchSize = 100;

        while ($row = mysqli_fetch_assoc($assets)) {
            $vendor = mysqli_real_escape_string($conn, $row['vendor']);
            $title  = mysqli_real_escape_string($conn, $row['title']);
            $version = mysqli_real_escape_string($conn, $row['version']);
            $edition = mysqli_real_escape_string($conn, $row['edition']);
            $site_id = $row['site_id'];

            $this->info("🧩 Asset ID {$row['id']} (site_id: {$site_id})");

            // ✅ สร้าง query ดึง CVE ที่ตรงกับ asset
            $query = "SELECT * FROM fx_data_cveven WHERE vendor='$vendor' AND title='$title'";
            if (!in_array($version, ['-', '*', ''])) $query .= " AND version='$version'";
            if (!in_array($edition, ['-', '*', ''])) $query .= " AND edition='$edition'";

            $cves = mysqli_query($conn, $query);
            if (!$cves) {
                $this->error("❌ Error fetching CVEs for asset {$row['id']}");
                continue;
            }

            // ⚙️ ตรวจว่าต้อง skip CVE ก่อนหน้าไหม (resume จากจุดค้าง)
            $skipMode = ($row['id'] == $lastAssetId && $lastCveName) ? true : false;

            while ($cve = mysqli_fetch_assoc($cves)) {
                $name = $cve['namecve'];

                // ถ้ายังอยู่ในโหมด skip ให้ข้ามไปจนกว่าจะถึง CVE เดิม
                if ($skipMode) {
                    if ($name == $lastCveName) {
                        $skipMode = false;
                        continue; // ข้าม CVE เดิม แล้วเริ่มจากตัวถัดไป
                    }
                    continue;
                }

                // ตรวจว่ามี mapping แล้วหรือยัง
                $exists = mysqli_query($conn, "
                    SELECT 1 FROM fx_data_datacve_mapping_assets
                    WHERE namecve='$name' AND site_id='$site_id' AND cve_asset_id='{$row['id']}'
                ");
                if ($exists && mysqli_num_rows($exists) == 0) {
                    $insert = "
                        INSERT IGNORE INTO fx_data_datacve_mapping_assets
                        (namecve, cve_asset_id, site_id, code, updated_at, created_at)
                        VALUES ('$name', '{$row['id']}', '$site_id', '" . $this->GUID() . "', NOW(), NOW())
                    ";
                    mysqli_query($conn, $insert);
                }

                // ✅ บันทึก progress ทุกครั้งที่ผ่าน CVE
                CveAssetsProgress::query()->update([
                    'last_asset_id' => $row['id'],
                    'last_cve_name' => $name,
                    'updated_at' => now(),
                ]);
            }

            // 🧹 ล้างค่า CVE หลังทำ asset นี้ครบ
            CveAssetsProgress::query()->update(['last_cve_name' => null]);

            $mapped++;
            if ($mapped % $mapBatchSize === 0) {
                $mins = round((microtime(true) - $stepStart) / 60, 2);
                $this->info("📈 Mapped {$mapped} assets so far | Elapsed={$mins} min");
                gc_collect_cycles();
            }
        }

        // 🧾 สรุปผลหลังจากประมวลผลครบทั้งหมด
        $this->info("📊 Generating summaries...");

        $sites = SiteSettings::select('id', 'code')->where('active', 1)->get();
        foreach ($sites as $site) {
            $this->generateSummary($conn, $site->id, $site->code);
        }
        // site_id = 0 (overall)
        $this->generateSummary($conn, 0, 0);

        CveAssetsProgress::query()->update([
            'last_asset_id' => 0,
            'last_cve_name' => null,
            'updated_at' => now(),
        ]);
        $this->info("🔄 Reset cve_assets_progress for next batch.");

        $mins = round((microtime(true) - $stepStart) / 60, 2);
        $this->info("✅ CVE Mapping & Summary completed successfully in ~{$mins} minutes!");
    }

    private function generateSummary($conn, $site_id, $site_code)
    {
        $this->info("🔁 generateSummary(site_id={$site_id}, code={$site_code}) ...");

        // ดึง namecve ทั้งหมดของ site (distinct)
        $mappedCve = DB::table('data_datacve_mapping_assets')
            ->where('site_id', $site_id)
            ->distinct()
            ->pluck('namecve');

        if ($mappedCve->isEmpty()) {
            $this->info("ℹ️ No CVE mapping for site={$site_code}");
            return;
        }

        // ดึง severity ตาม CVE แบบ DISTINCT
        $severityList = DB::table('data_datacve_mapping')
            ->whereIn('namecve', $mappedCve)
            ->select('namecve', 'severity')
            ->groupBy('namecve', 'severity')
            ->get();

        // เคลียร์ summary เดิม
        DB::table('summary')
            ->where('site', $site_code)
            ->where('data_key_2', 'Vulnerability')
            ->delete();

        // นับตาม severity
        $crit = $high = $med = $low = $info = 0;

        foreach ($severityList as $s) {
            switch (strtoupper($s->severity)) {
                case 'CRITICAL':
                    $crit++;
                    break;
                case 'HIGH':
                    $high++;
                    break;
                case 'MEDIUM':
                    $med++;
                    break;
                case 'LOW':
                    $low++;
                    break;
                default:
                    $info++;
                    break;
            }
        }

        // เขียน summary
        DB::table('summary')->insert([
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Critical', 'data_value' => $crit, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'High', 'data_value' => $high, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Medium', 'data_value' => $med, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Low', 'data_value' => $low, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Information', 'data_value' => $info, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->info("   ✔ Summary updated for {$site_code} — Total CVEs = " . ($crit + $high + $med + $low + $info));
    }


    private function GUID()
    {
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}
