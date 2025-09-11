<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
// เลือก use ให้ตรงกับโมเดลจริง
use App\Models\ApiToken;   // ถ้าโมเดลอยู่ใน app/Models
// use App\ApiToken;      // ถ้าโมเดลอยู่ใน app/

class TokenController extends Controller
{
    public function generate(Request $request)
    {
        $name = $request->input('name', 'Feed Token');
        $days = (int) $request->input('days', 30);

        // gen plaintext token
        do {
            $plain = Str::random(60);
        } while (ApiToken::where('token', $plain)->exists());

        $token = ApiToken::create([
            'name'       => $name,
            'token'      => $plain,
            'expires_at' => $days > 0 ? now()->addDays($days) : null,
        ]);

        return response()->json([
            'id'         => $token->id,
            'name'       => $token->name,
            'token'      => $plain, // ส่งกลับให้ใช้จริง
            'expires_at' => optional($token->expires_at)->toDateTimeString(),
        ], 201);
    }
}
