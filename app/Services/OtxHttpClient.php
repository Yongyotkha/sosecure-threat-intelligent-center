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
     * @return array{success:bool,result:?string,error:?string,attempts:int}
     */
    public function get($url)
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;
            try {
                $body = $this->client->request('GET', $url, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json',
                        'X-OTX-API-KEY' => $this->apiKey,
                    ],
                    'timeout' => $this->timeout,
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
                if ($attempt < $this->maxRetries) {
                    sleep(min(30, 2 ** $attempt));
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
