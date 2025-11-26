<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Entities\TransactionBatchjob;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

use App\DataCveSource;


class MDCVEDataYear extends Command
{
    protected $signature = 'app:MDCVEDataYear {--year=}';
    protected $description = 'Download and import NVD CVE Data (JSON 2.0)';

    public function handle()
    {
        date_default_timezone_set("Asia/Bangkok");

        // === Update batchjob ===
        $batch = TransactionBatchjob::where('mode', 'MDCVEDataYear')->first();
        if ($batch) {
            $batch->progress = 2;
            $batch->transcation_date_start = now();
            $batch->transcation_date = now();
            $batch->save();
        }

        // === Connect Database ===
        $conn = mysqli_connect(
            env('DB_HOST'),
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            env('DB_PORT')
        );

        if (!$conn) {
            $this->error("❌ Cannot connect to MySQL");
            return;
        }

        ini_set('memory_limit', '-1');

        $year = $this->option('year') ?: date('Y');
        $tempPath = app_path() . "/Console/Commands/temp/";
        $zipPath  = "{$tempPath}nvdcve-2.0-{$year}.json.zip";
        $jsonPath = "{$tempPath}nvdcve-2.0-{$year}.json";

        // === Correct NVD 2.0 Feed URL ===
        $feedUrl = "https://nvd.nist.gov/feeds/json/cve/2.0/nvdcve-2.0-{$year}.json.zip";

        try {
            $this->info("📥 Downloading CVE Feed for {$year} ...");
            $data = @file_get_contents($feedUrl);
            if (!$data) throw new Exception("Cannot download NVD Feed. (403 / Not Found)");

            file_put_contents($zipPath, $data);

            // === Extract ZIP ===
            $this->info("📦 Extracting JSON file ...");
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($tempPath);
                $zip->close();
            } else {
                throw new Exception("Failed to open or extract zip file.");
            }

            // === Parse JSON (Streaming Mode) ===
            $this->info("🧩 Parsing JSON data (streaming mode) ...");

            // ✅ ใช้ Items::fromFile() จาก JsonMachine v1.x
            $vulnerabilities = \JsonMachine\Items::fromFile($jsonPath, [
                'pointer' => '/vulnerabilities'
            ]);



            // ตรวจว่าข้อมูลมีจริงไหม
            $firstItem = null;
            foreach ($vulnerabilities as $v) {
                $firstItem = $v;
                break;
            }
            if (!$firstItem) {
                throw new Exception("Invalid or empty JSON structure: 'vulnerabilities' not found");
            }

            // อ่านไฟล์ใหม่อีกครั้งเพื่อประมวลผลทั้งหมด
            $vulnerabilities = \JsonMachine\Items::fromFile($jsonPath, [
                'pointer' => '/vulnerabilities',
                'decoder' => new ExtJsonDecoder(true) // 👈 true = associative array
            ]);


            foreach ($vulnerabilities as $vuln) {
                $vuln = (array) $vuln;
                if (!isset($vuln['cve'])) continue;

                $cve = (array) $vuln['cve'];
                $CVE_Code = $cve['id'] ?? null;
                if (!$CVE_Code) continue;


                echo PHP_EOL . "=================================================================";
                echo PHP_EOL . "Processing: {$CVE_Code}" . PHP_EOL;

                // === Description ===
                $description_data = "";
                foreach ($cve['descriptions'] ?? [] as $desc) {
                    if (($desc['lang'] ?? '') === 'en') {
                        $description_data .= htmlspecialchars($desc['value'], ENT_QUOTES);
                    }
                }

                // === CVSS v3 / v2 Metrics ===
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

                $publishedDate = explode('T', $cve['published'] ?? '')[0] ?? '';
                $lastModifiedDate = explode('T', $cve['lastModified'] ?? '')[0] ?? '';

                $array_cpe_match = [];
                foreach ($cve['configurations'] ?? [] as $config) {
                    foreach ($config['nodes'] ?? [] as $node) {
                        foreach ($node['cpeMatch'] ?? [] as $cpe) {
                            if (!empty($cpe['vulnerable'])) {
                                $array_cpe_match[] = $cpe['criteria'];
                            }
                        }
                    }
                }

                foreach ($array_cpe_match as $vendor_text) {
                    $split = explode(':', $vendor_text);
                    $vendor_name  = $split[3] ?? '';
                    $product_name = $split[4] ?? '';
                    $product_version = $split[5] ?? '';
                    $product_edition = ($split[6] ?? '') === '*' ? '' : ($split[6] ?? '');


                    $sql_check = "SELECT namecve FROM fx_data_cveven 
                         WHERE namecve='{$CVE_Code}' AND rawtext='{$vendor_text}'";
                    $result = mysqli_query($conn, $sql_check);
                    $num = $result ? mysqli_num_rows($result) : 0;

                    if ($num == 0) {
                        $created_at = date("Y-m-d H:i:s");
                        $sql_insert = "INSERT INTO fx_data_cveven
                            (namecve, title, vendor, version, edition, rawtext, created_at)
                            VALUES ('{$CVE_Code}', '{$product_name}', '{$vendor_name}', '{$product_version}', '{$product_edition}', '{$vendor_text}', '{$created_at}')";
                        mysqli_query($conn, $sql_insert);
                    }
                }


                // === Insert / Update CVE data ===
                $description_data = mysqli_real_escape_string($conn, $description_data);
                $check_sql = "SELECT namecve FROM fx_data_datacve WHERE namecve='{$CVE_Code}'";
                $result = mysqli_query($conn, $check_sql);
                $exists = $result ? mysqli_num_rows($result) : 0;

                $now = date("Y-m-d H:i:s");

                $sourceQuery = "SELECT * FROM fx_data_cve_sources 
                WHERE namecve='{$CVE_Code}' AND source='command' LIMIT 1";
                $sourceResult = mysqli_query($conn, $sourceQuery);

                if (!$sourceResult) {
                    echo "Error: " . mysqli_error($conn) . "\n";
                    continue; // หรือ return
                }

                $cveSource = mysqli_fetch_assoc($sourceResult);

                $latestCve = null;
                if ($cveSource && !empty($cveSource['datacve_id'])) {
                    $cveQuery = "SELECT * FROM fx_data_datacve WHERE id={$cveSource['datacve_id']} LIMIT 1";
                    $cveResult = mysqli_query($conn, $cveQuery);

                    if ($cveResult) {
                        $latestCve = mysqli_fetch_assoc($cveResult);
                    }
                }

                $needNewRecord = false;

                // 2. เช็คว่าต้องสร้าง record ใหม่ไหม
                if ($latestCve) {
                    if ($latestCve['cvss_score'] != $baseScore || $latestCve['severity'] != $baseSeverity) {
                        $needNewRecord = true;
                    }
                } else {
                    $needNewRecord = true;
                }

                // 3. บันทึกข้อมูล
                if ($needNewRecord) {
                    // สร้าง record ใหม่
                    $sql_insert = "INSERT INTO fx_data_datacve
                    (namecve, published, modified, description, cvss_score, severity, created_at, updated_at)
                    VALUES ('{$CVE_Code}', '{$publishedDate}', '{$lastModifiedDate}',
                            '{$description_data}', '{$baseScore}', '{$baseSeverity}',
                            '{$now}', '{$now}')";

                    if (mysqli_query($conn, $sql_insert)) {
                        $newCveId = mysqli_insert_id($conn);

                        // Update หรือสร้าง source record
                        if ($cveSource) {
                            $sql_update_source = "UPDATE fx_data_cve_sources 
                                 SET datacve_id={$newCveId}, updated_at='{$now}'
                                 WHERE id={$cveSource['id']}";
                            mysqli_query($conn, $sql_update_source);
                        } else {
                            $sql_insert_source = "INSERT INTO fx_data_cve_sources
                            (namecve, source, datacve_id, created_at, updated_at)
                            VALUES ('{$CVE_Code}', 'command', {$newCveId}, '{$now}', '{$now}')";
                            mysqli_query($conn, $sql_insert_source);
                        }

                        echo "✓ สร้าง CVE ใหม่: {$CVE_Code}\n";
                    } else {
                        echo "Error insert: " . mysqli_error($conn) . "\n";
                    }
                } else {
                    // Update เฉพาะข้อมูลที่ไม่ใช่ score/severity
                    $sql_update = "UPDATE fx_data_datacve SET
                   published='{$publishedDate}',
                   modified='{$lastModifiedDate}',
                   description='{$description_data}',
                   updated_at='{$now}'
                   WHERE id={$latestCve['id']}";

                    if (mysqli_query($conn, $sql_update)) {
                        echo "✓ Update CVE: {$CVE_Code}\n";
                    } else {
                        echo "Error update: " . mysqli_error($conn) . "\n";
                    }
                }

                echo "✅ Imported CVE: {$CVE_Code}" . PHP_EOL;
            }

            $this->info("🎯 Import completed for {$year}");
            // Log::info("🎯 Import completed for {$year}");
        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }

        // === Update batchjob status ===
        if ($batch) {
            $batch->progress = 1;
            $batch->transcation_date_end = now();
            $batch->transcation_date = now();
            $batch->save();
        }
    }
}
