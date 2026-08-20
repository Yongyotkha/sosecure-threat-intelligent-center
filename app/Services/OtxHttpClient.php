<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

class OtxHttpClient
{
    protected $apiKey;
    protected $timeout;
    protected $maxRetries;
    protected $client;

    public function __construct($timeout = 180, $maxRetries = 5)
    {
        $this->apiKey = env('OTX_KEY', '');
        $this->timeout = $timeout;
        $this->maxRetries = $maxRetries;
        $this->client = new Client();
    }

    /**
     * GET JSON from OTX with retry + exponential backoff.
     *
     * @param string $url
     * @param int|null $timeout Override constructor timeout (seconds)
     * @param int|null $maxRetries Override constructor retries
     * @return array{success:bool,result:?string,error:?string,attempts:int}
     */
    public function get($url, $timeout = null, $maxRetries = null)
    {
        $attempt = 0;
        $lastError = null;
        $timeout = $timeout !== null ? (int) $timeout : $this->timeout;
        $maxRetries = $maxRetries !== null ? (int) $maxRetries : $this->maxRetries;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $body = $this->client->request('GET', $url, $this->requestOptions($timeout))->getBody()->getContents();

                return [
                    'success' => true,
                    'result' => $body,
                    'error' => null,
                    'attempts' => $attempt,
                ];
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                if (preg_match('/\b(404|401|403)\b/', $lastError)) {
                    break;
                }
                $isGateway = (bool) preg_match('/\b(502|503|504)\b/', $lastError);
                if ($isGateway && $attempt >= 2) {
                    break;
                }
                if ($attempt < $maxRetries) {
                    sleep($isGateway ? 2 : min(30, 2 ** $attempt));
                }
            }
        }

        return [
            'success' => false,
            'result' => null,
            'error' => $lastError,
            'attempts' => $attempt,
        ];
    }

    /**
     * Parallel GET. One extra parallel retry for non-404 failures.
     *
     * @param array<string,string> $urls keyed urls
     * @return array<string,array{success:bool,result:?string,error:?string,attempts:int}>
     */
    public function getMany(array $urls, $timeout = null)
    {
        if (empty($urls)) {
            return [];
        }
        $timeout = $timeout !== null ? (int) $timeout : $this->timeout;
        $out = $this->getManyOnce($urls, $timeout);
        $retry = [];
        foreach ($out as $key => $resp) {
            if (!empty($resp['success'])) {
                continue;
            }
            $err = (string) ($resp['error'] ?? '');
            if (preg_match('/\b(404|401|403)\b/', $err)) {
                continue;
            }
            $retry[$key] = $urls[$key];
        }
        if ($retry) {
            $second = $this->getManyOnce($retry, $timeout);
            foreach ($second as $key => $resp) {
                $resp['attempts'] = (int) ($out[$key]['attempts'] ?? 1) + (int) ($resp['attempts'] ?? 1);
                $out[$key] = $resp;
            }
        }

        return $out;
    }

    protected function getManyOnce(array $urls, $timeout)
    {
        $promises = [];
        foreach ($urls as $key => $url) {
            $promises[$key] = $this->client->requestAsync('GET', $url, $this->requestOptions($timeout));
        }
        $settled = \GuzzleHttp\Promise\settle($promises)->wait();
        $out = [];
        foreach ($settled as $key => $outcome) {
            if (($outcome['state'] ?? '') === 'fulfilled') {
                $out[$key] = [
                    'success' => true,
                    'result' => (string) $outcome['value']->getBody(),
                    'error' => null,
                    'attempts' => 1,
                ];
                continue;
            }
            $reason = $outcome['reason'] ?? null;
            $out[$key] = [
                'success' => false,
                'result' => null,
                'error' => $reason instanceof Exception ? $reason->getMessage() : (string) $reason,
                'attempts' => 1,
            ];
        }

        return $out;
    }

    protected function requestOptions($timeout)
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
                'X-OTX-API-KEY' => $this->apiKey,
            ],
            'timeout' => $timeout,
            'connect_timeout' => 15,
        ];
    }
}
