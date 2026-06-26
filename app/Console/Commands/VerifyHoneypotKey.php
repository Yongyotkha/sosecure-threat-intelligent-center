<?php

namespace App\Console\Commands;

use App\ApiToken;
use Illuminate\Console\Command;

class VerifyHoneypotKey extends Command
{
    protected $signature = 'honeypot:verify-key {api_key : Plain X-API-Key value to test}';

    protected $description = 'Check whether a honeypot API key matches a record in api_tokens';

    public function handle()
    {
        $apiKey = trim((string) $this->argument('api_key'));

        if ($apiKey === '') {
            $this->error('API key is empty.');

            return 1;
        }

        $hashed = hash('sha256', $apiKey);
        $tokenType = (string) config('honeypot.api.token_type', 'honeypot_agent');

        $byHash = ApiToken::where('token', $hashed)->first();
        $byPlain = ApiToken::where('token', $apiKey)->first();
        $honeypot = ApiToken::where('token', $hashed)
            ->where('type', $tokenType)
            ->where('scope', ApiToken::SCOPE_SITE)
            ->first();

        $this->line('Key length : ' . strlen($apiKey));
        $this->line('SHA-256    : ' . substr($hashed, 0, 12) . '...');

        if ($byPlain && !$byHash) {
            $this->warn('This value matches a token row as PLAINTEXT (not SHA-256).');
        }

        if (!$byHash && !$byPlain) {
            $this->error('No api_tokens row matches this key (hashed or plain).');

            return 1;
        }

        $row = $byHash ?: $byPlain;

        $this->info('Token row found (id ' . $row->id . ')');
        $this->line('type       : ' . ($row->type ?: '(empty)'));
        $this->line('scope      : ' . ($row->scope ?: '(empty)'));
        $this->line('site_id    : ' . ($row->site_id ?: '(empty)'));
        $this->line('whitelist  : ' . ($row->whitelist_ips ?: '(empty)'));

        if ($honeypot) {
            $this->info('Honeypot auth: OK');

            return 0;
        }

        $this->error('Honeypot auth: FAIL');

        return 1;
    }
}
