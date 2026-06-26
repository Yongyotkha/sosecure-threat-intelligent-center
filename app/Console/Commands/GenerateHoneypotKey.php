<?php

namespace App\Console\Commands;

use App\ApiToken;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;

class GenerateHoneypotKey extends Command
{
    protected $signature = 'honeypot:generate-key
                            {site_id : Site ID from the site table}
                            {name=Honeypot Agent : Display name for this sensor node}
                            {--whitelist_ips= : Comma-separated allowed source IPs (required)}
                            {--days=0 : Expiry in days (0 = never expires)}';

    protected $description = 'Generate a honeypot agent API key (SHA-256 stored, plain key shown once)';

    public function handle()
    {
        $siteId = (int) $this->argument('site_id');
        $name = trim((string) $this->argument('name'));
        $whitelist = trim((string) $this->option('whitelist_ips'));
        $days = (int) $this->option('days');

        $site = SiteSettings::find($siteId);
        if (!$site) {
            $this->error("Site ID {$siteId} was not found.");

            return 1;
        }

        if ($whitelist === '') {
            $this->error('Option --whitelist_ips is required for honeypot agents.');

            return 1;
        }

        do {
            $plain = bin2hex(random_bytes(32));
            $hashed = hash('sha256', $plain);
        } while (ApiToken::where('token', $hashed)->exists());

        $token = ApiToken::create([
            'name' => $name,
            'token' => $hashed,
            'type' => config('honeypot.api.token_type', 'honeypot_agent'),
            'scope' => ApiToken::SCOPE_SITE,
            'site_id' => $siteId,
            'whitelist_ips' => $whitelist,
            'expires_at' => $days > 0 ? now()->addDays($days) : null,
        ]);

        $token->refresh();

        if ($token->token !== $hashed) {
            $this->error('Token was not stored as SHA-256. Saved value does not match hash.');

            return 1;
        }

        $this->info('=== HONEYPOT AGENT KEY CREATED ===');
        $this->line('ID        : ' . $token->id);
        $this->line('Site ID   : ' . $siteId . ' (' . $site->name . ')');
        $this->line('Name      : ' . $name);
        $this->line('Whitelist : ' . $whitelist);
        $this->line('Expires   : ' . ($token->expires_at ? $token->expires_at->toDateTimeString() : 'never'));
        $this->line('');
        $this->warn('Copy this API key now. It will not be shown again.');
        $this->line('X-API-Key : ' . $plain);

        return 0;
    }
}
