<?php

namespace App\Services;

use App\Services\SystemApiKeyService;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class PhishingDetectionService
{
    private static $freeHostingSuffixes = [
        '.vercel.app', '.netlify.app', '.github.io', '.web.app', '.firebaseapp.com',
        '.pages.dev', '.herokuapp.com', '.azurewebsites.net', '.000webhostapp.com',
        '.wixsite.com', '.weebly.com', '.wordpress.com', '.blogspot.com',
    ];

    /** @var SystemApiKeyService */
    private $apiKeyService;

    /** @var Client */
    private $httpClient;

    private $lastVtRequestAt = 0;

    private const VT_MIN_INTERVAL = 15;

    // --- Domain Discovery properties ---

    private $commonTlds = [
        '.com', '.net', '.org', '.info', '.biz', '.co', '.io', '.me',
        '.tv', '.cc', '.tk', '.ml', '.ga', '.cf', '.top', '.xyz',
    ];

    private $suspiciousKeywords = [
        'login', 'secure', 'verify', 'account', 'bank', 'payment',
        'update', 'confirm', 'validate', 'authenticate', 'signin',
        'signup', 'register', 'recover', 'reset', 'unlock', 'support',
        'help', 'service', 'portal', 'dashboard', 'admin', 'office',
        'mail', 'email', 'webmail', 'client', 'customer', 'user',
        'security', 'official', 'urgent', 'suspended', 'blocked',
    ];

    private $homoglyphMap = [
        'a' => ['@', '4'],
        'e' => ['3'],
        'i' => ['1', 'l', '|'],
        'o' => ['0'],
        's' => ['5', '$'],
        't' => ['7'],
    ];

    private $phishingPatterns = [
        'support-', 'help-', 'service-', 'secure-', 'official-',
        '-support', '-help', '-secure', '-login', '-verify',
    ];

    public function __construct(SystemApiKeyService $apiKeyService = null, Client $httpClient = null)
    {
        $this->apiKeyService = $apiKeyService ?: new SystemApiKeyService();
        $this->httpClient = $httpClient ?: new Client(['timeout' => 15, 'http_errors' => false, 'verify' => false]);
    }

    // --- Public API ---

    public function discoverCandidates($officialDomain, $offlineMode = false)
    {
        $discovery = $this->discoverDomains($officialDomain, $offlineMode);
        $candidates = $this->buildCandidatesFromDiscovery($discovery);

        $urlscan = $this->searchUrlscanDomain($officialDomain);
        $seen = array_column($candidates, 'domain');
        foreach ($urlscan['details']['sample_urls'] ?? [] as $sampleUrl) {
            $host = parse_url($sampleUrl, PHP_URL_HOST);
            if (!$host) {
                continue;
            }
            $host = strtolower(preg_replace('/^www\./', '', $host));
            if ($host === strtolower($officialDomain) || in_array($host, $seen, true)) {
                continue;
            }
            $ip = gethostbyname($host);
            if ($ip && $ip !== $host) {
                $candidates[] = [
                    'domain' => $host,
                    'source' => 'urlscan',
                    'dns_resolves' => true,
                    'ip_addresses' => [$ip],
                ];
                $seen[] = $host;
            }
        }
        $discovery['urlscan'] = $urlscan;

        return [
            'discovery' => $discovery,
            'candidates' => $candidates,
        ];
    }

    public function fetchOfficialText($officialUrl)
    {
        try {
            $response = $this->httpClient->get($officialUrl, ['timeout' => 15]);
            return (string) $response->getBody();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function analyzeCandidate($candidateDomain, $officialDomain, $officialUrl, $officialText = '')
    {
        $candidateDomain = strtolower(preg_replace('/^www\./', '', $candidateDomain));
        $candidateUrl = 'https://' . $candidateDomain . '/';
        $brandLabel = $this->brandLabel($officialDomain);

        $candidateText = '';
        try {
            $response = $this->httpClient->get($candidateUrl, [
                'timeout' => 12,
                'allow_redirects' => ['max' => 3],
            ]);
            $candidateText = (string) $response->getBody();
        } catch (\Exception $e) {
            $candidateText = '';
        }

        $urlscanSubmit = $this->submitToUrlscan($candidateUrl);

        $providers = $this->runAllProviderChecks($candidateUrl, $candidateDomain);
        $reputationHit = $this->reputationHit($providers);
        $reputationSources = $this->reputationSources($providers);

        $heuristics = $this->collectHeuristics($candidateText, $candidateDomain, $officialUrl);
        $brandHeuristics = $this->extraBrandHeuristics($candidateDomain, $candidateText, $brandLabel, $officialText);
        $advanced = $this->comprehensiveAnalysis($candidateUrl, $candidateDomain, $candidateText);
        $ml = $this->enhancedPhishingDetection($candidateUrl, $candidateDomain, $candidateText);
        $structureSimilarity = $this->calculateVisualSimilarity($officialUrl, $candidateUrl);
        $contentSimilarity = $this->contentJaccardSimilarity($officialText, $candidateText);
        $visualSimilarity = max($structureSimilarity, $contentSimilarity);
        $domainAgeDays = $this->getWhoisAgeDays($candidateDomain);

        $scoreResult = $this->computeScoreV3(
            $reputationHit,
            $heuristics['keyword_indicators'],
            $domainAgeDays,
            $heuristics['free_hosting'],
            $heuristics['hotlink_assets'],
            $visualSimilarity,
            !empty($advanced['ssl_analysis']['issues']),
            (int) ($advanced['redirect_analysis']['redirect_count'] ?? 0),
            !empty($advanced['javascript_analysis']['obfuscated']),
            ($advanced['form_analysis']['suspicious_forms'] ?? 0) > 0,
            $advanced['url_entropy'] ?? null,
            !empty($advanced['dns_analysis']['suspicious'])
        );

        $brandBonus = 0;
        foreach ($brandHeuristics as $item) {
            $brandBonus += (int) ($item['weight'] ?? 0);
        }
        $scoreResult['score'] = max(0, min(100, $scoreResult['score'] + $brandBonus));
        $scoreResult['label'] = $this->scoreToLabel($scoreResult['score']);

        $ip = gethostbyname($candidateDomain);
        if ($ip === $candidateDomain) {
            $ip = 'Unknown';
        }

        $heuristicHits = $heuristics['hits'];
        foreach ($brandHeuristics as $item) {
            if (!empty($item['hit'])) {
                $heuristicHits[] = $item['name'] . ($item['reason'] ? ': ' . $item['reason'] : '');
            }
        }

        return [
            'domain' => $candidateDomain,
            'url' => $candidateUrl,
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'ip' => $ip,
            'signals' => [
                'reputation' => $reputationSources,
                'heuristics' => $heuristicHits,
                'domain_age_days' => $domainAgeDays,
                'brand_heuristics' => $brandHeuristics,
            ],
            'providers' => $providers,
            'evidence' => [
                'title' => $this->extractTitle($candidateText),
                'resolved_ip' => $ip,
                'urlscan_submit' => $urlscanSubmit,
                'advanced_analysis' => [
                    'url_entropy' => $advanced['url_entropy'] ?? 0,
                    'ssl_issues' => !empty($advanced['ssl_analysis']['issues']),
                    'redirect_count' => $advanced['redirect_analysis']['redirect_count'] ?? 0,
                    'javascript_obfuscated' => !empty($advanced['javascript_analysis']['obfuscated']),
                    'suspicious_forms' => ($advanced['form_analysis']['suspicious_forms'] ?? 0) > 0,
                    'dns_suspicious' => !empty($advanced['dns_analysis']['suspicious']),
                    'visual_similarity' => $visualSimilarity,
                    'structure_similarity' => $structureSimilarity,
                    'content_similarity' => $contentSimilarity,
                    'overall_suspicious_score' => $advanced['suspicious_score'] ?? 0,
                ],
                'ml_analysis' => [
                    'ml_score' => $ml['ml_score'] ?? 0,
                    'ml_label' => $ml['ml_label'] ?? 'Unknown',
                    'feature_count' => $ml['feature_count'] ?? 0,
                    'high_risk_features' => $ml['high_risk_features'] ?? [],
                ],
            ],
        ];
    }

    public function isOfficialOrTrusted($candidateDomain, $officialDomain)
    {
        $candidate = strtolower(preg_replace('/^www\./', '', $candidateDomain));
        $official = strtolower(preg_replace('/^www\./', '', $officialDomain));

        if ($candidate === $official || Str::endsWith($candidate, '.' . $official)) {
            return true;
        }

        $trusted = [
            'youtube.com', 'facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com',
            'wikipedia.org', 'github.com', 'google.com', 'reddit.com',
        ];

        return in_array($candidate, $trusted, true);
    }

    public function labelToSeverity($label)
    {
        switch ($label) {
            case 'Phishing':
                return ['severity' => 'Critical', 'db_score' => 10];
            case 'Suspicious':
                return ['severity' => 'High', 'db_score' => 7];
            default:
                return ['severity' => 'Low', 'db_score' => 3];
        }
    }

    // --- Heuristics ---

    private function brandLabel($officialDomain)
    {
        $parts = $this->parseDomain(strtolower(preg_replace('/^www\./', '', $officialDomain)));
        return $parts['domain'] ?? explode('.', $officialDomain)[0];
    }

    private function extraBrandHeuristics($candidateDomain, $candidateText, $brandLabel, $officialText)
    {
        $extras = [];
        $dom = strtolower($candidateDomain);
        $hasBrand = $brandLabel !== '' && strpos($dom, strtolower($brandLabel)) !== false;

        $suspiciousTokens = [];
        $officialLower = strtolower($officialText);
        $phishingPatterns = [
            'login', 'signin', 'sign-in', 'secure', 'security', 'verify', 'verification',
            'account', 'profile', 'dashboard', 'portal', 'support', 'help', 'update',
            'reset', 'password', 'wallet', 'payment', 'pay', 'auth', 'confirm',
            'suspended', 'blocked', 'locked', 'urgent', 'action required',
        ];
        foreach ($phishingPatterns as $pattern) {
            if (strpos($officialLower, $pattern) !== false) {
                $suspiciousTokens[$pattern] = true;
            }
        }

        if (preg_match_all('/<input[^>]*name=["\']([^"\']*)["\'][^>]*>/i', $officialLower, $formMatches)) {
            foreach ($formMatches[1] as $name) {
                if (preg_match('/login|sign|verify|submit|continue/i', $name)) {
                    $suspiciousTokens[trim($name)] = true;
                }
            }
        }

        if (empty($suspiciousTokens)) {
            $suspiciousTokens = array_flip([
                'login', 'secure', 'verify', 'account', 'support', 'update',
                'reset', 'wallet', 'pay', 'signin', 'auth',
            ]);
        }

        $foundTokens = [];
        foreach (array_keys($suspiciousTokens) as $token) {
            if (strpos($dom, $token) !== false) {
                $foundTokens[] = $token;
            }
        }

        $hitBrandKw = $hasBrand && !empty($foundTokens);
        $extras[] = [
            'name' => 'Brand + dynamic suspicious keywords in domain',
            'category' => 'heuristic',
            'hit' => $hitBrandKw,
            'weight' => $hitBrandKw ? 15 : 0,
            'reason' => $hitBrandKw ? $dom . ' (found: ' . implode(', ', $foundTokens) . ')' : null,
        ];

        $sim = $this->contentJaccardSimilarity($officialText, $candidateText);
        $weight = 0;
        if ($sim >= 0.6) {
            $weight = 25;
        } elseif ($sim >= 0.35) {
            $weight = 12;
        }
        $extras[] = [
            'name' => 'Brand content similarity',
            'category' => 'heuristic',
            'hit' => $weight > 0,
            'weight' => $weight,
            'reason' => 'jaccard=' . number_format($sim, 2),
        ];

        return $extras;
    }

    private function contentJaccardSimilarity($officialText, $candidateText)
    {
        $offTokens = array_flip($this->tokenizeWords($this->stripHtml($officialText)));
        $candTokens = array_flip($this->tokenizeWords($this->stripHtml($candidateText)));
        if (empty($offTokens) || empty($candTokens)) {
            return 0.0;
        }

        $intersection = count(array_intersect_key($offTokens, $candTokens));
        $union = count($offTokens + $candTokens);

        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function stripHtml($html)
    {
        if (!$html) {
            return '';
        }
        $text = preg_replace('/<script.*?>.*?<\/script>/is', ' ', $html);
        $text = preg_replace('/<style.*?>.*?<\/style>/is', ' ', $text);
        $text = preg_replace('/<[^>]+>/', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function tokenizeWords($text)
    {
        if (!$text) {
            return [];
        }
        preg_match_all('/[a-zA-Z0-9]+/', strtolower($text), $matches);
        $words = [];
        foreach ($matches[0] ?? [] as $word) {
            if (strlen($word) >= 3) {
                $words[] = $word;
            }
        }

        return $words;
    }

    private function collectHeuristics($candidateText, $candidateDomain, $officialUrl)
    {
        $hits = [];
        $textLc = strtolower($candidateText);
        $hasForm = strpos($textLc, '<form') !== false;

        $keywordMatches = [];
        if (preg_match_all(
            '/\b(login|signin|verify|password|account|secure|payment|update|reset|confirm|wallet|urgent)\b/i',
            $textLc,
            $matches
        )) {
            $keywordMatches = array_unique(array_map('strtolower', $matches[0]));
        }

        if ($hasForm) {
            $hits[] = 'form detected';
        }
        foreach ($keywordMatches as $keyword) {
            $hits[] = 'keyword:' . $keyword;
        }

        $officialHost = parse_url($officialUrl, PHP_URL_HOST) ?: '';
        $hotlink = $this->hotlinksOfficialAssets($candidateText, $officialHost);
        if ($hotlink) {
            $hits[] = 'hotlink brand assets';
        }

        $freeHosting = $this->isFreeHosting($candidateDomain);
        if ($freeHosting) {
            $hits[] = 'free hosting';
        }

        return [
            'hits' => $hits,
            'keyword_indicators' => $hasForm && !empty($keywordMatches),
            'free_hosting' => $freeHosting,
            'hotlink_assets' => $hotlink,
        ];
    }

    private function hotlinksOfficialAssets($html, $officialHost)
    {
        if (!$html || !$officialHost) {
            return false;
        }

        $h = strtolower($html);
        $host = strtolower($officialHost);

        return strpos($h, '//' . $host . '/') !== false || strpos($h, '://' . $host . '/') !== false;
    }

    private function isFreeHosting($domain)
    {
        $domain = strtolower($domain);
        foreach (self::$freeHostingSuffixes as $suffix) {
            if (substr($domain, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return false;
    }

    private function extractTitle($html)
    {
        if (!$html) {
            return '';
        }
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return trim(preg_replace('/\s+/', ' ', strip_tags($matches[1])));
        }

        return '';
    }

    // --- Domain Discovery ---

    private function discoverDomains($targetDomain, $offlineMode = false)
    {
        $targetDomain = strtolower(preg_replace('/^www\./', '', $targetDomain));
        $parts = $this->parseDomain($targetDomain);
        $domainName = $parts['domain'];
        $tld = $parts['tld'];

        $typoVariations = $this->generateTypoVariations($domainName, $tld);
        $aiVariations = $this->generateAiDomains($domainName, $tld);
        $advancedPatterns = $this->generateAdvancedPatterns($domainName, $tld);
        $mlPatterns = $this->generateMlEnhancedDomains($domainName, $tld);

        $certificateDomains = [];
        $urlscanDomains = [];
        if (!$offlineMode) {
            $certificateDomains = $this->searchCertificateTransparency($targetDomain);
            $urlscanDomains = $this->searchUrlscanDiscovery($targetDomain);
        }

        $externalDomains = [];
        foreach ($certificateDomains as $entry) {
            $externalDomains[] = $entry['domain'];
        }
        foreach ($urlscanDomains as $entry) {
            $externalDomains[] = $entry['domain'];
        }

        $allGenerated = array_unique(array_merge(
            $typoVariations,
            $aiVariations,
            $advancedPatterns,
            $mlPatterns,
            $externalDomains
        ));
        $resolvable = $this->preFilterDomains($targetDomain, $allGenerated);

        $dnsAnalysis = [];
        foreach ($resolvable as $domain) {
            $dns = $this->checkDnsResolution($domain);
            if (!$dns['resolves']) {
                continue;
            }

            $similarity = $this->analyzeDomainSimilarity($targetDomain, $domain);
            $baseScore = $this->calculateSuspiciousScore($dns, $similarity);
            $historical = $this->analyzeDomainHistory($domain);
            $geolocation = $this->analyzeGeolocation($domain);
            $mlInfo = $this->mlPhishingDetection($domain);

            $historicalScore = (int) ($historical['historical_risk_score'] ?? 0);
            $geolocationScore = (int) ($geolocation['geolocation_risk_score'] ?? 0);
            $mlScore = (int) ($mlInfo['ml_score'] ?? 0);
            $enhancedScore = (int) (($baseScore * 0.3) + ($historicalScore * 0.2) + ($geolocationScore * 0.2) + ($mlScore * 0.3));

            $dnsAnalysis[] = [
                'domain' => $domain,
                'dns_resolution' => $dns,
                'similarity_analysis' => $similarity,
                'historical_analysis' => $historical,
                'geolocation_analysis' => $geolocation,
                'ml_analysis' => $mlInfo,
                'risk_level' => $similarity['risk_level'],
                'suspicious_score' => $enhancedScore,
                'base_score' => $baseScore,
                'historical_score' => $historicalScore,
                'geolocation_score' => $geolocationScore,
                'ml_score' => $mlScore,
            ];
        }

        usort($dnsAnalysis, function ($a, $b) {
            return ($b['suspicious_score'] ?? 0) <=> ($a['suspicious_score'] ?? 0);
        });

        return [
            'target_domain' => $targetDomain,
            'typo_variations' => $typoVariations,
            'ai_variations' => $aiVariations,
            'advanced_patterns' => $advancedPatterns,
            'ml_patterns' => $mlPatterns,
            'certificate_domains' => $certificateDomains,
            'urlscan_domains' => $urlscanDomains,
            'dns_analysis' => $dnsAnalysis,
            'summary' => [
                'total_discovered' => count($dnsAnalysis),
                'resolves' => count($dnsAnalysis),
            ],
        ];
    }

    private function buildCandidatesFromDiscovery(array $discoveryResults)
    {
        $candidates = [];
        foreach ($discoveryResults['dns_analysis'] ?? [] as $domainInfo) {
            $domain = $domainInfo['domain'] ?? '';
            if (!$domain || empty($domainInfo['dns_resolution']['resolves'])) {
                continue;
            }

            $source = 'unknown';
            if (in_array($domain, $discoveryResults['typo_variations'] ?? [], true)) {
                $source = 'typo_squatting';
            } elseif (in_array($domain, $discoveryResults['ai_variations'] ?? [], true)) {
                $source = 'ai_powered';
            } elseif (in_array($domain, $discoveryResults['advanced_patterns'] ?? [], true)) {
                $source = 'advanced_patterns';
            } elseif (in_array($domain, $discoveryResults['ml_patterns'] ?? [], true)) {
                $source = 'ml_enhanced';
            } elseif ($this->domainInCertificateList($domain, $discoveryResults['certificate_domains'] ?? [])) {
                $source = 'certificate_transparency';
            } elseif ($this->domainInUrlscanList($domain, $discoveryResults['urlscan_domains'] ?? [])) {
                $source = 'urlscan';
            }

            $candidates[] = [
                'domain' => $domain,
                'source' => $source,
                'suspicious_score' => $domainInfo['suspicious_score'] ?? 0,
                'risk_level' => $domainInfo['risk_level'] ?? 'MINIMAL',
                'dns_resolves' => true,
                'ip_addresses' => $domainInfo['dns_resolution']['ip_addresses'] ?? [],
            ];
        }

        return $candidates;
    }

    private function domainInCertificateList($domain, array $certificateDomains)
    {
        foreach ($certificateDomains as $entry) {
            if (($entry['domain'] ?? '') === $domain) {
                return true;
            }
        }

        return false;
    }

    private function domainInUrlscanList($domain, array $urlscanDomains)
    {
        foreach ($urlscanDomains as $entry) {
            if (($entry['domain'] ?? '') === $domain) {
                return true;
            }
        }

        return false;
    }

    private function parseDomain($domain)
    {
        $domain = strtolower(preg_replace('/^www\./', '', $domain));
        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            return ['domain' => $domain, 'tld' => 'com'];
        }

        $twoPartTlds = ['co.th', 'co.uk', 'com.au', 'co.jp', 'com.br', 'co.id', 'com.sg'];
        $lastTwo = implode('.', array_slice($parts, -2));
        if (in_array($lastTwo, $twoPartTlds, true) && count($parts) >= 3) {
            return [
                'domain' => $parts[count($parts) - 3],
                'tld' => $lastTwo,
            ];
        }

        return [
            'domain' => $parts[count($parts) - 2],
            'tld' => end($parts),
        ];
    }

    private function generateTypoVariations($domainName, $tld, $maxVariations = 40)
    {
        $variations = [];

        foreach ($this->homoglyphMap as $char => $replacements) {
            if (strpos($domainName, $char) === false) {
                continue;
            }
            foreach ($replacements as $replacement) {
                $variations[] = str_replace($char, $replacement, $domainName) . '.' . $tld;
            }
        }

        for ($i = 1; $i < strlen($domainName); $i++) {
            $variations[] = substr($domainName, 0, $i) . substr($domainName, $i + 1) . '.' . $tld;
        }

        for ($i = 0; $i < strlen($domainName) - 1; $i++) {
            $chars = str_split($domainName);
            $tmp = $chars[$i];
            $chars[$i] = $chars[$i + 1];
            $chars[$i + 1] = $tmp;
            $variations[] = implode('', $chars) . '.' . $tld;
        }

        foreach ($this->commonTlds as $newTld) {
            $suffix = ltrim($newTld, '.');
            if ($suffix !== $tld) {
                $variations[] = $domainName . '.' . $suffix;
            }
        }

        foreach (array_slice($this->suspiciousKeywords, 0, 8) as $keyword) {
            $variations[] = $keyword . $domainName . '.' . $tld;
            $variations[] = $domainName . $keyword . '.' . $tld;
            $variations[] = $keyword . '-' . $domainName . '.' . $tld;
            $variations[] = $domainName . '-' . $keyword . '.' . $tld;
        }

        $variations = array_values(array_unique($variations));

        return array_slice($variations, 0, $maxVariations);
    }

    private function generateAdvancedPatterns($domainName, $tld, $max = 30)
    {
        $variations = [];

        foreach ($this->phishingPatterns as $pattern) {
            if (Str::startsWith($pattern, '-')) {
                $variations[] = $domainName . $pattern . $tld;
            } else {
                $variations[] = $pattern . $domainName . '.' . $tld;
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $variations[] = $domainName . $i . '.' . $tld;
            $variations[] = $i . $domainName . '.' . $tld;
        }

        $year = (int) date('Y');
        foreach ([$year, $year - 1, $year + 1] as $y) {
            $variations[] = $domainName . $y . '.' . $tld;
        }

        $affixes = ['secure', 'login', 'verify', 'app', 'mobile', 'support', 'official'];
        foreach ($affixes as $affix) {
            $variations[] = $affix . '-' . $domainName . '.' . $tld;
            $variations[] = $domainName . '-' . $affix . '.' . $tld;
        }

        return array_slice(array_values(array_unique($variations)), 0, $max);
    }

    private function generateAiDomains($domainName, $tld, $maxDomains = 15)
    {
        $variations = [];

        $phoneticMap = [
            'ph' => 'f', 'f' => 'ph',
            'c' => 'k', 'k' => 'c',
            'qu' => 'kw', 'kw' => 'qu',
            'x' => 'ks', 'ks' => 'x',
        ];
        foreach ($phoneticMap as $old => $new) {
            if (strpos($domainName, $old) !== false) {
                $variations[] = str_replace($old, $new, $domainName) . '.' . $tld;
            }
        }

        $misspellings = [
            'google' => ['gogle', 'googel', 'goggle'],
            'facebook' => ['facebok', 'faceboook'],
            'amazon' => ['amazom', 'amazone'],
            'apple' => ['aple', 'appel'],
            'twitter' => ['twiter', 'twittter'],
            'instagram' => ['instgram', 'instagrm'],
            'youtube' => ['youtub', 'youtbe'],
        ];
        foreach ($misspellings as $correct => $list) {
            if (strpos($domainName, $correct) !== false) {
                foreach ($list as $misspelled) {
                    $variations[] = str_replace($correct, $misspelled, $domainName) . '.' . $tld;
                }
            }
        }

        $keyboardLayout = [
            'q' => ['w', 'a'], 'w' => ['q', 'e', 'a', 's'], 'e' => ['w', 'r', 's', 'd'],
            'r' => ['e', 't', 'd', 'f'], 't' => ['r', 'y', 'f', 'g'], 'y' => ['t', 'u', 'g', 'h'],
            'a' => ['q', 'w', 's', 'z'], 's' => ['a', 'w', 'e', 'd', 'x', 'z'],
            'd' => ['s', 'e', 'r', 'f', 'c', 'x'], 'o' => ['i', 'p', 'k', 'l'],
            'i' => ['u', 'o', 'j', 'k'], 'l' => ['k', 'o', 'p'],
        ];
        for ($i = 0; $i < strlen($domainName); $i++) {
            $char = $domainName[$i];
            if (!isset($keyboardLayout[$char])) {
                continue;
            }
            foreach ($keyboardLayout[$char] as $nearby) {
                $variations[] = substr($domainName, 0, $i) . $nearby . substr($domainName, $i + 1) . '.' . $tld;
            }
        }

        return array_slice(array_values(array_unique($variations)), 0, $maxDomains);
    }

    private function generateMlEnhancedDomains($domainName, $tld, $max = 20)
    {
        $variations = [];

        $suspiciousTlds = ['tk', 'ml', 'ga', 'cf', 'click', 'download'];
        foreach ($suspiciousTlds as $newTld) {
            if ($newTld !== $tld) {
                $variations[] = $domainName . '.' . $newTld;
            }
        }

        for ($i = 1; $i < 4; $i++) {
            $variations[] = $domainName . '-' . $i . '.' . $tld;
            $variations[] = $i . '-' . $domainName . '.' . $tld;
            $variations[] = $domainName . '-' . $i . '-' . $domainName . '.' . $tld;
        }

        for ($i = 0; $i < 10; $i++) {
            $variations[] = $domainName . $i . $i . '.' . $tld;
            $variations[] = $i . $i . $domainName . '.' . $tld;
        }

        return array_slice(array_values(array_unique($variations)), 0, $max);
    }

    private function mlPhishingDetection($domain)
    {
        $mlScore = 0;

        if (strlen($domain) > 20) {
            $mlScore += 10;
        } elseif (strlen($domain) < 5) {
            $mlScore += 15;
        }

        $hyphenCount = substr_count($domain, '-');
        if ($hyphenCount > 2) {
            $mlScore += 20;
        } elseif ($hyphenCount > 0) {
            $mlScore += 5;
        }

        $digitCount = preg_match_all('/\d/', $domain);
        if ($digitCount > 3) {
            $mlScore += 15;
        } elseif ($digitCount > 0) {
            $mlScore += 5;
        }

        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.click', '.download'];
        foreach ($suspiciousTlds as $suffix) {
            if (substr($domain, -strlen($suffix)) === $suffix) {
                $mlScore += 25;
                break;
            }
        }

        if (preg_match('/[01lIO]/', $domain)) {
            $mlScore += 20;
        }

        if (substr_count($domain, '.') > 2) {
            $mlScore += 10;
        }

        $keywords = ['secure', 'verify', 'update', 'confirm', 'login'];
        foreach ($keywords as $keyword) {
            if (strpos(strtolower($domain), $keyword) !== false) {
                $mlScore += 15;
                break;
            }
        }

        if ($this->calculateUrlEntropy($domain) > 4.0) {
            $mlScore += 10;
        }

        $mlScore = min($mlScore, 100);
        $confidence = $mlScore > 80 ? 'HIGH' : ($mlScore > 60 ? 'MEDIUM' : 'LOW');

        return [
            'domain' => $domain,
            'ml_score' => $mlScore,
            'confidence' => $confidence,
            'prediction' => $mlScore > 70 ? 'PHISHING' : 'LEGITIMATE',
        ];
    }

    private function analyzeDomainHistory($domain)
    {
        $ageDays = $this->getWhoisAgeDays($domain);
        $patterns = [
            'recent_registration' => $ageDays !== null && $ageDays < 30,
            'privacy_protected' => false,
            'bulk_registration' => false,
            'suspicious_registrar' => false,
            'multiple_domains_same_day' => false,
        ];

        $riskFactors = 0;
        if ($patterns['recent_registration']) {
            $riskFactors += 2;
        }

        return [
            'domain' => $domain,
            'age_days' => $ageDays,
            'registration_patterns' => $patterns,
            'historical_risk_score' => min($riskFactors * 10, 100),
        ];
    }

    private function analyzeGeolocation($domain)
    {
        $dns = $this->checkDnsResolution($domain);
        if (!$dns['resolves']) {
            return ['domain' => $domain, 'error' => 'Domain does not resolve', 'geolocation_risk_score' => 0];
        }

        $riskFactors = 0;
        $geolocationData = [];
        foreach (array_slice($dns['ip_addresses'], 0, 3) as $ip) {
            $isPrivate = preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $ip);
            $isFreeHost = $this->isFreeHosting($domain);
            if ($isPrivate) {
                $riskFactors += 2;
            }
            if ($isFreeHost) {
                $riskFactors += 2;
            }
            $geolocationData[] = [
                'ip' => $ip,
                'is_private' => (bool) $isPrivate,
                'free_hosting' => $isFreeHost,
            ];
        }

        return [
            'domain' => $domain,
            'geolocation_data' => $geolocationData,
            'geolocation_risk_score' => min($riskFactors * 10, 100),
        ];
    }

    private function searchUrlscanDiscovery($domain)
    {
        $search = $this->searchUrlscanDomain($domain);
        $results = [];
        foreach ($search['details']['sample_urls'] ?? [] as $url) {
            $host = parse_url($url, PHP_URL_HOST);
            if (!$host) {
                continue;
            }
            $host = strtolower(preg_replace('/^www\./', '', $host));
            $results[$host] = ['domain' => $host, 'source' => 'urlscan', 'url' => $url];
        }

        return array_values($results);
    }

    private function searchCertificateTransparency($domain)
    {
        try {
            $response = $this->httpClient->get('https://crt.sh/?q=' . urlencode($domain) . '&output=json');
            $data = json_decode((string) $response->getBody(), true);
            if (!is_array($data)) {
                return [];
            }

            $domains = [];
            foreach ($data as $cert) {
                $nameValue = $cert['name_value'] ?? '';
                foreach (preg_split('/\s+/', $nameValue) as $certDomain) {
                    $certDomain = strtolower(trim(str_replace('*.', '', $certDomain)));
                    if ($certDomain && $certDomain !== $domain && strpos($certDomain, $domain) !== false) {
                        $domains[$certDomain] = [
                            'domain' => $certDomain,
                            'source' => 'certificate_transparency',
                        ];
                    }
                }
            }

            return array_slice(array_values($domains), 0, 20);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function checkDnsResolution($domain)
    {
        $result = [
            'domain' => $domain,
            'resolves' => false,
            'ip_addresses' => [],
            'error' => null,
        ];

        $records = @dns_get_record($domain, DNS_A);
        if ($records) {
            foreach ($records as $record) {
                if (!empty($record['ip'])) {
                    $result['ip_addresses'][] = $record['ip'];
                }
            }
        }

        if (empty($result['ip_addresses'])) {
            $ip = gethostbyname($domain);
            if ($ip && $ip !== $domain) {
                $result['ip_addresses'][] = $ip;
            }
        }

        $result['resolves'] = !empty($result['ip_addresses']);

        return $result;
    }

    private function preFilterDomains($targetDomain, array $domains, $maxCheck = 80)
    {
        $resolvable = [];
        $checked = 0;

        foreach ($domains as $domain) {
            if ($checked >= $maxCheck) {
                break;
            }
            $domain = strtolower(preg_replace('/^www\./', '', $domain));
            if ($domain === $targetDomain || $domain === '') {
                continue;
            }

            $checked++;
            $dns = $this->checkDnsResolution($domain);
            if (!$dns['resolves']) {
                continue;
            }

            $similarity = $this->analyzeDomainSimilarity($targetDomain, $domain);
            if (($similarity['similarity_score'] ?? 0) > 0.3
                || !empty($similarity['has_homoglyphs'])
                || !empty($similarity['has_suspicious_keywords'])
                || ($similarity['edit_distance'] ?? 999) <= 5) {
                $resolvable[] = $domain;
            }
        }

        return array_values(array_unique($resolvable));
    }

    private function analyzeDomainSimilarity($originalDomain, $candidateDomain)
    {
        $originalClean = strtolower(str_replace('.', '', $originalDomain));
        $candidateClean = strtolower(str_replace('.', '', $candidateDomain));
        $distance = $this->levenshteinDistance($originalClean, $candidateClean);
        $maxLen = max(strlen($originalClean), strlen($candidateClean));
        $similarityScore = $maxLen > 0 ? 1 - ($distance / $maxLen) : 0;

        $hasHomoglyphs = false;
        foreach ($this->homoglyphMap as $char => $replacements) {
            if (strpos($originalClean, $char) !== false) {
                foreach ($replacements as $rep) {
                    if (strpos($candidateClean, $rep) !== false) {
                        $hasHomoglyphs = true;
                        break 2;
                    }
                }
            }
        }

        $hasSuspiciousKeywords = false;
        foreach ($this->suspiciousKeywords as $keyword) {
            if (strpos($candidateClean, $keyword) !== false) {
                $hasSuspiciousKeywords = true;
                break;
            }
        }

        return [
            'similarity_score' => $similarityScore,
            'edit_distance' => $distance,
            'has_homoglyphs' => $hasHomoglyphs,
            'has_suspicious_keywords' => $hasSuspiciousKeywords,
            'risk_level' => $this->calculateRiskLevel($similarityScore, $hasHomoglyphs, $hasSuspiciousKeywords),
        ];
    }

    private function calculateRiskLevel($similarity, $hasHomoglyphs, $hasSuspiciousKeywords)
    {
        if ($similarity > 0.9 && ($hasHomoglyphs || $hasSuspiciousKeywords)) {
            return 'HIGH';
        }
        if ($similarity > 0.8) {
            return 'MEDIUM';
        }
        if ($similarity > 0.6) {
            return 'LOW';
        }

        return 'MINIMAL';
    }

    private function calculateSuspiciousScore(array $dnsInfo, array $similarityInfo)
    {
        $score = 0;
        $score += (int) (($similarityInfo['similarity_score'] ?? 0) * 40);
        if (!empty($similarityInfo['has_homoglyphs'])) {
            $score += 20;
        }
        if (!empty($similarityInfo['has_suspicious_keywords'])) {
            $score += 15;
        }
        if (!empty($dnsInfo['resolves'])) {
            $score += 10;
        }

        $editDistance = $similarityInfo['edit_distance'] ?? 999;
        if ($editDistance <= 2) {
            $score += 15;
        } elseif ($editDistance <= 4) {
            $score += 10;
        } elseif ($editDistance <= 6) {
            $score += 5;
        }

        return min($score, 100);
    }

    private function levenshteinDistance($s1, $s2)
    {
        $len1 = strlen($s1);
        $len2 = strlen($s2);
        if ($len1 === 0) {
            return $len2;
        }
        if ($len2 === 0) {
            return $len1;
        }

        $matrix = [];
        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i][0] = $i;
        }
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = $s1[$i - 1] === $s2[$j - 1] ? 0 : 1;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,
                    $matrix[$i][$j - 1] + 1,
                    $matrix[$i - 1][$j - 1] + $cost
                );
            }
        }

        return $matrix[$len1][$len2];
    }

    // --- Provider Checks ---

    private function runAllProviderChecks($url, $domain)
    {
        $results = [];

        $results[] = $this->checkVirusTotal($url);
        $results[] = $this->checkOtx($domain);
        $results[] = $this->checkOpenPhish($url, $domain);
        $results[] = $this->checkSafeBrowsing($url);
        $results[] = $this->checkUrlhaus($domain);
        $results[] = $this->checkPhishStats($url);
        $results[] = $this->checkScumware($url);

        return $results;
    }

    private function reputationHit(array $providerResults)
    {
        foreach ($providerResults as $result) {
            if (!empty($result['hit'])) {
                return true;
            }
        }

        return false;
    }

    private function reputationSources(array $providerResults)
    {
        $sources = [];
        foreach ($providerResults as $result) {
            if (!empty($result['hit'])) {
                $sources[] = $result['name'];
            }
        }

        return $sources;
    }

    private function checkVirusTotal($url)
    {
        $result = [
            'name' => 'VirusTotal',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        $apiKey = $this->apiKeyService->nextKey('virustotal');
        if (!$apiKey) {
            $result['details'] = ['error' => 'Missing VT API key'];
            return $result;
        }

        $headers = ['x-apikey' => $apiKey, 'accept' => 'application/json'];

        try {
            $this->throttleVt();
            $submitResponse = $this->httpClient->post('https://www.virustotal.com/api/v3/urls', [
                'headers' => $headers,
                'form_params' => ['url' => $url],
            ]);
            $submitData = json_decode((string) $submitResponse->getBody(), true) ?: [];
            $analysisId = $submitData['data']['id'] ?? null;

            if ($analysisId) {
                $status = 'queued';
                $retries = 3;
                while ($retries > 0 && in_array($status, ['queued', 'in-progress'], true)) {
                    sleep(3);
                    $this->throttleVt();
                    $analysisResponse = $this->httpClient->get(
                        'https://www.virustotal.com/api/v3/analyses/' . $analysisId,
                        ['headers' => $headers]
                    );
                    $analysisData = json_decode((string) $analysisResponse->getBody(), true) ?: [];
                    $status = $analysisData['data']['attributes']['status'] ?? '';
                    $retries--;
                }
            }

            $urlId = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
            $this->throttleVt();
            $response = $this->httpClient->get("https://www.virustotal.com/api/v3/urls/{$urlId}", ['headers' => $headers]);

            if ($response->getStatusCode() !== 200) {
                $result['details'] = ['error' => 'HTTP ' . $response->getStatusCode()];
                return $result;
            }

            $data = json_decode((string) $response->getBody(), true) ?: [];
            $attributes = $data['data']['attributes'] ?? [];
            $stats = $attributes['last_analysis_stats'] ?? [];
            $lastAnalysis = $attributes['last_analysis_results'] ?? [];

            $malicious = (int) ($stats['malicious'] ?? 0);
            $suspicious = (int) ($stats['suspicious'] ?? 0);
            $harmless = (int) ($stats['harmless'] ?? 0);
            $totalScanners = array_sum(array_map('intval', $stats));

            $vendorResults = [];
            foreach ($lastAnalysis as $vendor => $details) {
                $category = strtolower($details['category'] ?? '');
                if (in_array($category, ['malicious', 'suspicious'], true)) {
                    $vendorResults[] = [
                        'vendor' => $vendor,
                        'category' => $details['category'] ?? '',
                        'result' => $details['result'] ?? '',
                    ];
                }
            }

            $result['details'] = [
                'malicious' => $malicious,
                'suspicious' => $suspicious,
                'harmless' => $harmless,
                'total_scanners' => $totalScanners,
                'detections' => $vendorResults,
                'summary' => $malicious . '/' . $totalScanners . ' security vendors flagged as malicious',
            ];

            $totalEngines = $malicious + $suspicious + $harmless;
            $phishingDetected = false;
            foreach ($vendorResults as $det) {
                if (stripos($det['result'], 'phish') !== false) {
                    $phishingDetected = true;
                    break;
                }
            }

            $maliciousRatio = $totalEngines > 0 ? ($malicious + $suspicious) / $totalEngines : 0;
            $isHighRisk = $malicious >= 5
                || $suspicious >= 5
                || $maliciousRatio >= 0.15
                || $phishingDetected;

            if ($isHighRisk) {
                $result['hit'] = true;
                $result['weight'] = 60;
            } elseif ($malicious > 0 || $suspicious > 0) {
                $result['hit'] = true;
                $result['weight'] = 30;
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkOtx($domain)
    {
        $result = [
            'name' => 'OTX',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        $apiKey = $this->apiKeyService->nextKey('otx');
        $headers = $apiKey ? ['X-OTX-API-KEY' => $apiKey] : [];

        try {
            $response = $this->httpClient->get(
                'https://otx.alienvault.com/api/v1/indicators/domain/' . urlencode($domain) . '/general',
                ['headers' => $headers]
            );
            $data = json_decode((string) $response->getBody(), true) ?: [];
            $count = (int) (($data['pulse_info']['count'] ?? 0));
            $result['details'] = ['pulses' => $count];
            if ($count > 0) {
                $result['hit'] = true;
                $result['weight'] = 15;
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkOpenPhish($url, $domain)
    {
        $result = [
            'name' => 'OpenPhish',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        try {
            $response = $this->httpClient->get('https://openphish.com/feed.txt');
            $text = (string) $response->getBody();
            if ($text === '') {
                $result['details'] = ['error' => 'Empty feed'];
                return $result;
            }

            $lines = array_filter(array_map('trim', explode("\n", $text)));
            $feedSet = array_flip($lines);
            $candidates = $this->urlCandidates($url);

            $exact = false;
            foreach ($candidates as $cand) {
                if (isset($feedSet[$cand])) {
                    $exact = true;
                    break;
                }
            }

            $domainMatch = false;
            if (!$exact) {
                $targetDomain = strtolower($this->registeredDomain($domain));
                foreach ($lines as $line) {
                    $lineDomain = strtolower($this->registeredDomain(parse_url($line, PHP_URL_HOST) ?: $line));
                    if ($lineDomain && $lineDomain === $targetDomain) {
                        $domainMatch = true;
                        break;
                    }
                }
            }

            $result['details'] = ['exact_match' => $exact, 'domain_match' => $domainMatch];
            if ($exact) {
                $result['hit'] = true;
                $result['weight'] = 60;
            } elseif ($domainMatch) {
                $result['hit'] = true;
                $result['weight'] = 30;
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkSafeBrowsing($url)
    {
        $result = [
            'name' => 'Google Safe Browsing',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        $apiKey = $this->apiKeyService->nextKey('safe_browsing');
        if (!$apiKey) {
            $result['details'] = ['error' => 'Missing Safe Browsing API key'];
            return $result;
        }

        $body = [
            'client' => ['clientId' => 'threat-intelligent', 'clientVersion' => '1.0.0'],
            'threatInfo' => [
                'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'],
                'platformTypes' => ['ANY_PLATFORM'],
                'threatEntryTypes' => ['URL'],
                'threatEntries' => [['url' => $url]],
            ],
        ];

        try {
            $response = $this->httpClient->post(
                'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . urlencode($apiKey),
                ['json' => $body]
            );
            $data = json_decode((string) $response->getBody(), true) ?: [];
            $matches = $data['matches'] ?? [];
            if (!empty($matches)) {
                $result['hit'] = true;
                $result['weight'] = 60;
                $result['details'] = ['threatTypes' => array_values(array_unique(array_column($matches, 'threatType')))];
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkUrlhaus($domain)
    {
        $result = [
            'name' => 'URLhaus',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        try {
            $response = $this->httpClient->post('https://urlhaus.abuse.ch/api/v1/host/', [
                'form_params' => ['host' => $domain],
            ]);
            $data = json_decode((string) $response->getBody(), true) ?: [];
            if (($data['query_status'] ?? '') === 'ok') {
                $urls = $data['urls'] ?? $data['malicious_urls'] ?? [];
                $count = is_array($urls) ? count($urls) : 0;
                $result['details'] = ['count' => $count];
                if ($count > 0) {
                    $result['hit'] = true;
                    $result['weight'] = 30;
                }
            } else {
                $result['details'] = ['query_status' => $data['query_status'] ?? 'unknown'];
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkPhishStats($url)
    {
        $result = [
            'name' => 'PhishStats',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        try {
            $response = $this->httpClient->get('https://phishstats.info/api/check?url=' . urlencode($url));
            $data = json_decode((string) $response->getBody(), true) ?: [];
            if (!empty($data['is_phishing'])) {
                $result['hit'] = true;
                $result['weight'] = 30;
            }
            $result['details'] = [
                'is_phishing' => $data['is_phishing'] ?? false,
                'confidence' => $data['confidence'] ?? 0,
            ];
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function checkScumware($url)
    {
        $result = [
            'name' => 'SCUMWARE',
            'category' => 'provider',
            'hit' => false,
            'weight' => 0,
            'details' => [],
        ];

        try {
            $response = $this->httpClient->get('https://www.scumware.org/search?q=' . urlencode($url));
            $body = (string) $response->getBody();
            if (stripos($body, $url) !== false || stripos($body, 'threat') !== false) {
                $result['hit'] = true;
                $result['details'] = ['found' => true];
            } else {
                $result['details'] = ['found' => false];
            }
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function searchUrlscanDomain($domain)
    {
        $result = [
            'name' => 'urlscan.io search',
            'category' => 'provider',
            'hit' => false,
            'details' => [],
        ];

        $apiKey = $this->apiKeyService->nextKey('urlscan');
        $headers = ['Accept' => 'application/json'];
        if ($apiKey) {
            $headers['API-Key'] = $apiKey;
        }

        try {
            $response = $this->httpClient->get(
                'https://urlscan.io/api/v1/search/?q=' . urlencode('domain:' . $domain),
                ['headers' => $headers]
            );
            $data = json_decode((string) $response->getBody(), true) ?: [];
            $total = (int) ($data['total'] ?? 0);
            $urls = [];
            foreach (array_slice($data['results'] ?? [], 0, 10) as $entry) {
                $pageUrl = $entry['page']['url'] ?? null;
                if ($pageUrl) {
                    $urls[] = $pageUrl;
                }
            }
            $result['hit'] = $total > 0;
            $result['details'] = ['total' => $total, 'sample_urls' => $urls];
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function submitToUrlscan($url)
    {
        $result = [
            'name' => 'urlscan.io submit',
            'category' => 'provider',
            'hit' => false,
            'details' => [],
        ];

        $apiKey = $this->apiKeyService->nextKey('urlscan');
        if (!$apiKey) {
            $result['details'] = ['error' => 'missing API key'];
            return $result;
        }

        try {
            $response = $this->httpClient->post('https://urlscan.io/api/v1/scan/', [
                'headers' => ['API-Key' => $apiKey, 'Content-Type' => 'application/json'],
                'json' => ['url' => $url, 'visibility' => 'public'],
            ]);
            $data = json_decode((string) $response->getBody(), true) ?: [];
            $uuid = $data['uuid'] ?? null;
            $result['hit'] = (bool) $uuid;
            $result['details'] = ['uuid' => $uuid];
        } catch (\Exception $e) {
            $result['details'] = ['error' => $e->getMessage()];
        }

        return $result;
    }

    private function urlCandidates($url)
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return [$url];
        }

        $candidates = [$url];
        foreach (['http', 'https'] as $scheme) {
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $built = $scheme . '://' . $parsed['host'] . $path . $query;
            $candidates[] = $built;
            if (substr($built, -1) !== '/') {
                $candidates[] = $built . '/';
            }
        }

        return array_unique($candidates);
    }

    private function registeredDomain($host)
    {
        $host = strtolower(preg_replace('/^www\./', '', (string) $host));
        $parts = explode('.', $host);
        if (count($parts) <= 2) {
            return $host;
        }

        $twoPartTlds = ['co.th', 'co.uk', 'com.au', 'co.jp', 'com.br', 'co.id'];
        $lastTwo = implode('.', array_slice($parts, -2));
        if (in_array($lastTwo, $twoPartTlds, true)) {
            return implode('.', array_slice($parts, -3));
        }

        return implode('.', array_slice($parts, -2));
    }

    private function throttleVt()
    {
        $elapsed = microtime(true) - $this->lastVtRequestAt;
        if ($elapsed < self::VT_MIN_INTERVAL) {
            usleep((int) ((self::VT_MIN_INTERVAL - $elapsed) * 1000000));
        }
        $this->lastVtRequestAt = microtime(true);
    }

    // --- Advanced Analysis ---

    private function comprehensiveAnalysis($url, $domain, $htmlContent = null)
    {
        $result = [
            'url_entropy' => $this->calculateUrlEntropy($url),
            'ssl_analysis' => $this->analyzeSslCertificate($domain),
            'redirect_analysis' => $this->analyzeRedirectChain($url),
            'dns_analysis' => $this->analyzeDnsRecords($domain),
            'javascript_analysis' => $this->detectJavascriptObfuscation($htmlContent ?: ''),
            'form_analysis' => $this->analyzeFormBehavior($htmlContent ?: ''),
        ];

        $suspiciousIndicators = 0;
        if ($result['url_entropy'] > 4.5) {
            $suspiciousIndicators++;
        }
        if (!empty($result['ssl_analysis']['issues'])) {
            $suspiciousIndicators++;
        }
        if (!empty($result['redirect_analysis']['suspicious'])) {
            $suspiciousIndicators++;
        }
        if (!empty($result['dns_analysis']['suspicious'])) {
            $suspiciousIndicators++;
        }
        if (!empty($result['javascript_analysis']['obfuscated'])) {
            $suspiciousIndicators++;
        }
        if (($result['form_analysis']['suspicious_forms'] ?? 0) > 0) {
            $suspiciousIndicators++;
        }

        $result['suspicious_score'] = $suspiciousIndicators / 6.0;
        $result['overall_assessment'] = $result['suspicious_score'] > 0.5 ? 'High Risk' : 'Low Risk';

        return $result;
    }

    private function calculateVisualSimilarity($officialUrl, $candidateUrl)
    {
        $official = parse_url($officialUrl);
        $candidate = parse_url($candidateUrl);
        if (!$official || !$candidate || empty($official['host']) || empty($candidate['host'])) {
            return 0.0;
        }

        $officialParts = explode('.', $official['host']);
        $candidateParts = explode('.', $candidate['host']);

        if (count($officialParts) !== count($candidateParts)) {
            return 0.0;
        }

        $similar = 0;
        foreach ($officialParts as $i => $part) {
            if (($candidateParts[$i] ?? '') === $part) {
                $similar++;
            }
        }

        return count($officialParts) > 0 ? $similar / count($officialParts) : 0.0;
    }

    private function calculateUrlEntropy($url)
    {
        if (!$url) {
            return 0.0;
        }

        $freq = [];
        $len = strlen($url);
        for ($i = 0; $i < $len; $i++) {
            $ch = $url[$i];
            $freq[$ch] = ($freq[$ch] ?? 0) + 1;
        }

        $entropy = 0.0;
        foreach ($freq as $count) {
            $p = $count / $len;
            $entropy -= $p * (log($p) / log(2));
        }

        return $entropy;
    }

    private function analyzeSslCertificate($domain)
    {
        $result = [
            'valid' => false,
            'self_signed' => false,
            'expired' => false,
            'issuer' => null,
            'issues' => [],
        ];

        try {
            $ctx = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
            $read = @stream_socket_client('ssl://' . $domain . ':443', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
            if (!$read) {
                $result['issues'][] = 'SSL connection failed';
                return $result;
            }

            $params = stream_context_get_params($read);
            fclose($read);
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
            $result['valid'] = true;
            $result['issuer'] = is_array($cert['issuer']['O'] ?? null)
                ? implode(', ', $cert['issuer']['O'])
                : ($cert['issuer']['O'] ?? 'Unknown');

            if (($cert['validTo_time_t'] ?? 0) < time()) {
                $result['expired'] = true;
                $result['issues'][] = 'Certificate expired';
            }
        } catch (\Exception $e) {
            $result['issues'][] = 'SSL error: ' . $e->getMessage();
        }

        return $result;
    }

    private function analyzeRedirectChain($url)
    {
        $result = [
            'redirects' => [],
            'final_url' => $url,
            'redirect_count' => 0,
            'suspicious' => false,
            'issues' => [],
        ];

        try {
            $redirectCount = 0;
            $response = $this->httpClient->get($url, [
                'allow_redirects' => [
                    'max' => 10,
                    'track_redirects' => true,
                ],
            ]);

            if ($response->hasHeader('X-Guzzle-Redirect-History')) {
                $redirectCount = count($response->getHeader('X-Guzzle-Redirect-History'));
            }

            $result['redirect_count'] = $redirectCount;
            $result['suspicious'] = $redirectCount > 5;
            if ($result['suspicious']) {
                $result['issues'][] = 'Too many redirects';
            }
        } catch (\Exception $e) {
            $result['issues'][] = 'Redirect analysis error: ' . $e->getMessage();
        }

        return $result;
    }

    private function analyzeDnsRecords($domain)
    {
        $result = [
            'mx_records' => [],
            'txt_records' => [],
            'ns_records' => [],
            'issues' => [],
            'suspicious' => false,
        ];

        $mx = @dns_get_record($domain, DNS_MX);
        if ($mx) {
            foreach ($mx as $record) {
                $result['mx_records'][] = ($record['target'] ?? '') . ' pri=' . ($record['pri'] ?? 0);
            }
        } else {
            $result['issues'][] = 'No MX records found';
        }

        $txt = @dns_get_record($domain, DNS_TXT);
        if ($txt) {
            foreach ($txt as $record) {
                $result['txt_records'][] = $record['txt'] ?? '';
            }
        }

        $ns = @dns_get_record($domain, DNS_NS);
        if ($ns) {
            foreach ($ns as $record) {
                $result['ns_records'][] = $record['target'] ?? '';
            }
        } else {
            $result['issues'][] = 'No NS records found';
        }

        if (empty($result['mx_records']) && empty($result['txt_records'])) {
            $result['suspicious'] = true;
            $result['issues'][] = 'No email-related DNS records';
        }

        return $result;
    }

    private function detectJavascriptObfuscation($htmlContent)
    {
        $result = [
            'obfuscated' => false,
            'suspicious_patterns' => [],
            'obfuscation_score' => 0.0,
        ];

        if (!$htmlContent) {
            return $result;
        }

        $patterns = [
            'eval(' => 'eval()',
            'unescape(' => 'unescape()',
            'String.fromCharCode' => 'fromCharCode',
            'atob(' => 'atob()',
            'btoa(' => 'btoa()',
        ];

        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $htmlContent, $matches);
        $blocks = $matches[1] ?? [];
        $totalBlocks = 0;
        $suspiciousCount = 0;

        foreach ($blocks as $block) {
            if (trim($block) === '') {
                continue;
            }
            $totalBlocks++;
            foreach ($patterns as $needle => $label) {
                $count = substr_count($block, $needle);
                if ($count > 0) {
                    $suspiciousCount += $count;
                    $result['suspicious_patterns'][] = $label;
                }
            }
        }

        if ($totalBlocks > 0) {
            $result['obfuscation_score'] = $suspiciousCount / $totalBlocks;
            $result['obfuscated'] = $result['obfuscation_score'] > 0.3;
        }

        return $result;
    }

    private function analyzeFormBehavior($htmlContent)
    {
        $result = [
            'forms_found' => 0,
            'suspicious_forms' => 0,
            'password_fields' => 0,
            'suspicious_actions' => [],
            'issues' => [],
        ];

        if (!$htmlContent) {
            return $result;
        }

        $lower = strtolower($htmlContent);
        $result['forms_found'] = substr_count($lower, '<form');
        $result['password_fields'] = substr_count($lower, 'type="password"');

        if (preg_match_all('/<form[^>]*action=["\']([^"\']*)["\'][^>]*>/i', $htmlContent, $formMatches)) {
            foreach ($formMatches[1] as $action) {
                if ($action && strpos($action, 'http://') !== false && strpos($action, 'https://') === false) {
                    $result['suspicious_forms']++;
                    $result['suspicious_actions'][] = 'Mixed content form action: ' . $action;
                }
            }
        }

        if ($result['password_fields'] > 2) {
            $result['issues'][] = 'Multiple password fields detected';
        }

        return $result;
    }

    private function getWhoisAgeDays($domain)
    {
        $host = preg_replace('/^www\./', '', strtolower($domain));
        $creationDate = $this->lookupWhoisCreationDate($host);
        if (!$creationDate) {
            return null;
        }

        $timestamp = strtotime($creationDate);
        if (!$timestamp) {
            return null;
        }

        return max(0, (int) floor((time() - $timestamp) / 86400));
    }

    private function lookupWhoisCreationDate($host)
    {
        try {
            $referralServer = null;
            $fp = @fsockopen('whois.iana.org', 43, $errno, $errstr, 5);
            if ($fp) {
                fwrite($fp, $host . "\r\n");
                $out = '';
                while (!feof($fp)) {
                    $out .= fgets($fp);
                }
                fclose($fp);

                if (preg_match('/refer:\s+(.*)/i', $out, $matches)) {
                    $referralServer = trim($matches[1]);
                } elseif (preg_match('/whois:\s+(.*)/i', $out, $matches)) {
                    $referralServer = trim($matches[1]);
                }
            }

            if (!$referralServer) {
                $referralServer = 'whois.verisign-grs.com';
            }

            $fp = @fsockopen($referralServer, 43, $errno, $errstr, 5);
            if (!$fp) {
                return null;
            }

            fwrite($fp, $host . "\r\n");
            $whoisData = '';
            while (!feof($fp)) {
                $whoisData .= fgets($fp);
            }
            fclose($fp);

            $patterns = [
                '/Creation Date:\s*(.*)/i',
                '/Created on:\s*(.*)/i',
                '/Registration Time:\s*(.*)/i',
                '/created(?:\s+date)?:\s*(.*)/i',
                '/Registered on:\s*(.*)/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $whoisData, $matches)) {
                    $dateStr = trim(preg_replace('/\s*\(.*\)/', '', $matches[1]));
                    if (strtotime($dateStr)) {
                        return $dateStr;
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    // --- ML Enhancement ---

    private function enhancedPhishingDetection($url, $domain, $htmlContent = null)
    {
        $features = $this->extractAdvancedFeatures($url, $domain, $htmlContent);
        list($mlScore, $mlLabel) = $this->calculateMlScore($features);

        $highRisk = [];
        foreach ($features as $key => $value) {
            if (is_numeric($value) && $value > 0 && stripos($key, 'suspicious') !== false) {
                $highRisk[] = $key;
            }
        }

        return [
            'features' => $features,
            'ml_score' => $mlScore,
            'ml_label' => $mlLabel,
            'feature_count' => count($features),
            'high_risk_features' => $highRisk,
        ];
    }

    private function extractAdvancedFeatures($url, $domain, $htmlContent = null)
    {
        $features = array_merge(
            $this->extractUrlFeatures($url),
            $this->extractDomainFeatures($domain)
        );

        if ($htmlContent) {
            $features = array_merge($features, $this->extractHtmlFeatures($htmlContent));
        }

        return $features;
    }

    private function extractUrlFeatures($url)
    {
        $features = [
            'url_length' => 0,
            'domain_length' => 0,
            'path_length' => 0,
            'query_length' => 0,
            'url_entropy' => 0,
            'has_ip' => 0,
            'has_suspicious_tld' => 0,
            'special_char_ratio' => 0,
        ];

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $features;
        }

        $host = $parsed['host'];
        $path = $parsed['path'] ?? '';
        $query = $parsed['query'] ?? '';

        $features['url_length'] = strlen($url);
        $features['domain_length'] = strlen($host);
        $features['path_length'] = strlen($path);
        $features['query_length'] = strlen($query);
        $features['url_entropy'] = $this->calculateMlEntropy($url);
        $features['has_ip'] = preg_match('/^\d+\.\d+\.\d+\.\d+/', $host) ? 1 : 0;
        $features['has_suspicious_tld'] = preg_match('/\.(tk|ml|ga|cf)$/i', $host) ? 1 : 0;
        $features['special_char_ratio'] = strlen($url) > 0
            ? array_sum(array_map(function ($c) {
                return !ctype_alnum($c) ? 1 : 0;
            }, str_split($url))) / strlen($url)
            : 0;

        return $features;
    }

    private function extractDomainFeatures($domain)
    {
        $parts = explode('.', $domain);
        $domainName = $parts[0] ?? $domain;
        $tld = end($parts) ?: '';

        return [
            'subdomain_count' => max(0, count($parts) - 2),
            'has_numbers' => preg_match('/\d/', $domainName) ? 1 : 0,
            'has_hyphens' => strpos($domainName, '-') !== false ? 1 : 0,
            'is_common_tld' => in_array(strtolower($tld), ['com', 'org', 'net', 'edu', 'gov'], true) ? 1 : 0,
            'suspicious_keywords' => $this->countSuspiciousKeywords($domainName),
        ];
    }

    private function extractHtmlFeatures($htmlContent)
    {
        $lower = strtolower($htmlContent);

        return [
            'form_count' => substr_count($lower, '<form'),
            'password_inputs' => substr_count($lower, 'type="password"'),
            'suspicious_js_functions' => $this->countSuspiciousJsFunctions($htmlContent),
            'external_scripts' => substr_count($lower, 'src='),
            'text_to_html_ratio' => strlen($htmlContent) > 0
                ? strlen(strip_tags($htmlContent)) / strlen($htmlContent)
                : 0,
        ];
    }

    private function calculateMlScore(array $features)
    {
        $score = 0.0;

        if (($features['url_length'] ?? 0) > 100) {
            $score += 0.1;
        }
        if (($features['url_entropy'] ?? 0) > 4.0) {
            $score += 0.15;
        }
        if (!empty($features['has_ip'])) {
            $score += 0.2;
        }
        if (!empty($features['has_suspicious_tld'])) {
            $score += 0.15;
        }
        if (($features['special_char_ratio'] ?? 0) > 0.3) {
            $score += 0.1;
        }
        if (($features['subdomain_count'] ?? 0) > 3) {
            $score += 0.1;
        }
        if (!empty($features['has_numbers']) && !empty($features['has_hyphens'])) {
            $score += 0.1;
        }
        if (($features['suspicious_keywords'] ?? 0) > 2) {
            $score += 0.15;
        }
        if (empty($features['is_common_tld'])) {
            $score += 0.05;
        }
        if (($features['form_count'] ?? 0) > 2) {
            $score += 0.1;
        }
        if (($features['password_inputs'] ?? 0) > 1) {
            $score += 0.15;
        }
        if (($features['suspicious_js_functions'] ?? 0) > 3) {
            $score += 0.2;
        }
        if (($features['external_scripts'] ?? 0) > 5) {
            $score += 0.1;
        }
        if (($features['text_to_html_ratio'] ?? 1) < 0.1) {
            $score += 0.1;
        }

        $score = min($score, 1.0);

        if ($score >= 0.7) {
            $label = 'High Risk';
        } elseif ($score >= 0.4) {
            $label = 'Medium Risk';
        } else {
            $label = 'Low Risk';
        }

        return [$score, $label];
    }

    private function calculateMlEntropy($text)
    {
        if ($text === '' || $text === null) {
            return 0.0;
        }

        $freq = [];
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            $freq[$ch] = ($freq[$ch] ?? 0) + 1;
        }

        $entropy = 0.0;
        foreach ($freq as $count) {
            $p = $count / $len;
            $entropy -= $p * (log($p) / log(2));
        }

        return $entropy;
    }

    private function countSuspiciousKeywords($text)
    {
        $keywords = [
            'login', 'signin', 'secure', 'verify', 'account', 'support', 'update',
            'reset', 'password', 'wallet', 'payment', 'auth', 'confirm', 'suspended',
        ];
        $text = strtolower($text);
        $count = 0;
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $count++;
            }
        }

        return $count;
    }

    private function countSuspiciousJsFunctions($html)
    {
        $functions = ['eval(', 'unescape(', 'String.fromCharCode', 'atob(', 'btoa('];
        $count = 0;
        foreach ($functions as $func) {
            $count += substr_count($html, $func);
        }

        return $count;
    }

    // --- Scoring ---

    private function scoreToLabel($score)
    {
        if ($score >= 85) {
            return 'Phishing';
        }
        if ($score >= 60) {
            return 'Suspicious';
        }

        return 'Benign';
    }

    private function computeScoreV3(
        bool $reputationHit,
        bool $keywordIndicators,
        $domainAgeDays,
        bool $freeHosting,
        bool $hotlinkAssets,
        $visualSimilarity = null,
        bool $sslIssues = false,
        int $redirectChain = 0,
        bool $javascriptObfuscation = false,
        bool $formAnalysis = false,
        $urlEntropy = null,
        bool $dnsIssues = false
    ) {
        $total = 0;

        if ($reputationHit) {
            $total += 35;
        }
        if ($keywordIndicators) {
            $total += 18;
        }
        if ($domainAgeDays !== null && $domainAgeDays >= 0 && $domainAgeDays < 90) {
            $total += 12;
        }
        if ($freeHosting) {
            $total += 8;
        }
        if ($hotlinkAssets) {
            $total += 8;
        }
        if ($visualSimilarity !== null && $visualSimilarity > 0.8) {
            $total += 15;
        }
        if ($sslIssues) {
            $total += 10;
        }
        if ($redirectChain > 3) {
            $total += 8;
        }
        if ($javascriptObfuscation) {
            $total += 12;
        }
        if ($formAnalysis) {
            $total += 10;
        }
        if ($urlEntropy !== null && $urlEntropy > 4.5) {
            $total += 8;
        }
        if ($dnsIssues) {
            $total += 6;
        }

        $total = max(0, min(100, $total));

        if ($total >= 85) {
            $label = 'Phishing';
        } elseif ($total >= 60) {
            $label = 'Suspicious';
        } else {
            $label = 'Benign';
        }

        return ['score' => $total, 'label' => $label];
    }
}
