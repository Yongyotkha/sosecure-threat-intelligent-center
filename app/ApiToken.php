<?php

namespace App;

use Modules\ApiKey\Entities\ApiToken as ApiTokenEntity;

class ApiToken extends ApiTokenEntity
{
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function getToken($id)
    {
        $latest = static::where('site_id', $id)
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? $latest->token : null;
    }
}
