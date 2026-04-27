<?php

namespace App\Http\Controllers;

use App\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', '2fa']);
    }

    /**
     * Generate a new API token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $name = $request->input('name', 'API Token');
        $type = $request->input('type'); // e.g., 'feed_insight', 'service_receive_api'

        // ลองอ่าน expires_at ก่อน ถ้าไม่มีลองใช้ days
        $expiresAt = $request->input('expires_at') ?: $request->input('days');

        $siteId = $request->attributes->get('site_id')
            ?? $request->input('site_id')
            ?? $request->input('site');

        // (ถ้ารับจาก <input type="datetime-local"> จะเป็นรูป 2025-09-10T14:30)
        if ($expiresAt && strpos($expiresAt, 'T') !== false) {
            $expiresAt = str_replace('T', ' ', $expiresAt);
            if (strlen($expiresAt) === 16) {
                $expiresAt .= ':00';
            }
        }

        // สร้าง unique token
        do {
            $plain = Str::random(60);
        } while (ApiToken::where('token', $plain)->exists());

        $token = ApiToken::create([
            'name'       => $name . ($expiresAt ? ' (Expires: ' . $expiresAt . ')' : ''),
            'token'      => $plain,
            'expires_at' => $expiresAt ?: null,
            'site_id'    => $siteId ?: null,
            'type'         => $type ?: null,
            'whitelist_ips'=> $request->input('whitelist_ips') ?: null,
        ]);

        return response()->json([
            'id'         => $token->id,
            'name'       => $token->name,
            'token'      => $plain,
            'expires_at' => $token->expires_at,
            'type'       => $token->type,
        ], 201);
    }

    /**
     * Get token by site_id and type
     * 
     * @param int $siteId
     * @param string|null $type
     * @return string|null
     */
    public static function getTokenBySiteAndType($siteId, $type = null)
    {
        $query = ApiToken::where('site_id', $siteId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $latest = $query->orderBy('id', 'desc')->first();

        return $latest ? $latest->token : null;
    }
}
