<?php



namespace Modules\ApiKey\Entities;



use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

use Modules\SiteSettings\Entities\SiteSettings;



class ApiToken extends Model

{

    const SCOPE_SYSTEM = 'system';

    const SCOPE_SITE = 'site';



    protected $table = 'api_tokens';



    protected $fillable = [

        'name',

        'token',

        'url',

        'type',

        'site_id',

        'scope',

        'expires_at',

        'last_used_at',

        'last_ip',

        'whitelist_ips',

    ];



    protected $dates = [

        'expires_at',

        'last_used_at',

        'created_at',

        'updated_at',

    ];



    public static function systemProviderOptions()

    {

        return [

            'virustotal' => 'VirusTotal',

            'otx' => 'OTX AlienVault',

            'abuseipdb' => 'AbuseIPDB',

            'threatfox' => 'ThreatFox',

            'rstcloud' => 'RST Cloud',

            'hybrid' => 'Hybrid Analysis',

            'ibmcloud' => 'IBM X-Force',

            'urlhaus' => 'URLhaus',

            'misp' => 'MISP',

            'rest' => 'REST API',

        ];

    }



    public function site()

    {

        return $this->belongsTo(SiteSettings::class, 'site_id');

    }



    public static function normalizeScope($scope)

    {

        return $scope === self::SCOPE_SYSTEM ? self::SCOPE_SYSTEM : self::SCOPE_SITE;

    }



    public static function providerGroupKey($token)

    {

        return $token->type ?: $token->name;

    }



    public static function compositeGroupKey($token)

    {

        $scope = static::normalizeScope($token->scope ?? self::SCOPE_SITE);

        $siteKey = $scope === self::SCOPE_SYSTEM ? 'system' : ($token->site_id ?? 'global');



        return $scope . '::' . $siteKey . '::' . static::providerGroupKey($token);

    }



    public function scopeForScope($query, $scope = null)

    {

        if (!$scope) {

            return $query;

        }



        return $query->where('scope', static::normalizeScope($scope));

    }



    public function scopeForSites($query, $siteId = null, array $allowedSiteIds = null, $scope = null)

    {

        $scope = static::normalizeScope($scope ?? self::SCOPE_SITE);



        $query->where('scope', $scope);



        if ($scope === self::SCOPE_SYSTEM) {

            return $query->whereNull('site_id');

        }



        if ($siteId !== null && $siteId !== '') {

            return $query->where('site_id', $siteId);

        }



        if ($allowedSiteIds !== null) {

            return $query->whereIn('site_id', $allowedSiteIds);

        }



        return $query;

    }



    public function scopeInProviderGroup($query, $groupKey, $siteId = null, $scope = self::SCOPE_SITE)

    {

        $scope = static::normalizeScope($scope);



        $query->where('scope', $scope)->where(function ($sub) use ($groupKey) {

            $sub->where('type', $groupKey)

                ->orWhere(function ($inner) use ($groupKey) {

                    $inner->where(function ($q) {

                        $q->whereNull('type')->orWhere('type', '');

                    })->where('name', $groupKey);

                });

        });



        if ($scope === self::SCOPE_SYSTEM) {

            return $query->whereNull('site_id');

        }



        if ($siteId === null || $siteId === '') {

            return $query->whereNull('site_id');

        }



        return $query->where('site_id', $siteId);

    }



    public static function providerExists($groupKey, $siteId = null, $scope = self::SCOPE_SITE)

    {

        return static::inProviderGroup($groupKey, $siteId, $scope)->exists();

    }



    public static function getProviderTokens($groupKey, $siteId = null, $scope = self::SCOPE_SITE)

    {

        return static::inProviderGroup($groupKey, $siteId, $scope)

            ->orderBy('id', 'asc')

            ->get();

    }



    public static function getGroupedProviders($siteId = null, array $allowedSiteIds = null, $scope = null)

    {

        return static::query()

            ->forSites($siteId, $allowedSiteIds, $scope)

            ->orderBy('updated_at', 'desc')

            ->get()

            ->groupBy(function ($token) {

                return static::compositeGroupKey($token);

            });

    }



    public static function deleteProvider($groupKey, $siteId = null, $scope = self::SCOPE_SITE)

    {

        return static::inProviderGroup($groupKey, $siteId, $scope)->delete();

    }



    public static function validateProviderKeys(array $keys)

    {

        $errors = [];

        $seenTokens = [];



        foreach ($keys as $index => $keyData) {

            $value = trim($keyData['key_value'] ?? '');



            if ($value === '') {

                continue;

            }



            $keyLabel = trim($keyData['name'] ?? '') ?: ('Key #' . ($index + 1));



            if (isset($seenTokens[$value])) {

                $errors["keys.{$index}.key_value"][] = 'Duplicate API key: "' . $keyLabel . '" has the same value as "' . $seenTokens[$value] . '".';

                continue;

            }



            $seenTokens[$value] = $keyLabel;



            $id = !empty($keyData['id']) ? (int) $keyData['id'] : null;

            $query = static::where('token', $value);



            if ($id) {

                $query->where('id', '!=', $id);

            }



            $existing = $query->first();



            if ($existing) {

                $providerName = $existing->type ?: $existing->name ?: 'another provider';

                $errors["keys.{$index}.key_value"][] = 'Duplicate API key: "' . $keyLabel . '" is already registered under provider "' . $providerName . '".';

            }

        }



        return $errors;

    }



    public static function appendProviderKeys($type, $url, array $keys, $siteId = null, $scope = self::SCOPE_SITE)

    {

        $scope = static::normalizeScope($scope);

        $siteId = $scope === self::SCOPE_SYSTEM ? null : ($siteId ?: null);



        foreach ($keys as $index => $keyData) {

            $name = trim($keyData['name'] ?? '');

            $value = trim($keyData['key_value'] ?? '');



            if ($name === '' && $value === '') {

                continue;

            }



            if ($value === '') {

                continue;

            }



            if ($name === '') {

                $name = 'Key Name ' . ($index + 1);

            }



            static::create([

                'type' => $type,

                'name' => $name,

                'token' => $value,

                'url' => $url,

                'scope' => $scope,

                'site_id' => $siteId,

                'expires_at' => static::parseDateTime($keyData['expires_at'] ?? null),

                'whitelist_ips' => $scope === self::SCOPE_SYSTEM

                    ? null

                    : (trim($keyData['whitelist_ips'] ?? '') ?: null),

            ]);

        }



        if ($url !== null && $url !== '') {

            static::inProviderGroup($type, $siteId, $scope)->update(['url' => $url]);

        }

    }



    public static function syncProvider($type, $url, array $keys, $oldGroupKey = null, $siteId = null, $scope = self::SCOPE_SITE)

    {

        $scope = static::normalizeScope($scope);

        $siteId = $scope === self::SCOPE_SYSTEM ? null : ($siteId ?: null);

        $groupKey = $oldGroupKey ?: $type;

        $existing = $oldGroupKey

            ? static::inProviderGroup($groupKey, $siteId, $scope)->get()->keyBy('id')

            : collect();



        $keptIds = [];



        foreach ($keys as $index => $keyData) {

            $name = trim($keyData['name'] ?? '');

            $value = trim($keyData['key_value'] ?? '');



            if ($name === '' && $value === '') {

                continue;

            }



            if ($name === '') {

                $name = 'Key Name ' . ($index + 1);

            }



            if ($value === '') {

                continue;

            }



            $payload = [

                'type' => $type,

                'name' => $name,

                'token' => $value,

                'url' => $url,

                'scope' => $scope,

                'site_id' => $siteId,

                'expires_at' => static::parseDateTime($keyData['expires_at'] ?? null),

                'whitelist_ips' => $scope === self::SCOPE_SYSTEM
                    ? null
                    : (trim($keyData['whitelist_ips'] ?? '') ?: null),

            ];



            if (!empty($keyData['id']) && $existing->has($keyData['id'])) {

                $token = $existing->get($keyData['id']);

                $token->update($payload);

                $keptIds[] = $token->id;

                continue;

            }



            $token = static::create($payload);

            $keptIds[] = $token->id;

        }



        if ($oldGroupKey) {

            $query = static::inProviderGroup($groupKey, $siteId, $scope);



            if (!empty($keptIds)) {

                $query->whereNotIn('id', $keptIds)->delete();

            } else {

                $query->delete();

            }

        }

    }



    public static function scopeLabel($scope)

    {

        return static::normalizeScope($scope) === self::SCOPE_SYSTEM ? 'System' : 'Site';

    }



    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function matchesExpireFilter($token, $status)
    {
        if (!$status || $status === 'all') {
            return true;
        }

        $hasExpiration = (bool) $token->expires_at;

        switch ($status) {
            case 'expired':
                return $token->isExpired();
            case 'active':
                return !$token->isExpired();
            case 'no_expiration':
                return !$hasExpiration;
            default:
                return true;
        }
    }

    public static function maskToken($token)

    {

        if (empty($token)) {

            return '-';

        }



        $length = strlen($token);



        if ($length <= 5) {

            return $token;

        }



        return str_repeat('*', $length - 5) . substr($token, -5);

    }



    public static function parseDateTime($value)

    {

        if (empty($value)) {

            return null;

        }



        if (strpos($value, 'T') !== false) {

            $value = str_replace('T', ' ', $value);

            if (strlen($value) === 16) {

                $value .= ':00';

            }

        }



        try {

            return Carbon::parse($value);

        } catch (\Exception $e) {

            return null;

        }

    }



    public static function formatTimePicker($value)
    {
        if (!$value) {
            return '';
        }

        return timePickerFormat($value instanceof \DateTimeInterface ? $value : Carbon::parse($value));
    }

    public static function formatDateTimeLocal($value)
    {
        return static::formatTimePicker($value);
    }



    public static function formatDisplayDateTime($value)

    {

        if (!$value) {

            return '';

        }



        return $value instanceof \DateTimeInterface

            ? $value->format('Y-m-d H:i:s')

            : Carbon::parse($value)->format('Y-m-d H:i:s');

    }

}


