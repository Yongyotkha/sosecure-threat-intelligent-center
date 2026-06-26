<?php

namespace App\Console\Commands;

use App\ApiToken;
use Illuminate\Console\Command;

class RehashHoneypotKey extends Command
{
    protected $signature = 'honeypot:rehash-key {api_key : Plain X-API-Key currently stored or sent by the sensor}';

    protected $description = 'Convert a honeypot api_tokens row from plaintext storage to SHA-256';

    public function handle()
    {
        $plain = trim((string) $this->argument('api_key'));
        $tokenType = (string) config('honeypot.api.token_type', 'honeypot_agent');
        $hashed = hash('sha256', $plain);

        $token = ApiToken::where('token', $plain)
            ->where('type', $tokenType)
            ->where('scope', ApiToken::SCOPE_SITE)
            ->first();

        if (!$token) {
            $already = ApiToken::where('token', $hashed)
                ->where('type', $tokenType)
                ->where('scope', ApiToken::SCOPE_SITE)
                ->first();

            if ($already) {
                $this->info('Token id ' . $already->id . ' is already stored as SHA-256.');

                return 0;
            }

            $this->error('No honeypot_agent row found with this plain token value.');

            return 1;
        }

        if (ApiToken::where('token', $hashed)->where('id', '!=', $token->id)->exists()) {
            $this->error('SHA-256 hash already exists on another api_tokens row.');

            return 1;
        }

        $token->forceFill(['token' => $hashed])->save();

        $this->info('Token id ' . $token->id . ' rehashed to SHA-256.');

        return 0;
    }
}
