<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\TransactionScans;
use App\TransactionScansCveTemp;
use App\DataTypes;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Domain;
use Carbon\Carbon;
use Exception;

class DomainScan extends Command
{
    protected $signature = 'app:DomainScan {domain_id? : Optional Domain ID (if empty, scans all active)} {--save : Save results to database}';
    protected $description = 'Domain Security Scanner — Subdomain, Port, OS, CPE, CVE';

    private $client;
    private $subdomains = [];   // [{subdomain, ip_address, source}]
    private $ports = [];        // [{target, ip, port, protocol, service, version, banner, ssl_info}]
    private $osResults = [];    // [{target, ip, asn, asn_name, country, city}]
    private $cpeResults = [];   // [{target, port, vendor, product, version, cpe_uri}]
    private $cveResults = [];   // [{cve_id, target, cvss_score, severity, description, published, modified, affected_cpe}]

    public function handle()
    {
        $domainId = $this->argument('domain_id');

        if ($domainId) {
            $domainModel = Domain::find($domainId);
            if (!$domainModel) {
                $this->error("Domain ID not found: {$domainId}");
                return 1;
            }
            $this->processScan($domainModel);
        } else {
            $this->info("🚀 Scanning ALL active domains...");
            
            // Active Sites (active=1)
            $sites = SiteSettings::where('active', 1)->get();
            $total = 0;

            foreach ($sites as $site) {
                // Active Domains (status=1)
                $domains = Domain::where('site_id', $site->id)
                    ->where('status', 1)
                    ->get();

                foreach ($domains as $domainModel) {
                    $this->processScan($domainModel);
                    $total++;
                }
            }

            $this->info("");
            $this->info("🏁 Completely scanned {$total} domains.");
        }

        return 0;
    }

    private function processScan($domainModel)
    {
        // Reset state
        $this->subdomains = [];
        $this->ports      = [];
        $this->osResults  = [];
        $this->cpeResults = [];
        $this->cveResults = [];

        $domain   = $domainModel->domain;
        $siteId   = $domainModel->site_id;
        $domainId = $domainModel->id;

        $this->info("=== \xF0\x9F\x94\x8D Domain Security Scanner ===");
        $this->info("\xF0\x9F\x8C\x90 Target: {$domain}");
        $this->info("\xF0\x9F\x8F\xA2 Domain ID: {$domainId} | Site ID: {$siteId}");
        $this->info("");

        $this->client = new Client([
            'verify'  => false,
            'timeout' => 30,
        ]);

        $startTime = microtime(true);

        // Phase 1: Subdomain Discovery
        $this->info("--- 🎯 Phase 1: Subdomain Discovery ---");
        $this->discoverSubdomains($domain);

        // Phase 2: Censys Scan → Ports, Services, OS, CPE
        $this->info("");
        $this->info("--- 🔌 Phase 2: Censys Scan ---");
        $this->censysScan($domain);

        // Phase 3: CVE Search via NVD
        $this->info("");
        $this->info("--- 🚨 Phase 3: CVE Search ---");
        $this->searchCVE();

        // Summary
        $scanTime = round(microtime(true) - $startTime, 2);
        $this->printSummary($domain, $scanTime);

        // Export JSON (Commented out to save space)
        // $this->exportJson($domain, $scanTime);

        // Phase 4: Save to DB (only with --save flag)
        if ($this->option('save')) {
            $this->info("");
            $this->info("--- \xF0\x9F\x92\xBE Phase 4: Save to Database ---");
            $this->saveToDatabase($domain, $siteId, $domainId);
        } else {
            $this->info("");
            $this->warn("⚠️  Dry-run mode — ไม่ได้บันทึก DB");
            $this->warn("   เพิ่ม --save เพื่อบันทึก: php artisan app:DomainScan {$domainId} --save");
        }

        return 0;
    }

    private function exportJson($domain, $scanTime)
    {
        $data = [
            'target'     => $domain,
            'scan_time'  => $scanTime,
            'timestamp'  => date('Y-m-d H:i:s'),
            'subdomains' => $this->subdomains,
            'ports'      => array_map(function($p) {
                return [
                    'target'   => $p['target'],
                    'ip'       => $p['ip'],
                    'port'     => $p['port'],
                    'protocol' => $p['protocol'],
                    'service'  => $p['service'],
                    'version'  => $p['version'],
                    'ssl_info' => $p['ssl_info'] ? [
                        'version' => $p['ssl_info']['version_selected'] ?? null,
                        'cipher'  => $p['ssl_info']['cipher_selected'] ?? null,
                        'sha256'  => $p['ssl_info']['fingerprint_sha256'] ?? null,
                    ] : null,
                ];
            }, $this->ports),
            'os_results'  => $this->osResults,
            'cpe_results' => $this->cpeResults,
            'cve_results' => $this->cveResults,
        ];

        $dir = public_path('files/scans');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9.\-]/', '_', $domain);
        $filename = "domain_scan_{$safeName}_" . date('Ymd_His') . ".json";
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("");
        $this->info("📁 JSON saved: public/files/scans/{$filename}");
    }

    // =============================================
    //  Phase 4: Save to Database
    // =============================================

    private function saveToDatabase($domain, $siteId, $domainId)
    {
        $module   = 'domain_scanner';
        $saved    = 0;
        $skipped  = 0;

        // --- Subdomains + IP ---
        foreach ($this->subdomains as $sub) {
            // Subdomain record
            $r = $this->saveScanRecord($siteId, $domainId, $module, 'Subdomain', $sub['subdomain'], $sub['subdomain'], $sub['source']);
            $r ? $saved++ : $skipped++;

            // IP record (if resolved)
            if (!empty($sub['ip_address'])) {
                $r = $this->saveScanRecord($siteId, $domainId, $module, 'IP Address', $sub['ip_address'], $sub['subdomain'], $sub['source']);
                $r ? $saved++ : $skipped++;
            }
        }

        // --- Ports ---
        foreach ($this->ports as $p) {
            $rawData = $p['port'];
            $referent = $p['target']; // Just domain/target, no port
            $r = $this->saveScanRecord($siteId, $domainId, $module, 'Port', $rawData, $referent, 'censys');
            $r ? $saved++ : $skipped++;

            // Save IP Address from Port info
            if (!empty($p['ip'])) {
                $r = $this->saveScanRecord($siteId, $domainId, $module, 'IP Address', $p['ip'], $referent, 'censys');
                $r ? $saved++ : $skipped++;
            }

            // SSL records (break down version, cipher, sha256)
            if (!empty($p['ssl_info'])) {
                $referent = $p['target'];
                $ssl = $p['ssl_info'];
                
                if (!empty($ssl['version'])) {
                    $r = $this->saveScanRecord($siteId, $domainId, $module, 'SSL Version', $ssl['version'], $referent, 'censys');
                    $r ? $saved++ : $skipped++;
                }

                if (!empty($ssl['cipher'])) {
                    $r = $this->saveScanRecord($siteId, $domainId, $module, 'SSL Cipher', $ssl['cipher'], $referent, 'censys');
                    $r ? $saved++ : $skipped++;
                }

                if (!empty($ssl['sha256'])) {
                    $r = $this->saveScanRecord($siteId, $domainId, $module, 'SSL SHA256', $ssl['sha256'], $referent, 'censys');
                    $r ? $saved++ : $skipped++;
                }
            }
        }

        // --- OS/Network/Geo ---
        foreach ($this->osResults as $os) {
            $referent = $os['target'];
            
            // Save IP Address from OS info if available
            if (!empty($os['ip'])) {
                $r = $this->saveScanRecord($siteId, $domainId, $module, 'IP Address', $os['ip'], $referent, 'censys');
                $r ? $saved++ : $skipped++;
            }

            // Save OS Name/Version (Skip if name is empty)
            if (!empty($os['os_name'])) {
                $osRaw = $os['os_name'];
                if (!empty($os['os_version'])) $osRaw .= " " . $os['os_version'];
                $r = $this->saveScanRecord($siteId, $domainId, $module, 'OS', $osRaw, $referent, 'censys');
                $r ? $saved++ : $skipped++;
            }

            // ASN -> Network
            if (!empty($os['asn'])) {
                $asnRaw = "AS{$os['asn']} {$os['asn_name']}";
                $r = $this->saveScanRecord($siteId, $domainId, $module, 'Network', $asnRaw, $referent, 'censys');
                $r ? $saved++ : $skipped++;
            }
        }

        // --- CPE ---
        foreach ($this->cpeResults as $cpe) {
            $ver = $cpe['version'] ?: '*';
            $rawData = "{$cpe['vendor']}:{$cpe['product']}:{$ver}";
            $referent = $cpe['target']; // Just domain/target, no port

            $r = $this->saveScanRecord($siteId, $domainId, $module, 'CPE', $rawData, $referent, 'censys');
            $r ? $saved++ : $skipped++;
        }

        // --- CVE details first (to check for new ones) ---
        if (!empty($this->cveResults)) {
            $cveSaved = 0;
            $cveSkipped = 0;
            foreach ($this->cveResults as $cve) {
                $exists = TransactionScansCveTemp::where('site_id', $siteId)
                    ->where('domain_id', $domainId)
                    ->where('namecve', $cve['cve_id'])
                    ->first();

                if ($exists) {
                    $cveSkipped++;
                    continue;
                }

                $temp = new TransactionScansCveTemp();
                $temp->code        = Str::uuid()->toString();
                $temp->site_id     = $siteId;
                $temp->domain_id   = $domainId;
                $temp->namecve     = $cve['cve_id'];
                $temp->severity    = $cve['severity'];
                $temp->cvss_score  = $cve['cvss_score'];
                $temp->description = $cve['description'];
                $temp->published   = $cve['published'];
                $temp->modified    = $cve['modified'];
                // Clean target (no port)
                $temp->target      = explode(':', $cve['target'])[0];
                $temp->affected_cpe = $cve['affected_cpe'];
                $temp->source      = 'nist_nvd';
                $temp->is_mapped   = 0;
                $temp->save();
                $cveSaved++;
            }

            $this->info("  📋 CVE Temp: {$cveSaved} saved, {$cveSkipped} skipped (duplicate)");

            // --- CVE main pointer → transaction_scans ---
            // If we saved at least one NEW CVE ID, force status to 2 (New)
            // SaveScanRecord will handle the status logic internally but we need to know if it's "New" overall
            $r = $this->saveCvePointerRecord($siteId, $domainId, $module, 'CVE', $domain, $domain, 'nist_nvd', ($cveSaved > 0));
            $r ? $saved++ : $skipped++;
        }

        $this->info("  ✅ transaction_scans: {$saved} saved, {$skipped} skipped (duplicate)");
    }

    private function saveScanRecord($siteId, $domainId, $module, $dataType, $rawData, $referent, $source)
    {
        return $this->processScanSave($siteId, $domainId, $module, $dataType, $rawData, $referent, $source, false);
    }

    private function saveCvePointerRecord($siteId, $domainId, $module, $dataType, $rawData, $referent, $source, $forceNew)
    {
        return $this->processScanSave($siteId, $domainId, $module, $dataType, $rawData, $referent, $source, $forceNew);
    }

    private function ensureDataTypeExists($value)
    {
        if (empty($value)) return;

        $exists = DataTypes::whereRaw('LOWER(value) = ?', [strtolower($value)])->first();
        if ($exists) return;

        $dt = new DataTypes();
        $dt->code   = Str::uuid()->toString();
        $dt->status = 1;
        $dt->value  = $value;
        $dt->save();

        $this->line("     📝 DataType registered: {$value}");
    }

    private function processScanSave($siteId, $domainId, $module, $dataType, $rawData, $referent, $source, $forceNew = false)
    {
        // Auto-register data type to data_types table
        $this->ensureDataTypeExists($dataType);

        // Strip port from referent if it exists (e.g. "domain.com:80" -> "domain.com")
        $referent = explode(':', $referent)[0];

        // Dedup check: same site + domain + module + data_type + raw_data
        $exists = TransactionScans::where('site_id', $siteId)
            ->where('domain_id', $domainId)
            ->where('module', $module)
            ->where('data_type', $dataType)
            ->where('raw_data', $rawData)
            ->first();

        if ($exists) {
            $exists->updated_at = Carbon::now();
            
            // Always ensure referent is clean (no port)
            if ($exists->referent !== $referent) {
                $exists->referent = $referent;
            }

            // If forceNew is true, set status to 2 (New), otherwise 1 (Discovered)
            $exists->status = $forceNew ? 2 : 1; 
            $exists->save();
            return false; // already existed/updated
        }

        $record = new TransactionScans();
        $record->code      = Str::uuid()->toString();
        $record->site_id   = $siteId;
        $record->domain_id = $domainId;
        $record->module    = $module;
        $record->data_type = $dataType;
        $record->raw_data  = (string)$rawData;
        $record->referent  = $referent;
        $record->source    = $source;
        $record->status    = 2; // New
        $record->save();
        return true; // saved
    }

    // =============================================
    //  Phase 1: Subdomain Discovery
    // =============================================

    private function discoverSubdomains($domain)
    {
        // 1a. Certificate Transparency (crt.sh)
        $this->crtShDiscovery($domain);

        // 1b. DNS Enumeration
        $this->dnsEnumeration($domain);

        // 1c. VirusTotal (if API key available)
        $this->virusTotalDiscovery($domain);

        // 1d. Resolve IPs for all subdomains
        $this->resolveIPs();

        $this->info("  ✅ Total subdomains found: " . count($this->subdomains));
    }

    private function crtShDiscovery($domain)
    {
        $this->info("  🔐 Certificate Transparency (crt.sh)...");

        try {
            $response = $this->client->get("https://crt.sh/?q=%25.{$domain}&output=json", [
                'timeout' => 15,
            ]);

            $data = json_decode($response->getBody(), true);
            if (!is_array($data)) return;

            $found = 0;
            foreach ($data as $cert) {
                $nameValue = $cert['name_value'] ?? '';
                if (!$nameValue) continue;

                // split multiple domains
                $names = explode("\n", $nameValue);
                foreach ($names as $name) {
                    $name = strtolower(trim($name));

                    // skip wildcards and self
                    if (strpos($name, '*') !== false) continue;
                    if ($name === $domain) continue;
                    if (!$this->endsWith($name, ".{$domain}")) continue;

                    if (!$this->isSubdomainKnown($name)) {
                        $this->subdomains[] = [
                            'subdomain'  => $name,
                            'ip_address' => null,
                            'source'     => 'certificate_transparency',
                        ];
                        $found++;
                    }
                }
            }

            $this->info("     Found {$found} subdomains from crt.sh");
        } catch (Exception $e) {
            $this->warn("     crt.sh error: " . $e->getMessage());
        }
    }

    private function dnsEnumeration($domain)
    {
        $this->info("  🌐 DNS Enumeration...");

        $found = 0;
        $recordTypes = [DNS_MX, DNS_NS, DNS_CNAME, DNS_SRV];

        foreach ($recordTypes as $type) {
            try {
                $records = @dns_get_record($domain, $type);
                if (!$records) continue;

                foreach ($records as $record) {
                    $target = null;

                    if (isset($record['target'])) {
                        $target = rtrim($record['target'], '.');
                    }

                    if ($target && $this->endsWith($target, ".{$domain}") && $target !== $domain) {
                        if (!$this->isSubdomainKnown($target)) {
                            $this->subdomains[] = [
                                'subdomain'  => $target,
                                'ip_address' => null,
                                'source'     => 'dns_enumeration',
                            ];
                            $found++;
                        }
                    }
                }
            } catch (Exception $e) {
                // skip
            }
        }

        $this->info("     Found {$found} subdomains from DNS");
    }

    private function virusTotalDiscovery($domain)
    {
        $apiKey = env('VIRUSTOTAL_API_KEY');
        if (!$apiKey) {
            $this->line("  🔍 VirusTotal: skipped (no API key)");
            return;
        }

        $this->info("  🔍 VirusTotal API...");

        try {
            $response = $this->client->get("https://www.virustotal.com/api/v3/domains/{$domain}", [
                'headers' => [
                    'x-apikey'     => $apiKey,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $attributes = $data['data']['attributes'] ?? [];
            $dnsRecords = $attributes['last_dns_records'] ?? [];

            $found = 0;
            foreach ($dnsRecords as $record) {
                $type  = $record['type'] ?? '';
                $value = rtrim($record['value'] ?? '', '.');

                if (in_array($type, ['CNAME', 'MX', 'NS']) && $value) {
                    if ($this->endsWith($value, ".{$domain}") && $value !== $domain) {
                        if (!$this->isSubdomainKnown($value)) {
                            $this->subdomains[] = [
                                'subdomain'  => $value,
                                'ip_address' => null,
                                'source'     => 'virustotal',
                            ];
                            $found++;
                        }
                    }
                }
            }

            $this->info("     Found {$found} subdomains from VirusTotal");
        } catch (Exception $e) {
            $this->warn("     VirusTotal error: " . $e->getMessage());
        }

        sleep(1); // rate limit
    }

    private function resolveIPs()
    {
        $this->info("  📡 Resolving IPs...");

        foreach ($this->subdomains as &$sub) {
            if ($sub['ip_address']) continue;

            $ip = @gethostbyname($sub['subdomain']);
            if ($ip !== $sub['subdomain']) {
                $sub['ip_address'] = $ip;
            }
        }
        unset($sub);
    }

    // =============================================
    //  Phase 2: Censys Scan
    // =============================================

    private function censysScan($domain)
    {
        $bearerToken = env('CENSYS_BEARER_TOKEN');
        $orgId       = env('CENSYS_ORG_ID');

        if (!$bearerToken || !$orgId) {
            $this->warn("  ⚠️ Censys credentials not set (CENSYS_BEARER_TOKEN, CENSYS_ORG_ID)");
            $this->warn("     Skipping port/service scan");
            return;
        }

        // Collect unique IPs to scan
        $ipsToScan = [];

        // Main domain IP
        $mainIp = @gethostbyname($domain);
        if ($mainIp && $mainIp !== $domain) {
            $ipsToScan[(string)$mainIp] = $domain;
        }

        // Subdomain IPs (limit first 5)
        $count = 0;
        foreach ($this->subdomains as $sub) {
            if (!empty($sub['ip_address']) && !isset($ipsToScan[(string)$sub['ip_address']])) {
                $ipsToScan[(string)$sub['ip_address']] = $sub['subdomain'];
                $count++;
                if ($count >= 5) break;
            }
        }

        $this->info("  🔎 Scanning " . count($ipsToScan) . " unique IPs via Censys API...");

        foreach ($ipsToScan as $ip => $target) {
            $this->fetchCensysHost($ip, $target, $bearerToken, $orgId);
            usleep(800000); // 0.8s rate limit
        }

        $this->info("  ✅ Ports: " . count($this->ports) . " | OS: " . count($this->osResults) . " | CPE: " . count($this->cpeResults));
    }

    private function fetchCensysHost($ip, $target, $bearerToken, $orgId)
    {
        try {
            $url = "https://api.platform.censys.io/v3/global/asset/host/{$ip}";

            $response = $this->client->get($url, [
                'headers' => [
                    'Accept'          => 'application/vnd.censys.api.v3.host.v1+json',
                    'Authorization'   => "Bearer {$bearerToken}",
                    'X-Organization-ID' => $orgId,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $resource = $data['result']['resource'] ?? [];

            // --- Extract Services → Ports + CPE ---
            $services = $resource['services'] ?? [];
            foreach ($services as $service) {
                $port      = $service['port'] ?? 0;
                $protocol  = $service['transport_protocol'] ?? 'tcp';
                $svcName   = $service['service_name'] ?? ($service['protocol'] ?? 'unknown');
                $banner    = $service['banner'] ?? '';
                $sslInfo   = $service['tls'] ?? null;

                // Port record
                $this->ports[] = [
                    'target'   => $target,
                    'ip'       => $ip,
                    'port'     => $port,
                    'protocol' => $protocol,
                    'service'  => $svcName,
                    'version'  => null,
                    'banner'   => $banner,
                    'ssl_info' => $sslInfo,
                ];

                // Software → CPE
                $software = $service['software'] ?? [];
                if (is_array($software)) {
                    foreach ($software as $sw) {
                        if (!is_array($sw)) continue;
                        $vendor  = $sw['vendor'] ?? '';
                        $product = $sw['product'] ?? '';
                        $version = $sw['version'] ?? null;

                        if ($vendor || $product) {
                            $cpeUri = "cpe:2.3:a:{$vendor}:{$product}:" . ($version ?: '*');

                            // Update port version from software
                            $lastKey = count($this->ports) - 1;
                            if ($this->ports[$lastKey]['version'] === null) {
                                $this->ports[$lastKey]['version'] = $version;
                            }

                            // Avoid duplicate CPE
                            if (!$this->isCpeKnown($target, $port, $vendor, $product)) {
                                $this->cpeResults[] = [
                                    'target'  => $target,
                                    'port'    => $port,
                                    'vendor'  => $vendor,
                                    'product' => $product,
                                    'version' => $version,
                                    'cpe_uri' => $cpeUri,
                                ];
                            }
                        }
                    }
                }
            }

            // --- Extract Autonomous System + Location + OS → OS record ---
            $asn      = $resource['autonomous_system'] ?? null;
            $location = $resource['location'] ?? null;
            $os       = $data['result']['operating_system'] ?? [];

            if ($asn || $location || $os) {
                $this->osResults[] = [
                    'target'      => $target,
                    'ip'          => $ip,
                    'asn'         => $asn['asn'] ?? null,
                    'asn_name'    => $asn['description'] ?? ($asn['name'] ?? ''),
                    'country'     => $location['country'] ?? '',
                    'city'        => $location['city'] ?? '',
                    'os_name'     => $os['product'] ?? ($os['name'] ?? null),
                    'os_family'   => $os['family'] ?? null,
                    'os_version'  => $os['version'] ?? null,
                    'os_vendor'   => $os['vendor'] ?? null,
                ];
            }

            $this->line("     ✔ {$target} ({$ip}): " . count($services) . " services");

        } catch (Exception $e) {
            $code = 0;
            if (method_exists($e, 'getCode')) $code = $e->getCode();
            $this->warn("     ✗ {$target} ({$ip}): error {$code} — " . $e->getMessage());
        }
    }

    // =============================================
    //  Phase 3: CVE Search (NVD API)
    // =============================================

    private function searchCVE()
    {
        if (empty($this->cpeResults)) {
            $this->warn("  ⚠️ No CPE data to search CVEs for");
            return;
        }

        $nvdApiKey = env('NVD_API_KEY');

        // Group CPEs by vendor+product to avoid duplicate NVD calls
        $unique = [];
        foreach ($this->cpeResults as $cpe) {
            $key = strtolower($cpe['vendor'] . ':' . $cpe['product']);
            if (!isset($unique[$key])) {
                $unique[$key] = $cpe;
            }
        }

        $this->info("  🔎 Searching NVD for " . count($unique) . " unique vendor:product pairs...");

        $nvdClient = new Client([
            'base_uri' => 'https://services.nvd.nist.gov/',
            'timeout'  => 30,
            'verify'   => false,
        ]);

        foreach ($unique as $key => $cpe) {
            $keyword = "{$cpe['vendor']} {$cpe['product']}";
            $this->line("     🔍 {$keyword}...");

            try {
                $params = [
                    'keywordSearch'  => $keyword,
                    'resultsPerPage' => 20,
                ];

                $headers = [];
                if ($nvdApiKey) {
                    $headers['apiKey'] = $nvdApiKey;
                }

                $response = $nvdClient->get('rest/json/cves/2.0', [
                    'query'   => $params,
                    'headers' => $headers,
                ]);

                $data = json_decode($response->getBody(), true);
                $vulns = $data['vulnerabilities'] ?? [];

                $found = 0;
                foreach ($vulns as $vuln) {
                    $cveData = $vuln['cve'] ?? null;
                    if (!$cveData) continue;

                    $cveId     = $cveData['id'] ?? '';
                    $published = explode('T', $cveData['published'] ?? '')[0];
                    $modified  = explode('T', $cveData['lastModified'] ?? '')[0];

                    // Description (EN)
                    $desc = '';
                    foreach ($cveData['descriptions'] ?? [] as $d) {
                        if (($d['lang'] ?? '') === 'en') {
                            $desc = $d['value'];
                            break;
                        }
                    }

                    // CVSS Score + Severity
                    $cvssScore    = 0;
                    $cvssSeverity = '';
                    $metrics = $cveData['metrics'] ?? [];

                    foreach (['cvssMetricV40', 'cvssMetricV31', 'cvssMetricV30'] as $ver) {
                        if (isset($metrics[$ver][0]['cvssData'])) {
                            $cvss = $metrics[$ver][0]['cvssData'];
                            $cvssScore    = $cvss['baseScore'] ?? 0;
                            $cvssSeverity = $cvss['baseSeverity'] ?? '';
                            break;
                        }
                    }

                    // Fallback V2
                    if ($cvssScore == 0 && isset($metrics['cvssMetricV2'][0])) {
                        $cvss = $metrics['cvssMetricV2'][0]['cvssData'];
                        $cvssScore    = $cvss['baseScore'] ?? 0;
                        $cvssSeverity = $metrics['cvssMetricV2'][0]['baseSeverity'] ?? '';
                    }

                    // Avoid duplicate CVE IDs
                    if (!$this->isCveKnown($cveId)) {
                        $this->cveResults[] = [
                            'cve_id'       => $cveId,
                            'target'       => $cpe['target'],
                            'cvss_score'   => $cvssScore,
                            'severity'     => strtoupper($cvssSeverity),
                            'description'  => $desc,
                            'published'    => $published,
                            'modified'     => $modified,
                            'affected_cpe' => "{$cpe['vendor']}:{$cpe['product']}:" . ($cpe['version'] ?: '*'),
                        ];
                        $found++;
                    }
                }

                $this->line("        → {$found} CVEs");

            } catch (Exception $e) {
                $this->warn("        ✗ NVD error: " . $e->getMessage());
            }

            // NVD rate limit: 0.6s with key, 6s without
            sleep($nvdApiKey ? 1 : 6);
        }

        $this->info("  ✅ Total CVEs found: " . count($this->cveResults));
    }

    // =============================================
    //  Summary
    // =============================================

    private function printSummary($domain, $scanTime)
    {
        $this->info("");
        $this->info("╔══════════════════════════════════════════════╗");
        $this->info("║          📊 SCAN SUMMARY                    ║");
        $this->info("╠══════════════════════════════════════════════╣");
        $this->info("║ Target:     {$domain}");
        $this->info("║ Subdomains: " . count($this->subdomains));
        $this->info("║ Ports:      " . count($this->ports));
        $this->info("║ OS/ASN:     " . count($this->osResults));
        $this->info("║ CPE:        " . count($this->cpeResults));
        $this->info("║ CVE:        " . count($this->cveResults));
        $this->info("║ Time:       {$scanTime}s");
        $this->info("╚══════════════════════════════════════════════╝");

        // Detail: Subdomains
        if ($this->subdomains) {
            $this->info("");
            $this->info("📋 Subdomains:");
            foreach ($this->subdomains as $sub) {
                $ip = $sub['ip_address'] ?? 'N/A';
                $this->line("   {$sub['subdomain']} → {$ip} ({$sub['source']})");
            }
        }

        // Detail: Ports
        if ($this->ports) {
            $this->info("");
            $this->info("🔌 Open Ports:");
            foreach ($this->ports as $p) {
                $ver = $p['version'] ? " v{$p['version']}" : '';
                $this->line("   {$p['target']}:{$p['port']} ({$p['protocol']}) — {$p['service']}{$ver}");
            }
        }

        // Detail: OS/ASN
        if ($this->osResults) {
            $this->info("");
            $this->info("\xF0\x9F\x96\xA5\xEF\xB8\x8F OS / ASN / Geo:");
            foreach ($this->osResults as $os) {
                // OS Name & Version
                $info = [];
                if (!empty($os['os_name'])) $info[] = $os['os_name'];
                if (!empty($os['os_version'])) $info[] = $os['os_version'];
                $osStr = implode(' ', $info);

                if ($osStr) {
                    $this->line("   {$os['target']} ({$os['ip']}) — OS: {$osStr}");
                }

                // ASN & Geo
                if (!empty($os['asn'])) {
                    $this->line("   {$os['target']} ({$os['ip']}) — AS{$os['asn']} {$os['asn_name']} - {$os['city']}, {$os['country']}");
                }
            }
        }

        // Detail: CPE
        if ($this->cpeResults) {
            $this->info("");
            $this->info("🎯 CPE Components:");
            foreach ($this->cpeResults as $cpe) {
                $ver = $cpe['version'] ?: '*';
                $this->line("   {$cpe['target']}:{$cpe['port']} — {$cpe['vendor']}:{$cpe['product']}:{$ver}");
            }
        }

        // Detail: CVEs (top 10)
        if ($this->cveResults) {
            $this->info("");
            $critical = array_filter($this->cveResults, function($c) { return $c['severity'] === 'CRITICAL'; });
            $high     = array_filter($this->cveResults, function($c) { return $c['severity'] === 'HIGH'; });
            $this->info("🚨 CVEs: " . count($this->cveResults) . " total (🔴 " . count($critical) . " Critical, 🟠 " . count($high) . " High)");

            // Sort by score desc, show top 10
            usort($this->cveResults, function($a, $b) { return $b['cvss_score'] - $a['cvss_score']; });
            $top = array_slice($this->cveResults, 0, 10);

            $icons = ['CRITICAL' => '🔴', 'HIGH' => '🟠', 'MEDIUM' => '🟡', 'LOW' => '🟢'];
            foreach ($top as $cve) {
                $icon = isset($icons[$cve['severity']]) ? $icons[$cve['severity']] : '⚪';
                $desc = mb_substr($cve['description'], 0, 80) . '...';
                $this->line("   {$icon} {$cve['cve_id']} | {$cve['severity']} {$cve['cvss_score']} | {$desc}");
            }

            if (count($this->cveResults) > 10) {
                $this->line("   ... and " . (count($this->cveResults) - 10) . " more");
            }
        }
    }

    // =============================================
    //  Helpers
    // =============================================

    private function isSubdomainKnown($name)
    {
        foreach ($this->subdomains as $sub) {
            if ($sub['subdomain'] === $name) return true;
        }
        return false;
    }

    private function isCpeKnown($target, $port, $vendor, $product)
    {
        foreach ($this->cpeResults as $cpe) {
            if ($cpe['target'] === $target && $cpe['port'] === $port
                && $cpe['vendor'] === $vendor && $cpe['product'] === $product) {
                return true;
            }
        }
        return false;
    }

    private function isCveKnown($cveId)
    {
        foreach ($this->cveResults as $cve) {
            if ($cve['cve_id'] === $cveId) return true;
        }
        return false;
    }

    private function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}
