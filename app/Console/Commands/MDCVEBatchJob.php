<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\TransactionBatchjob;
use App\CveAssetsProgress;

class MDCVEBatchJob extends Command
{
    protected $signature = 'app:MDCVEBatchJob {--summary-only : Skip import and only run mapping and summary}';
    protected $description = 'Full CVE Data Feed Processor (NVD v2.0, same flow as legacy)';

    public function handle()
    {
        $conn = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT'));
        if (!$conn) {
            $this->error("❌ Connection failed: " . mysqli_connect_error());
            return;
        }

        ini_set('memory_limit', '2048M');
        date_default_timezone_set("Asia/Bangkok");
        $jobStartTime = microtime(true);

        // Check for summary-only flag
        if ($this->option('summary-only')) {
            $this->info("⏩ Skipping import phase. Moving directly to Mapping & Summary...");
            $this->processMappingAndSummary($conn);
            $this->info("🎯 MDCVEBatchJob (Summary-Only) finished successfully.");
            return;
        }

        $this->info("=== 🟢 MDCVEBatchJob started ===");

        try {
            $TransactionBatchjob = TransactionBatchjob::where('mode', 'MDCVEBatchJob')->first();
            if ($TransactionBatchjob) {
                $TransactionBatchjob->progress = 2;
                $TransactionBatchjob->transcation_date_start = now();
                $TransactionBatchjob->transcation_date = now();
                $TransactionBatchjob->save();
            }

            // 1. 📥 ดาวน์โหลดไฟล์ NVD Feed (v2.0)
            $this->info("⬇️  Downloading NVD Feed (v2.0)...");
            $url = "https://nvd.nist.gov/feeds/json/cve/2.0/nvdcve-2.0-modified.json.zip"; 
            
            $tempDir = storage_path('app/temp/adddatabase/');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $zipPath = $tempDir . 'nvdcve-2.0-modified.json.zip';
            $extractPath = $tempDir;

            $ch = curl_init($url);
            $fp = fopen($zipPath, 'wb');
            if (!$fp) {
                throw new Exception("Cannot open file for writing: $zipPath. Please check permissions.");
            }

            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            
            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if (!$success || $httpCode !== 200) {
                throw new Exception("Download failed with HTTP Code: $httpCode. cURL Error: $curlError");
            }

            $fileSize = filesize($zipPath);
            $this->info("✅ Downloaded: ~" . round($fileSize / 1024 / 1024, 2) . " MB in " . round(microtime(true) - $jobStartTime, 2) . "s");

            // 2. 📦 แตกไฟล์
            $this->info("📦 Extracting zip...");
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($extractPath);
                $jsonFile = $extractPath . $zip->getNameIndex(0);
                $zip->close();
                $this->info("✅ Extracted to: {$jsonFile}");
            } else {
                throw new Exception("Cannot open zip file");
            }

            // 3. 🧩 Parse JSON และนำเข้า
            $this->info("🧩 Parsing JSON (NVD v2.0 structure)...");
            $parseStart = microtime(true);
            $jsonContent = file_get_contents($jsonFile);
            $json = json_decode($jsonContent, true);
            unset($jsonContent); // Free up memory
            if (!$json || !isset($json['vulnerabilities'])) {
                throw new Exception("Invalid JSON structure or missing 'vulnerabilities' key");
            }

            $this->info("✅ JSON ready in " . round(microtime(true) - $parseStart, 2) . "s");

            $count_new = 0;
            $count_update = 0;
            $processed = 0;
            $batchSize = 500;
            $chunkStart = microtime(true);

            mysqli_query($conn, "START TRANSACTION");

            foreach ($json['vulnerabilities'] as $item) {
                $processed++;

                $cve = $item['cve'] ?? null;
                $CVE_Code = $cve['id'] ?? null;
                if (!$CVE_Code) continue;

                $publishedDate = explode('T', $cve['published'] ?? '')[0];
                $lastModifiedDate = explode('T', $cve['lastModified'] ?? '')[0];

                // คำอธิบายภาษาอังกฤษ
                $description_data = '';
                foreach ($cve['descriptions'] ?? [] as $desc) {
                    if (($desc['lang'] ?? '') === 'en') {
                        $description_data .= htmlspecialchars($desc['value'], ENT_QUOTES);
                    }
                }

                // CVSS Metrics (v4.0 -> v3.1 -> v3.0 -> v2.0)
                $baseScore = "";
                $baseSeverity = "";
                $metrics = $cve['metrics'] ?? [];

                if (isset($metrics['cvssMetricV40'][0]['cvssData'])) {
                    $cvss = $metrics['cvssMetricV40'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                } elseif (isset($metrics['cvssMetricV31'][0]['cvssData'])) {
                    $cvss = $metrics['cvssMetricV31'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                } elseif (isset($metrics['cvssMetricV30'][0]['cvssData'])) {
                    $cvss = $metrics['cvssMetricV30'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $cvss['baseSeverity'] ?? "";
                } elseif (isset($metrics['cvssMetricV2'][0]['cvssData'])) {
                    $cvss = $metrics['cvssMetricV2'][0]['cvssData'];
                    $baseScore = $cvss['baseScore'] ?? "";
                    $baseSeverity = $metrics['cvssMetricV2'][0]['baseSeverity'] ?? "";
                }

                // CPE
                $cpeMatches = [];
                foreach ($cve['configurations'] ?? [] as $config) {
                    foreach ($config['nodes'] ?? [] as $node) {
                        foreach ($node['cpeMatch'] ?? [] as $match) {
                            if (!empty($match['vulnerable']) && isset($match['criteria'])) {
                                $cpeMatches[] = $match['criteria'];
                            }
                        }
                    }
                }

                // === Insert fx_data_cveven ===
                foreach ($cpeMatches as $uri) {
                    $parts = explode(':', $uri);
                    $vendor_name  = mysqli_real_escape_string($conn, $parts[3] ?? '');
                    $product_name = mysqli_real_escape_string($conn, $parts[4] ?? '');
                    $version      = mysqli_real_escape_string($conn, $parts[5] ?? '');
                    $edition      = mysqli_real_escape_string($conn, ($parts[6] ?? '') === '*' ? '' : ($parts[6] ?? ''));

                    $query_check = "SELECT 1 FROM fx_data_cveven WHERE namecve='$CVE_Code' AND vendor='$vendor_name' AND title='$product_name' AND version='$version'";
                    $exists = mysqli_query($conn, $query_check);
                    if ($exists && mysqli_num_rows($exists) == 0) {
                        $query_insert = "INSERT INTO fx_data_cveven(namecve, vendor, title, version, edition, created_at) VALUES ('$CVE_Code', '$vendor_name', '$product_name', '$version', '$edition', NOW())";
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

                // Mirror global mapping
                $CVE_Code_esc = mysqli_real_escape_string($conn, $CVE_Code);
                $published_esc = mysqli_real_escape_string($conn, $publishedDate);
                $modified_esc  = mysqli_real_escape_string($conn, $lastModifiedDate);
                $desc_esc      = mysqli_real_escape_string($conn, $description_data);
                $score_esc     = mysqli_real_escape_string($conn, (string)$baseScore);
                $sev_esc       = mysqli_real_escape_string($conn, (string)$baseSeverity);

                $update_sql = "UPDATE fx_data_datacve_mapping SET published='{$published_esc}', modified='{$modified_esc}', description='{$desc_esc}', cvss_score='{$score_esc}', severity='{$sev_esc}', updated_at=NOW() WHERE namecve='{$CVE_Code_esc}' AND site_id=0";
                mysqli_query($conn, $update_sql);

                if (mysqli_affected_rows($conn) === 0) {
                    $insert_sql = "INSERT INTO fx_data_datacve_mapping (namecve, published, modified, description, cvss_score, severity, site_id, updated_at, created_at) VALUES ('{$CVE_Code_esc}', '{$published_esc}', '{$modified_esc}', '{$desc_esc}', '{$score_esc}', '{$sev_esc}', 0, NOW(), NOW())";
                    mysqli_query($conn, $insert_sql);
                }

                if ($processed % $batchSize === 0) {
                    mysqli_query($conn, "COMMIT");
                    mysqli_query($conn, "START TRANSACTION");
                    $this->info("⏳ Processed: {$processed} CVEs | New={$count_new} / Update={$count_update} | Chunk time=" . round((microtime(true) - $chunkStart) / 60, 2) . " min");
                    $chunkStart = microtime(true);
                }
            }

            mysqli_query($conn, "COMMIT");
            $this->info("✅ Inserted new: {$count_new}, Updated: {$count_update}");

            // Start Mapping & Summary
            $this->processMappingAndSummary($conn);

            // Update Batch Job Progress
            if ($TransactionBatchjob) {
                $TransactionBatchjob->progress = 1;
                $TransactionBatchjob->transcation_date_end = now();
                $TransactionBatchjob->transcation_date = now();
                $TransactionBatchjob->save();
            }

            $this->info("🎯 MDCVEBatchJob finished successfully.");
        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    private function processMappingAndSummary($conn)
    {
        $this->info("🔁 Starting CVE Mapping & Summary...");
        
        $progress = CveAssetsProgress::first();
        $lastAssetId = $progress ? $progress->last_asset_id : 0;
        $lastCveName = $progress ? $progress->last_cve_name : null;

        $this->info("▶️ Resume from asset_id={$lastAssetId}");

        $assets = mysqli_query($conn, "SELECT * FROM fx_cve_assets WHERE id >= $lastAssetId ORDER BY id ASC");
        $totalAssets = mysqli_num_rows($assets);
        $this->info("🔍 Total Assets to process: {$totalAssets}");
        
        $processedAssets = 0;
        while ($row = mysqli_fetch_assoc($assets)) {
            $processedAssets++;
            if ($processedAssets % 100 == 0) {
                $this->info("⏳ Mapping Progress: {$processedAssets} / {$totalAssets} assets (Current ID: {$row['id']})");
            }
            $vendor = mysqli_real_escape_string($conn, $row['vendor']);
            $title  = mysqli_real_escape_string($conn, $row['title']);
            $version = mysqli_real_escape_string($conn, $row['version']);
            $edition = mysqli_real_escape_string($conn, $row['edition']);
            $site_id = $row['site_id'];

            $query = "SELECT * FROM fx_data_cveven WHERE vendor='$vendor' AND title='$title'";
            if (!in_array($version, ['-', '*', ''])) $query .= " AND version='$version'";
            if (!in_array($edition, ['-', '*', ''])) $query .= " AND edition='$edition'";

            $cves = mysqli_query($conn, $query);
            while ($cve = mysqli_fetch_assoc($cves)) {
                $name = $cve['namecve'];
                $exists = mysqli_query($conn, "SELECT 1 FROM fx_data_datacve_mapping_assets WHERE namecve='$name' AND site_id='$site_id' AND cve_asset_id='{$row['id']}'");
                if ($exists && mysqli_num_rows($exists) == 0) {
                    mysqli_query($conn, "INSERT IGNORE INTO fx_data_datacve_mapping_assets (namecve, cve_asset_id, site_id, code, updated_at, created_at) VALUES ('$name', '{$row['id']}', '$site_id', '" . $this->GUID() . "', NOW(), NOW())");
                }
            }

            CveAssetsProgress::query()->update(['last_asset_id' => $row['id'], 'updated_at' => now()]);
        }

        // Final step: Summary for each site
        $this->info("📊 Generating Site Summaries...");
        $sites = DB::table('site')->select('id', 'code')->where('active', 1)->get();
        foreach ($sites as $site) {
            $this->generateSummary($conn, $site->id, $site->code);
        }
        $this->generateSummary($conn, 0, 0); // Overall

        // Reset progress on successful completion so the next scheduled run starts fresh from 0
        CveAssetsProgress::query()->update(['last_asset_id' => 0, 'updated_at' => now()]);
    }

    private function generateSummary($conn, $site_id, $site_code)
    {
        $this->info("🔁 Summary for site={$site_code}...");
        
        // ✅ Optimized with JOIN
        $severityList = DB::table('data_datacve_mapping')
            ->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve')
            ->where('data_datacve_mapping_assets.site_id', $site_id)
            ->select('data_datacve_mapping.namecve', 'data_datacve_mapping.severity')
            ->groupBy('data_datacve_mapping.namecve', 'data_datacve_mapping.severity')
            ->get();

        DB::table('summary')->where('site', $site_code)->where('data_key_2', 'Vulnerability')->delete();

        $crit = $high = $med = $low = $info = 0;
        foreach ($severityList as $s) {
            switch (strtoupper($s->severity)) {
                case 'CRITICAL': $crit++; break;
                case 'HIGH': $high++; break;
                case 'MEDIUM': $med++; break;
                case 'LOW': $low++; break;
                default: $info++; break;
            }
        }

        DB::table('summary')->insert([
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Critical', 'data_value' => $crit, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'High', 'data_value' => $high, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Medium', 'data_value' => $med, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Low', 'data_value' => $low, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Information', 'data_value' => $info, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function insert_nvd($conn, $name, $pub, $mod, $desc, $score, $sev)
    {
        mysqli_query($conn, "INSERT INTO fx_data_datacve (namecve, published, modified, description, cvss_score, severity, updated_at, created_at) VALUES ('$name','$pub','$mod','$desc','$score','$sev',NOW(),NOW())");
    }

    private function update_nvd($conn, $name, $pub, $mod, $desc, $score, $sev)
    {
        mysqli_query($conn, "UPDATE fx_data_datacve SET published='$pub', modified='$mod', description='$desc', cvss_score='$score', severity='$sev', updated_at=NOW() WHERE namecve='$name'");
    }

    private function GUID()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
