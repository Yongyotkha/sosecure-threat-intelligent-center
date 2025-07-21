<?php
namespace App\Http\Middleware;

use Modules\Users\Entities\User;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
      $token = $request->header('Authorization');

        if (!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Token not provided.',
                'message' => 'Token not provided.',
                'status_code' => '02',//error
            ], 401);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'error' => 'Provided token is expired.',
                'message' => 'Provided token is expired.',
                'status_code' => '01',//error
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error while decoding token.',
                'message' => 'An error while decoding token.',
                'status_code' => '02',//error
            ], 400);
        }
        $user = User::find($credentials->sub);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);
    }
}