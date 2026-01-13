<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Log;

class IndicatorCheckService
{
    // API Keys Configuration
    // API Keys Configuration
    const VT_KEYS = [
        "8d1a95435a78ada270e1a4ee4abb8a87d69870b725b0ca9d92bf8ce314e0d953",
        "0c2ff4706b911810baaa4548c625c6b2d94c640faa5b99226069cbd7be2f329e",
        "2686458ccb2477cbb4a439950b59a3207c409fb7ad49704db212c215bc783faf",
        "1f28be66c766cb6a1f44d4e5b5e56a0eda63bc648028ef669ec0186e096bb9d4",
        "e5639402525b6e101e4a0a303684b3f094c9e01bb109b7d8fd5a9d8fc63aa66d",
    ];

    const OTX_KEYS = [
        "8d1a9f623e1698c37cf276712cec4c861f2323de2f8f27be5a53c6c5aa5890f3c",
        "ff67d575f75f39192f7b0b810f145a863860ffc9f2141587be8f76cb7d9e5fd4",
        "9c0f0f8849040301996c9041b9ebbf3a3ce7c1d6c4017769ee205b4505a67d3f",
        "ed575d32392769a5269d68e2bfd72e33ce1b5a8f865743f5c762a36c88030915",
        "3dde890f24a4668e4449e63b90cc40dc97597d92aa513c2998dee4a22f53ff91",
    ];

    const ABUSE_KEYS = [
        "718e46e84705145f16f57f5cd6c8cc9c3512e4aacbbc8ba0f1a010f7d8f10b97453d7c039c9180fe",
        "9db7f656bdc477ee447dabea6b3238c55c36cdf7b91e801bf80f2893cd2b1c3d21deae8bb711e081",
        "e6b0d45966d397554001d1ee222f78ac8b8298a1ef5c615d58bcdb040aafbd8df2e1a70279054ab2",
        "b97b0e5c7358a6f4d9d73fb0936997ee9145dea1646bc165d3de4658245bed918e69a74edff7aa2b",
        "f8444b91ac2a18e2ebd3124bb417b3e700c515b1fb86669d38b52a4d4012f5b05168dc81d13aa510",
    ];

    const TF_KEYS = [
        "d349c3c727f4beb4077dc31bac1fad81c0fd283749e5d0ff",
        "909914accb7ab0d1ac0536e2146535262dbda1bee66aa601",
        "f79a27568e2c280ac98e5ab17eaa46c50dc90164c30fa4d7",
    ];

    const RST_KEYS = [
        "dRe8o8bAfInJ5Bne6NtPmjR8UF1HCsNKESF5s-XBB_LOVZOOcHrgxw",
        "9TllfBoGN9b6Oy96e9RKqGWU5k3VAUh2KJiEO4unV7uCTlMbLbbN1A",
        "U-fmKoXw7tT-m85-4cnO_qcXAE8iYYwaDve3PF4yuke6DSFoZD78Xw",
    ];

    // Risk Scoring Configuration
    // Exact match from Python Source
    const THRESHOLDS = [
        "vt" => [
            [10, 10], [7, 8], [4, 6], [2, 4], [1, 2] // [malicious_count, score]
        ],
        "abuse_score" => [
            [80, 5], [40, 4], [25, 2], [15, 1]
        ],
        "abuse_reports" => [
            [50, 5], [20, 3], [10, 2], [5, 1]
        ],
        "tf" => [
            [95, 10], [85, 8], [75, 6], [50, 4], [25, 2]
        ],
        "otx" => [
            [50, 10], [30, 8], [20, 6], [10, 4], [5, 2], [1, 1]
        ]
    ];

    const WEIGHTS = [
        "vt" => 0.35, "abuse" => 0.20, "tf" => 0.10, "otx" => 0.25, "rst" => 0.10
    ];

    /**
     * Check a single IOC asynchronously
     * Returns a Promise that resolves to the result array
     */
    public function checkIocAsync(Client $client, $ioc, $type)
    {
        $promises = [
            'vt' => $this->checkVirusTotalAsync($client, $ioc, $type),
            'abuse' => ($type === 'ip') ? $this->checkAbuseIPDBAsync($client, $ioc) : Promise\Create::promiseFor([]),
            'otx' => $this->checkOTXAsync($client, $ioc, $type),
            'tf' => $this->checkThreatFoxAsync($client, $ioc, $type),
            'rst' => $this->checkRSTCloudAsync($client, $ioc, $type)
        ];

        return Utils::all($promises)->then(function ($results) use ($ioc, $type) {
            $vtResult = $results['vt'];
            $abuseResult = $results['abuse'];
            $otxResult = $results['otx'];
            $tfResult = $results['tf'];
            $rstResult = $results['rst'];

            list($totalScore, $riskLevel, $activeScores, $activeWeights) = $this->calculateRisk(
                $vtResult, $abuseResult, $tfResult, $otxResult,
                ['score' => $rstResult['score'] ?? null], 
                $type
            );

            return [
                "ioc" => $ioc,
                "type" => $type,
                "vt" => $vtResult,
                "abuse" => $abuseResult,
                "threatfox" => $tfResult,
                "otx" => $otxResult,
                "rstcloud_score" => $rstResult['score'] ?? 'N/A',
                "rstcloud_threat" => $rstResult['threat'] ?? [],
                "total_score" => $totalScore,
                "risk_level" => $riskLevel,
                "debug_scores" => $activeScores,
                "debug_weights" => $activeWeights
            ];
        });
    }

    // --- Internal Async API Methods ---

    protected function checkVirusTotalAsync($client, $ioc, $type)
    {
        $key = $this->getKey('VT');
        $headers = ['x-apikey' => $key];
        
        $url = "";
        if ($type === 'ip') $url = "https://www.virustotal.com/api/v3/ip_addresses/{$ioc}";
        elseif ($type === 'domain') $url = "https://www.virustotal.com/api/v3/domains/{$ioc}";
        elseif (in_array($type, ['md5', 'sha1', 'sha256'])) $url = "https://www.virustotal.com/api/v3/files/{$ioc}";
        elseif ($type === 'url') {
            $id = rtrim(strtr(base64_encode($ioc), '+/', '-_'), '=');
            $url = "https://www.virustotal.com/api/v3/urls/{$id}";
        } else {
            return Promise\Create::promiseFor(["malicious" => 0, "unique_results" => [], "suspicious" => 0]);
        }

        return $client->getAsync($url, ['headers' => $headers, 'timeout' => 30, 'connect_timeout' => 15])->then(
            function ($response) {
                $data = json_decode($response->getBody(), true);
                $attributes = $data['data']['attributes'] ?? [];
                $stats = $attributes['last_analysis_stats'] ?? [];
                $analysisResults = $attributes['last_analysis_results'] ?? [];
                
                $uniqueResults = [];
                foreach ($analysisResults as $res) {
                    if (!empty($res['result'])) $uniqueResults[] = $res['result'];
                }
                return [
                    "malicious" => $stats['malicious'] ?? 0,
                    "suspicious" => $stats['suspicious'] ?? 0,
                    "unique_results" => array_unique($uniqueResults)
                ];
            },
            function ($exception) use ($ioc) {
                Log::warning("VT API Error for {$ioc}: " . $exception->getMessage());
                return ["malicious" => 0, "unique_results" => [], "suspicious" => 0];
            }
        );
    }

    protected function checkAbuseIPDBAsync($client, $ip) 
    {
        $key = $this->getKey('ABUSE');
        return $client->getAsync("https://api.abuseipdb.com/api/v2/check", [
            'query' => ['ipAddress' => $ip, 'maxAgeInDays' => 120],
            'headers' => ['Key' => $key, 'Accept' => 'application/json'],
            'timeout' => 30,
            'connect_timeout' => 15
        ])->then(
            function ($response) {
                $data = json_decode($response->getBody(), true);
                return [
                    "score" => $data['data']['abuseConfidenceScore'] ?? 0,
                    "total_reports" => $data['data']['totalReports'] ?? 0
                ];
            },
            function ($exception) use ($ip) {
                Log::warning("AbuseIPDB Error for {$ip}: " . $exception->getMessage());
                return ["score" => "-", "total_reports" => 0];
            }
        );
    }

    protected function checkOTXAsync($client, $ioc, $type)
    {
        $key = $this->getKey('OTX');
        $url = "";
        $section = "general";
        
        if ($type === 'ip') $url = "https://otx.alienvault.com/api/v1/indicators/IPv4/{$ioc}/{$section}";
        elseif ($type === 'domain') $url = "https://otx.alienvault.com/api/v1/indicators/domain/{$ioc}/{$section}";
        elseif ($type === 'hostname') $url = "https://otx.alienvault.com/api/v1/indicators/hostname/{$ioc}/{$section}";
        elseif (in_array($type, ['md5', 'sha1', 'sha256'])) {
            $section = "analysis";
            $url = "https://otx.alienvault.com/api/v1/indicators/file/{$ioc}/{$section}";
        }
        elseif ($type === 'url') $url = "https://otx.alienvault.com/api/v1/indicators/url/{$ioc}/{$section}";
        else {
            return Promise\Create::promiseFor(["pulse_count" => 0]);
        }

        return $client->getAsync($url, ['headers' => ['X-OTX-API-KEY' => $key], 'timeout' => 30, 'connect_timeout' => 15])->then(
            function ($response) {
                $data = json_decode($response->getBody(), true);
                return ["pulse_count" => $data['pulse_info']['count'] ?? 0];
            },
            function ($exception) {
                return ["pulse_count" => 0];
            }
        );
    }

    protected function checkThreatFoxAsync($client, $ioc, $type)
    {
        $key = $this->getKey('TF');
        $body = ["query" => "search_ioc", "search_term" => $ioc];
        
        return $client->postAsync("https://threatfox-api.abuse.ch/api/v1/", [
            'json' => $body,
            'headers' => ['Auth-Key' => $key],
            'timeout' => 30,
            'connect_timeout' => 15
        ])->then(
            function ($response) use ($ioc) {
                $data = json_decode($response->getBody(), true);
                
                if (($data['query_status'] ?? '') === 'ok') {
                    $maxConf = 0;
                    $tags = [];
                    foreach ($data['data'] as $entry) {
                        if (isset($entry['confidence_level']) && $entry['confidence_level'] > $maxConf) {
                            $maxConf = $entry['confidence_level'];
                        }
                        
                        // Collect tags
                        if (!empty($entry['malware_printable'])) {
                            $tags[] = $entry['malware_printable'];
                        }
                        if (!empty($entry['threat_type'])) {
                            $tags[] = $entry['threat_type'];
                        }
                    }
                    return [
                        "confidence_level" => $maxConf,
                        "tags" => array_unique($tags)
                    ];
                }
                return ["confidence_level" => 0];
            },
            function ($exception) use ($ioc) {
                return ["confidence_level" => 0];
            }
        );
    }

    protected function checkRSTCloudAsync($client, $ioc, $type)
    {
        $key = $this->getKey('RST');
        
        $endpointType = '';
        if ($type === 'ip') $endpointType = 'ip';
        elseif ($type === 'domain') $endpointType = 'domain';
        else return Promise\Create::promiseFor(["score" => 0, "threat" => []]);

        $url = "https://api.rstcloud.net/v1/{$endpointType}/{$ioc}";
        
        return $client->getAsync($url, ['headers' => ['x-api-key' => $key], 'timeout' => 30, 'connect_timeout' => 15])->then(
            function ($response) {
                $data = json_decode($response->getBody(), true);
                return [
                    "score" => $data['score']['total'] ?? 0,
                    "threat" => $data['threat'] ?? []
                ];
            },
            function ($exception) {
                return ["score" => 0, "threat" => []];
            }
        );
    }

    // --- Helper Methods ---

    // Static counters for round-robin rotation
    protected static $keyCounters = [
        'VT' => 0, 'OTX' => 0, 'ABUSE' => 0, 'TF' => 0, 'RST' => 0
    ];

    protected function getKey($service)
    {
        $keys = constant("self::{$service}_KEYS");
        // Round-robin rotation (much better than random for avoiding rate limits)
        $index = self::$keyCounters[$service] % count($keys);
        self::$keyCounters[$service]++;
        return $keys[$index];
    }

    protected function mapScore($val, $thresholds)
    {
        foreach ($thresholds as $t) {
            if ($val >= $t[0]) return $t[1];
        }
        return 0;
    }

    protected function calculateRisk($vt, $abuse, $tf, $otx, $rst, $iocType)
    {
        // Match Python: empty dict, only add sources when API returns data
        $scores = [];

        // 1. Calculate score for each source
        if (!empty($vt) && isset($vt['malicious'])) {
            $malicious = $vt['malicious'];
            $suspicious = $vt['suspicious'] ?? 0;
            
            $vtScore = $this->mapScore($malicious, self::THRESHOLDS['vt']);
            
            if ($suspicious >= 5) $vtScore += 2;
            elseif ($suspicious >= 3) $vtScore += 1;
            
            $scores['vt'] = min($vtScore, 10);
        }

        if (!empty($abuse) && $iocType === 'ip' && isset($abuse['score'])) {
            $abuseConfidence = $abuse['score'];
            $totalReports = $abuse['total_reports'] ?? 0;
            
            $scorePart = $this->mapScore($abuseConfidence, self::THRESHOLDS['abuse_score']);
            $reportsPart = $this->mapScore($totalReports, self::THRESHOLDS['abuse_reports']);
            
            $scores['abuse'] = min($scorePart + $reportsPart, 10);
        }

        if (!empty($tf) && isset($tf['confidence_level'])) {
            $confidence = $tf['confidence_level'];
            $scores['tf'] = $this->mapScore($confidence, self::THRESHOLDS['tf']);
        }

        if (!empty($otx) && isset($otx['pulse_count'])) {
            $pulseCount = $otx['pulse_count'];
            $scores['otx'] = $this->mapScore($pulseCount, self::THRESHOLDS['otx']);
        }

        if (!empty($rst) && (isset($rst['score']) && $rst['score'] !== null)) {
            $scores['rst'] = min((float)$rst['score'] / 10.0, 10);
        }

        // 2. Calculate weighted average
        if (empty($scores)) {
            return [0, "Informational", [], []];
        }
        
        $activeScores = [];
        $activeWeights = [];
        
        foreach ($scores as $source => $score) {
            if (isset(self::WEIGHTS[$source])) {
                $activeScores[$source] = $score;
                $activeWeights[$source] = self::WEIGHTS[$source];
            }
        }

        // Weight Transfer Logic (Replicating Python Script Behavior/Bug)
        if ($iocType !== 'ip' && isset(self::WEIGHTS['abuse']) && isset($scores['vt'])) {
             if (isset($activeWeights['vt'])) {
                 $activeWeights['vt'] += self::WEIGHTS['abuse'];
             }
        }
        
        
        $totalWeight = array_sum($activeWeights);
        if ($totalWeight == 0) return [0, "Informational", [], []];
        
        $weightedSum = 0;
        foreach ($activeWeights as $source => $weight) {
             if (isset($activeScores[$source])) {
                 $weightedSum += $activeScores[$source] * $weight;
             }
        }

        $finalScoreRaw = $weightedSum / $totalWeight;
        $finalScore = ceil($finalScoreRaw); // Use ceil to be safer (1.25 -> 2)
        
        // 3. Determine risk level
        if ($finalScore >= 9) $level = "Critical";
        elseif ($finalScore >= 7) $level = "High";
        elseif ($finalScore >= 4) $level = "Medium";
        elseif ($finalScore >= 2) $level = "Low";
        elseif ($finalScore >= 1) $level = "Very Low";
        else $level = "Informational";
        
        return [$finalScore, $level, $activeScores, $activeWeights];
    }
}
