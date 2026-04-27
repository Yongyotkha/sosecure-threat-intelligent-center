<?php

namespace App;

use Cartalyst\Stripe\Api\Api;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = ['name', 'token', 'expires_at', 'last_used_at', 'last_ip', 'whitelist_ips', 'site_id', 'type'];

    protected $dates = ['expires_at', 'last_used_at', 'created_at', 'updated_at']; // รองรับ Laravel เก่า

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    public static function getToken($id)
    {
        $latest = ApiToken::where('site_id', $id)
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? $latest->token : null;
    }
}
