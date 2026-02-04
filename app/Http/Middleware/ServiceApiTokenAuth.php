<?php

namespace App\Http\Middleware;

use App\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ServiceApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'No permission'
            ], 401);
        }

        $apiToken = ApiToken::where('token', $token)
            ->where('type', 'service_receive_api')
            ->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API token or wrong token type'
            ], 401);
        }

        if ($apiToken->isExpired()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token has expired'
            ], 401);
        }

        $apiToken->update(['last_used_at' => now()]);

        $request->attributes->set('site_id', $apiToken->site_id);
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
