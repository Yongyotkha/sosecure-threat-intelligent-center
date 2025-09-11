<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
// เลือก use ให้ตรงกับโมเดลของคุณ
// use App\Models\ApiToken;   // ถ้าโมเดลอยู่ที่ app/Models/ApiToken.php
use App\ApiToken;      // ถ้าโมเดลอยู่ที่ app/ApiToken.php

class MakeApiToken extends Command
{
    protected $signature = 'token:make {name=Feed Token} {--days=30}';
    protected $description = 'Generate an API token (PLAINTEXT stored) with optional expiry days';

    public function handle()
    {
        $name = $this->argument('name');
        $days = (int) $this->option('days');

        // ออก token แบบ plaintext
        $plain = Str::random(60);
        while (ApiToken::where('token', $plain)->exists()) {
            $plain = Str::random(60);
        }

        $token = ApiToken::create([
            'name'       => $name,
            'token'      => $plain, // << เก็บ plaintext ตามที่คุณเลือก
            'expires_at' => $days > 0 ? now()->addDays($days) : null,
        ]);

        $this->info('=== API TOKEN CREATED ===');
        $this->line('Name      : '.$name);
        $this->line('Expires   : '.($token->expires_at ? $token->expires_at->toDateTimeString() : 'never'));
        $this->line('PlainText : '.$plain);
        return 0;
    }
}
