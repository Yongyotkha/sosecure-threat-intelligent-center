<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\ApiToken; // โมเดลของคุณอยู่ที่ App\

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        // 1) ดึง token จากหลายแหล่ง
        // $bearer = $request->bearerToken()
        //     ?: $request->query('token')
        //     ?: $request->query('api_token')
        //     ?: $request->header('X-Api-Token');

        // if ($bearer && strpos($bearer, 'Bearer ') === 0) {
        //     $bearer = substr($bearer, 7);
        // }
        // $bearer = $bearer ? trim($bearer, " \t\n\r\0\x0B\"'") : null;

        // $bearer = $request->route('token')   // สำคัญสำหรับ /feeds/t/{token}/...
        //     ?: $request->bearerToken()
        //     ?: $request->query('token')
        //     ?: $request->query('api_token')
        //     ?: $request->header('X-Api-Token');
        $bearer = $request->bearerToken()          // <-- Header: Authorization: Bearer <token>
            ?: $request->route('token')            // token-in-path (เดิม)
            ?: $request->query('token')            // query param
            ?: $request->header('X-Api-Token');

        if ($bearer && strpos($bearer, 'Bearer ') === 0) $bearer = substr($bearer, 7);
        $bearer = $bearer ? trim($bearer, " \t\n\r\0\x0B\"'<>") : null;

        if (!$bearer) {
            Log::warning('API.TOKEN missing', ['path' => $request->path(), 'ip' => $request->ip()]);
            return response()->json(['message' => 'Missing token'], 401);
        }

        // 2) หา token (เก็บ plaintext)
        $token = ApiToken::where('token', $bearer)->first();
        if (!$token) {
            Log::warning('API.TOKEN invalid', [
                'prefix' => substr($bearer, 0, 6),
                'path'   => $request->path(),
                'ip'     => $request->ip(),
            ]);
            return response()->json(['message' => 'Token invalid'], 401);
        }

        // 3) เช็คหมดอายุ / revoke
        if ($token->expires_at && $token->expires_at->isPast()) {
            Log::warning('API.TOKEN expired', ['id' => $token->id, 'exp' => $token->expires_at]);
            return response()->json(['message' => 'Token expired'], 401);
        }
        if (isset($token->revoked) && $token->revoked) {
            Log::warning('API.TOKEN revoked', ['id' => $token->id]);
            return response()->json(['message' => 'Token revoked'], 401);
        }

        // 4) ล็อก token ให้ใช้ได้เฉพาะ site ที่กำหนด
        // วิธีระบุ site ปลายทางของคำขอ:
        // - query: ?site_id=123
        // - header: X-Site-Id: 123
        // - route param: /sites/{site_id}/...
        // - (ทางเลือก) จาก code ใน route แล้ว map เป็น id
        $requestSiteId = $request->query('site_id')
            ?? $request->header('X-Site-Id')
            ?? $request->route('site_id');

        // ถ้าไม่ได้ส่งมา แต่ token ผูก site_id ไว้ → ใช้ site จาก token แทน
        if (is_null($requestSiteId) && !is_null($token->site_id)) {
            $requestSiteId = (int) $token->site_id;
        }

        // (ทางเลือก) map จาก code -> id ถ้าเส้นทางคุณเป็น /sites/{code}/...
        // if (is_null($requestSiteId) && ($code = $request->route('code'))) {
        //     $requestSiteId = \Modules\SiteSettings\Entities\SiteSettings::where('code', $code)->value('id');
        // }

        // ถ้ามีทั้งสองฝั่ง และไม่ตรงกัน → ห้าม
        if (!is_null($token->site_id) && !is_null($requestSiteId) && (int)$token->site_id !== (int)$requestSiteId) {
            return response()->json(['message' => 'Token not allowed for this site'], 403);
        }

        // ปัก site_id ให้ Controller ใช้งานต่อได้ทันที
        if (!is_null($requestSiteId)) {
            $request->attributes->set('site_id', (int)$requestSiteId);
        }

        // 5) อัปเดตการใช้งานล่าสุด
        $token->last_used_at = now();
        $token->save();

        return $next($request);
    }
}
