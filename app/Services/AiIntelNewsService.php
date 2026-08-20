<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Modules\RSSFeedSettings\Entities\AiIntelNewsLog;

class AiIntelNewsService
{
    protected $http;
    protected $feeds = [
        ['name' => 'Cyber Security News', 'url' => 'https://cybersecuritynews.com/feed/'],
        ['name' => 'SecurityWeek', 'url' => 'https://www.securityweek.com/rss.xml'],
        ['name' => 'BleepingComputer', 'url' => 'https://www.bleepingcomputer.com/feed/'],
        ['name' => 'The Hacker News', 'url' => 'https://feeds.feedburner.com/TheHackersNews'],
        ['name' => 'Security Online', 'url' => 'https://securityonline.info/feed/'],
        ['name' => 'Dark Reading', 'url' => 'https://www.darkreading.com/rss.xml'],
    ];

    const CISA_KEV_URL = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';

    const SYSTEM_PROMPT = <<<'PROMPT'
You are a Senior Cyber Threat Intelligence Analyst (Expert Level) and SOC specialist.
Your mission is to analyze the provided cybersecurity news article and extract highly accurate, structured security intelligence.

Priority: Accuracy > Completeness
Never fabricate, infer, hallucinate, or auto-complete security intelligence.
If any section is not explicitly supported by the article, omit it or return empty structures.

Extract CVEs only when explicitly mentioned.
IOCs must be concrete observable artifacts (IP, Domain, URL, Hash, Email, File, Registry, Command Line).
Do NOT put TTPs, malware family names, or threat actor names in indicators.

JSON STRUCTURE:
{
  "category": "Vulnerability" | "Malware" | "Attack" | "Info",
  "executive_summary": ["หัวข้อสรุปภาษาไทย 1", "หัวข้อสรุปภาษาไทย 2"],
  "intelligence_context": {
    "attacker_group": "Attacker group name (English) or null",
    "historical_narrative": ["บริบทประวัติศาสตร์ภาษาไทย 1"]
  },
  "vulnerabilities": [{"cve": "CVE-xxxx", "product": "...", "severity": "..."}],
  "indicators": [{"type": "IP|Domain|URL|Hash|Email|File Name|Registry Key|Command Line", "value": "...", "description": "อธิบายสั้นๆ ภาษาไทย", "confidence": "High|Medium"}],
  "campaign": {"name": "...", "target_sector": "...", "target_country": "...", "summary": "..."}
}

Use natural Thai for executive_summary, historical_narrative, description, and campaign.summary.
PROMPT;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 60,
            'connect_timeout' => 15,
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.8, */*;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    /**
     * Full sync: CISA KEV + RSS feeds → OpenAI extract → ai_intel_news_logs
     *
     * @param int $perFeedLimit
     * @param callable|null $logger function(string $level, string $message)
     * @return array{created:int,updated:int,skipped:int,failed:int}
     */
    public function sync($perFeedLimit = 1, $logger = null)
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $log = function ($level, $msg) use ($logger) {
            if (is_callable($logger)) {
                $logger($level, $msg);
            }
        };

        if (!config('services.openai.api_key')) {
            throw new Exception('OPENAI_API_KEY is not set in .env');
        }

        foreach ($this->collectCandidates($perFeedLimit, $log) as $candidate) {
            try {
                $result = $this->processAndSave($candidate, $log);
                $stats[$result]++;
                // Breath between OpenAI calls
                usleep(500000);
            } catch (Exception $e) {
                $stats['failed']++;
                $log('error', $e->getMessage());
                Log::error('AiIntelNewsService: ' . $e->getMessage());
                if ($this->isOpenAiQuotaError($e->getMessage())) {
                    $log('error', 'Stopping sync: OpenAI quota/credits exhausted.');
                    break;
                }
            }
        }

        return $stats;
    }

    protected function collectCandidates($perFeedLimit, $log)
    {
        $items = [];

        // Phase 1: CISA KEV
        $log('info', 'Fetching CISA KEV...');
        try {
            $response = $this->http->get(self::CISA_KEV_URL);
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                $vulns = @$data['vulnerabilities'] ?: [];
                foreach (array_slice($vulns, 0, max(1, (int) $perFeedLimit)) as $vuln) {
                    $cve = @$vuln['cveID'] ?: 'Unknown';
                    $items[] = [
                        'title' => 'ALARM: CISA Added ' . $cve . ' to KEV Catalog',
                        'source' => 'CISA KEV',
                        'source_url' => 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog',
                        'published_at' => @$vuln['dateAdded'] ?: date('Y-m-d'),
                        'content' => sprintf(
                            'Vulnerability in %s %s. Description: %s. Required Action: %s',
                            @$vuln['vendorProject'] ?: '',
                            @$vuln['product'] ?: '',
                            @$vuln['shortDescription'] ?: '',
                            @$vuln['requiredAction'] ?: ''
                        ),
                    ];
                    $log('info', 'CISA KEV queued: ' . $cve);
                }
            } else {
                $log('warn', 'CISA KEV HTTP ' . $response->getStatusCode());
            }
        } catch (Exception $e) {
            $log('error', 'CISA KEV: ' . $e->getMessage());
        }

        // Phase 2: RSS feeds
        foreach ($this->feeds as $feed) {
            $log('info', 'Scanning feed: ' . $feed['name']);
            try {
                $entries = $this->parseRssFeed($feed['url'], $perFeedLimit);
                foreach ($entries as $entry) {
                    $items[] = [
                        'title' => $entry['title'],
                        'source' => $feed['name'],
                        'source_url' => $entry['link'],
                        'published_at' => $entry['published_at'],
                        'content' => $entry['content'],
                    ];
                }
            } catch (Exception $e) {
                $log('error', $feed['name'] . ': ' . $e->getMessage());
            }
        }

        return $items;
    }

    protected function parseRssFeed($url, $limit = 1)
    {
        $response = $this->http->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new Exception('RSS HTTP ' . $response->getStatusCode());
        }

        $xml = @simplexml_load_string((string) $response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            throw new Exception('Invalid RSS XML');
        }

        $entries = [];
        $items = [];
        if (isset($xml->channel->item)) {
            $items = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            // Atom
            $items = $xml->entry;
        }

        $count = 0;
        foreach ($items as $item) {
            if ($count >= $limit) {
                break;
            }

            $title = trim((string) (@$item->title ?: 'No Title'));
            $link = trim((string) (@$item->link['href'] ?: @$item->link ?: ''));
            if ($link === '' && isset($item->link)) {
                foreach ($item->link as $l) {
                    $href = (string) @$l['href'];
                    if ($href) {
                        $link = $href;
                        break;
                    }
                }
            }

            $pub = (string) (@$item->pubDate ?: @$item->published ?: @$item->updated ?: '');
            $rawContent = (string) (
                @$item->children('content', true)->encoded
                ?: @$item->description
                ?: @$item->summary
                ?: @$item->content
                ?: ''
            );
            $content = trim(html_entity_decode(strip_tags($rawContent)));
            $content = mb_substr($content, 0, 4000);

            $entries[] = [
                'title' => $title,
                'link' => $link,
                'published_at' => $pub ?: date('Y-m-d H:i:s'),
                'content' => $content,
            ];
            $count++;
        }

        return $entries;
    }

    protected function processAndSave(array $candidate, $log)
    {
        $title = trim($candidate['title']);
        $sourceUrl = trim(@$candidate['source_url'] ?: '');

        $existingQuery = AiIntelNewsLog::query();
        if ($sourceUrl !== '') {
            $existingQuery->where('source_url', $sourceUrl);
        } else {
            $existingQuery->where('title', $title);
        }
        $existing = $existingQuery->first();

        if ($existing && in_array($existing->status, ['promoted', 'dismissed'], true)) {
            $log('info', '[SKIP promoted/dismissed] ' . $title);
            return 'skipped';
        }

        // Skip re-analysis if already have AI summary and not forced update of new-only
        if ($existing && !empty($existing->executive_summary) && $existing->status !== 'new') {
            $log('info', '[SKIP existing] ' . $title);
            return 'skipped';
        }

        // Dedup: if same title already exists as new with summary, skip OpenAI cost
        if ($existing && !empty($existing->executive_summary)) {
            $log('info', '[SKIP already analyzed] ' . $title);
            return 'skipped';
        }

        $log('info', '[ANALYSIS] ' . $title);
        $cti = $this->extractCtiData($candidate['content'], $title);
        if (!$cti) {
            $log('warn', '[FAILED ANALYSIS] ' . $title);
            return 'failed';
        }

        $cti = $this->cleanExtractedIocs($cti);

        $payload = [
            'title' => $title ?: 'Untitled',
            'source' => @$candidate['source'] ?: 'AI Intel',
            'source_url' => $sourceUrl ?: null,
            'category' => @$cti['category'] ?: 'Info',
            'executive_summary' => json_encode(@$cti['executive_summary'] ?: [], JSON_UNESCAPED_UNICODE),
            'intelligence_context' => json_encode(@$cti['intelligence_context'] ?: new \stdClass(), JSON_UNESCAPED_UNICODE),
            'indicators_json' => json_encode(@$cti['indicators'] ?: [], JSON_UNESCAPED_UNICODE),
            'vulnerabilities_json' => json_encode(@$cti['vulnerabilities'] ?: [], JSON_UNESCAPED_UNICODE),
            'published_at' => $this->parseDate(@$candidate['published_at']),
        ];

        if ($existing) {
            $existing->fill($payload);
            $existing->save();
            $log('info', '[UPDATED] ' . $title);
            return 'updated';
        }

        $logModel = new AiIntelNewsLog();
        $logModel->code = function_exists('generator_uuid') ? generator_uuid() : $this->guid();
        $logModel->status = 'new';
        $logModel->fill($payload);
        $logModel->save();
        $log('info', '[CREATED] ' . $title);

        return 'created';
    }

    protected function extractCtiData($content, $title = '')
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return null;
        }

        $searchResults = '';
        if ($title && config('services.google_cse.api_key') && config('services.google_cse.cx')) {
            $attribution = $this->searchThreatIntel("'" . $title . "' threat actor attribution hacker group history");
            $iocs = $this->searchThreatIntel("'" . $title . "' IOCs indicators domains IPs hashes");
            $parts = array_filter([$attribution, $iocs]);
            $searchResults = implode("\n---\n", $parts);
        }

        $userPrompt = "[NEWS CONTENT TO ANALYSE]:\n" . mb_substr((string) $content, 0, 5000) . "\n\n";
        if ($searchResults) {
            $userPrompt .= "[GROUNDING CONTEXT: WEB SEARCH RESULTS]:\n{$searchResults}\n\n";
            $userPrompt .= 'INSTRUCTION: ใช้ grounding context เพื่อดึง IOC และบริบทที่เกี่ยวข้องอย่างถูกต้อง';
        } else {
            $userPrompt .= 'INSTRUCTION: วิเคราะห์จากเนื้อหาต้นฉบับเพื่อหา IOC และสรุปข่าวภาษาไทย';
        }

        $model = config('services.openai.model', 'gpt-4o-mini');
        $response = $this->http->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ],
            'timeout' => 90,
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status !== 200) {
            Log::warning("OpenAI CTI extract HTTP {$status}: " . mb_substr($body, 0, 500));
            $decoded = json_decode($body, true);
            $err = @$decoded['error']['message'] ?: mb_substr($body, 0, 180);
            throw new Exception("OpenAI HTTP {$status}: {$err}");
        }

        $json = json_decode($body, true);
        $contentJson = @$json['choices'][0]['message']['content'];
        if (!$contentJson) {
            throw new Exception('OpenAI returned an empty analysis payload');
        }

        $parsed = json_decode($contentJson, true);
        if (!is_array($parsed)) {
            throw new Exception('OpenAI returned invalid JSON analysis');
        }

        return $parsed;
    }

    protected function isOpenAiQuotaError($message)
    {
        $needle = mb_strtolower((string) $message);
        return strpos($needle, 'insufficient_quota') !== false
            || strpos($needle, 'credit_balance_exhausted') !== false
            || strpos($needle, 'no credits remaining') !== false;
    }

    protected function searchThreatIntel($query)
    {
        $apiKey = config('services.google_cse.api_key');
        $cseId = config('services.google_cse.cx');
        if (!$apiKey || !$cseId || strpos($cseId, 'YOUR_CUSTOM') !== false) {
            return '';
        }

        try {
            $response = $this->http->get('https://www.googleapis.com/customsearch/v1', [
                'query' => [
                    'key' => $apiKey,
                    'cx' => $cseId,
                    'q' => $query,
                    'num' => 3,
                ],
                'timeout' => 20,
            ]);
            if ($response->getStatusCode() !== 200) {
                return '';
            }
            $data = json_decode((string) $response->getBody(), true);
            $lines = [];
            foreach (@$data['items'] ?: [] as $item) {
                $lines[] = 'Title: ' . @$item['title'] . "\nSnippet: " . @$item['snippet'] . "\nLink: " . @$item['link'];
            }
            return implode("\n---\n", $lines);
        } catch (Exception $e) {
            return '';
        }
    }

    protected function cleanExtractedIocs(array $ctiData)
    {
        if (!isset($ctiData['indicators']) || !is_array($ctiData['indicators'])) {
            $ctiData['indicators'] = [];
            return $ctiData;
        }

        $bannedTypes = ['malware', 'malware name', 'threat actor', 'hacker', 'campaign', 'tool', 'technique', 'vulnerability', 'ttp', 'other'];
        $bannedWords = ['chrome', 'windows', 'microsoft', 'google', 'python', 'cisa', 'github'];
        $placeholders = ['', 'n/a', 'none', 'unknown', 'ไม่มี', 'ไม่ทราบ', 'null', 'undefined'];

        $clean = [];
        foreach ($ctiData['indicators'] as $ind) {
            if (!is_array($ind)) {
                continue;
            }
            $val = trim((string) (@$ind['value'] ?: ''));
            $valLower = mb_strtolower($val);
            $type = mb_strtolower(trim((string) (@$ind['type'] ?: '')));

            if ($val === '' || in_array($valLower, $placeholders, true)) {
                continue;
            }
            if (in_array($type, $bannedTypes, true) || in_array($valLower, $bannedWords, true)) {
                continue;
            }
            if (preg_match('/overflow|corruption|escalation|bypass|exploit|vulnerability|rce|aslr|heap|stack|inject/i', $val)) {
                continue;
            }

            $valid = false;
            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $val)) {
                $ind['type'] = 'ip';
                $valid = true;
            } elseif (preg_match('/^[a-fA-F0-9]{32}$|^[a-fA-F0-9]{40}$|^[a-fA-F0-9]{64}$/', $val)) {
                $ind['type'] = 'hash';
                $valid = true;
            } elseif (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $ind['type'] = 'email';
                $valid = true;
            } elseif (preg_match('#^https?://#i', $val)) {
                $ind['type'] = 'url';
                $valid = true;
            } elseif (strpos($type, 'domain') !== false && strpos($val, '.') !== false && strpos($val, ' ') === false) {
                $valid = true;
            } elseif (strpos($type, 'file') !== false || strpos($type, 'path') !== false || strpos($type, 'registry') !== false || strpos($type, 'command') !== false) {
                $valid = strlen($val) > 3;
            }

            if (!$valid) {
                continue;
            }

            $conf = trim((string) (@$ind['confidence'] ?: 'Medium'));
            if (!in_array($conf, ['High', 'Medium'], true)) {
                if (in_array(mb_strtolower($conf), ['low', 'ต่ำ'], true)) {
                    continue;
                }
                $ind['confidence'] = 'Medium';
            }

            $clean[] = $ind;
        }

        $ctiData['indicators'] = $clean;
        return $ctiData;
    }

    protected function parseDate($value)
    {
        if (empty($value)) {
            return Carbon::now();
        }
        try {
            return Carbon::parse($value);
        } catch (Exception $e) {
            return Carbon::now();
        }
    }

    protected function guid()
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
