<?php

namespace App\Services;

use App\ApiToken;

class SystemApiKeyService
{
    private static $keysCache = [];
    private static $counters = [];

    private static $envMap = [
        'virustotal' => 'VIRUSTOTAL_API_KEYS',
        'serpapi' => 'SERPAPI_API_KEYS',
        'otx' => 'OTX_API_KEYS',
        'safe_browsing' => 'SAFE_BROWSING_API_KEYS',
        'urlscan' => 'URLSCAN_API_KEYS',
    ];

    public function getKeys(string $provider): array
    {
        if (isset(self::$keysCache[$provider])) {
            return self::$keysCache[$provider];
        }

        $dbKeys = ApiToken::getProviderTokens($provider, null, ApiToken::SCOPE_SYSTEM)
            ->filter(function ($token) {
                return !$token->isExpired() && trim((string) $token->token) !== '';
            })
            ->pluck('token')
            ->all();

        $envKeys = $this->keysFromEnv($provider);

        self::$keysCache[$provider] = array_values(array_unique(array_merge($dbKeys, $envKeys)));

        return self::$keysCache[$provider];
    }

    public function nextKey(string $provider): ?string
    {
        $keys = $this->getKeys($provider);
        if (empty($keys)) {
            return null;
        }

        $index = self::$counters[$provider] ?? 0;
        self::$counters[$provider] = $index + 1;

        return $keys[$index % count($keys)];
    }

    public function hasKeys(string $provider): bool
    {
        return !empty($this->getKeys($provider));
    }

    public function keyCount(string $provider): int
    {
        return count($this->getKeys($provider));
    }

    private function keysFromEnv(string $provider): array
    {
        $envKey = self::$envMap[$provider] ?? null;
        if (!$envKey) {
            return [];
        }

        $value = env($envKey, '');
        if ($value === '') {
            if ($provider === 'virustotal') {
                $single = trim((string) env('VIRUSTOTAL_API_KEY', ''));
                return $single !== '' ? [$single] : [];
            }
            if ($provider === 'otx') {
                $single = trim((string) env('OTX_API_KEY', ''));
                return $single !== '' ? [$single] : [];
            }
            if ($provider === 'safe_browsing') {
                $single = trim((string) env('SAFE_BROWSING_API_KEY', ''));
                return $single !== '' ? [$single] : [];
            }
            if ($provider === 'urlscan') {
                $single = trim((string) env('URLSCAN_API_KEY', ''));
                return $single !== '' ? [$single] : [];
            }

            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
