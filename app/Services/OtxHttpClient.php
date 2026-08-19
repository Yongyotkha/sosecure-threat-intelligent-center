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
     * @return array{success:bool,result:?string,error:?string,attempts:int}
     */
    public function get($url, $timeout = null)
    {
        $attempt = 0;
        $lastError = null;
        $timeout = $timeout !== null ? (int) $timeout : $this->timeout;

        while ($attempt < $this->maxRetries) {
            $attempt++;
            try {
                $body = $this->client->request('GET', $url, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json',
                        'X-OTX-API-KEY' => $this->apiKey,
                    ],
                    'timeout' => $timeout,
                    'connect_timeout' => 30,
                ])->getBody()->getContents();

                return [
                    'success' => true,
                    'result' => $body,
                    'error' => null,
                    'attempts' => $attempt,
                ];
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                // Do not retry client errors (404/401/403) — pulse may be gone on OTX.
                if (preg_match('/\b(404|401|403)\b/', $lastError)) {
                    break;
                }
                // Gateway timeouts rarely recover on the 3rd–5th wait; cap extra attempts.
                $isGateway = (bool) preg_match('/\b(502|503|504)\b/', $lastError);
                if ($isGateway && $attempt >= 2) {
                    break;
                }
                if ($attempt < $this->maxRetries) {
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
}
