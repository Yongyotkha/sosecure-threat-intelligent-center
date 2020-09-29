<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

use Firebase\JWT\JWT;
use Modules\Users\Entities\User;
use Exception;
// use Firebase\JWT\ExpiredException;
use Tymon\JWTAuth\TokenExpiredException;

class JWTT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        }

        return $next($request);
    }
}
