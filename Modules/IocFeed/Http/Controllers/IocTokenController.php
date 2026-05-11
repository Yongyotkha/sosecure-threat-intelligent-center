<?php

namespace Modules\IocFeed\Http\Controllers;

use App\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class IocTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', '2fa']);
    }

    /**
     * List tokens for IoC Feed
     */
    public function index()
    {
        $tokens = ApiToken::where('type', 'ioc_feed')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($tokens);
    }

    /**
     * Generate a new token
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Generate unique token
        do {
            $plain = Str::random(60);
        } while (ApiToken::where('token', $plain)->exists());

        $token = ApiToken::create([
            'name'       => $request->name,
            'token'      => $plain,
            'type'       => 'ioc_feed',
            'expires_at' => null, // Continuous access for feeds
            'site_id'    => $request->site_id ?? null,
        ]);

        return response()->json([
            'message' => 'Token generated successfully',
            'token'   => $plain,
            'data'    => $token
        ]);
    }

    /**
     * Remove the specified token
     */
    public function destroy($id)
    {
        $token = ApiToken::where('type', 'ioc_feed')->findOrFail($id);
        $token->delete();

        return response()->json(['message' => 'Token deleted successfully']);
    }
}
