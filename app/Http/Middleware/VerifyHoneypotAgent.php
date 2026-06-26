<?php

namespace App\Http\Middleware;

use App\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyHoneypotAgent
{
    public function handle(Request $request, Closure $next)
    {
        $header = (string) config('honeypot.api.header', 'X-API-Key');
        $apiKey = trim((string) $request->header($header));

        if ($apiKey === '') {
            Log::warning('HONEYPOT.AUTH missing', ['path' => $request->path(), 'ip' => $request->ip()]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $hashed = hash('sha256', $apiKey);
        $tokenType = (string) config('honeypot.api.token_type', 'honeypot_agent');

        $token = $this->findHoneypotToken($hashed, $tokenType);

        if (!$token) {
            $legacy = $this->findHoneypotToken($apiKey, $tokenType);

            if ($legacy) {
                Log::warning('HONEYPOT.AUTH upgrading plaintext token to SHA-256', ['id' => $legacy->id]);
                $legacy->forceFill(['token' => $hashed])->save();
                $token = $legacy;
            }
        }

        if (!$token) {
            Log::warning('HONEYPOT.AUTH invalid', [
                'prefix' => substr($apiKey, 0, 6),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($token->isExpired()) {
            Log::warning('HONEYPOT.AUTH expired', ['id' => $token->id]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (empty($token->site_id)) {
            Log::warning('HONEYPOT.AUTH missing site binding', ['id' => $token->id]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $clientIp = $request->ip();

        if (empty($token->whitelist_ips)) {
            Log::warning('HONEYPOT.AUTH no whitelist', ['id' => $token->id, 'ip' => $clientIp]);

            return response()->json(['message' => 'Forbidden'], 403);
        }

        $allowedIps = array_filter(array_map('trim', explode(',', str_replace(["\r\n", "\n"], ',', $token->whitelist_ips))));

        if (!in_array($clientIp, $allowedIps, true)) {
            Log::warning('HONEYPOT.AUTH ip denied', [
                'id' => $token->id,
                'client_ip' => $clientIp,
                'allowed' => $allowedIps,
            ]);

            return response()->json(['message' => 'Forbidden'], 403);
        }

        $token->forceFill([
            'last_used_at' => now(),
            'last_ip' => $clientIp,
        ])->save();

        $request->attributes->set('api_token', $token);
        $request->attributes->set('site_id', (int) $token->site_id);
        $request->attributes->set('honeypot_agent_name', $token->name);

        return $next($request);
    }

    protected function findHoneypotToken(string $tokenValue, string $tokenType)
    {
        return ApiToken::where('token', $tokenValue)
            ->where('type', $tokenType)
            ->where('scope', ApiToken::SCOPE_SITE)
            ->first();
    }
}
