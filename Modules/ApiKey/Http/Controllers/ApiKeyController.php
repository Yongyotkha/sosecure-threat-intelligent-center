<?php



namespace Modules\ApiKey\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Http\Response;

use Illuminate\Routing\Controller;

use Illuminate\Database\QueryException;

use Illuminate\Support\Facades\DB;

use Modules\ApiKey\Entities\ApiToken;

use Modules\SiteSettings\Entities\SiteSettings;

use Yajra\DataTables\DataTables;



class ApiKeyController extends Controller

{

    protected $request;



    public function __construct(Request $request)

    {

        $this->middleware(['auth', 'verified', '2fa']);

        $this->request = $request;

    }



    public function index()

    {

        $getRoleCustom = @get_role_custom();

        $data['page'] = langapp('api_key');

        $data['SiteSettings'] = @$getRoleCustom['SiteSettings'];

        $data['systemProviders'] = ApiToken::systemProviderOptions();

        $data['siteProviders'] = ApiToken::siteProviderOptions();

        $data['siteProviderEndpoints'] = ApiToken::siteProviderEndpointUrls();

        $data['honeypotTokenType'] = ApiToken::honeypotTokenType();

        $data['generatableSiteTypes'] = ApiToken::typesWithGeneratedKeys();



        return view('apikey::index')->with($data);

    }



    public function tableData(Request $request)

    {

        $scope = $this->resolveScope($request);

        $siteId = $scope === ApiToken::SCOPE_SYSTEM ? null : $this->resolveSiteId($request);

        $allowedSiteIds = $scope === ApiToken::SCOPE_SYSTEM ? null : $this->allowedSiteIds();

        $tokens = ApiToken::getGroupedProviders($siteId, $allowedSiteIds, $scope);



        if ($request->filled('filter_name')) {

            $tokens = $tokens->filter(function ($items, $compositeKey) use ($request) {

                $groupKey = $this->extractGroupKey($compositeKey);



                return stripos($groupKey, $request->filter_name) !== false;

            });

        }



        if ($request->filled('filter_url')) {

            $tokens = $tokens->filter(function ($items) use ($request) {

                return stripos($items->first()->url ?? '', $request->filter_url) !== false;

            });

        }

        if ($request->filled('filter_expire_status') && $request->filter_expire_status !== 'all') {

            $expireStatus = $request->filter_expire_status;

            $tokens = $tokens->filter(function ($items) use ($expireStatus) {

                return $items->contains(function ($token) use ($expireStatus) {

                    return ApiToken::matchesExpireFilter($token, $expireStatus);

                });

            });

        }



        $siteNames = SiteSettings::whereIn(

            'id',

            $tokens->flatten(1)->pluck('site_id')->filter()->unique()->values()

        )->pluck('name', 'id');



        $systemProviders = ApiToken::systemProviderOptions();



        $rows = $tokens->map(function ($items, $compositeKey) use ($siteNames, $systemProviders) {

            $first = $items->first();

            $updatedAt = $items->max('updated_at');

            $groupKey = $this->extractGroupKey($compositeKey);

            $scope = ApiToken::normalizeScope($first->scope ?? ApiToken::SCOPE_SITE);

            $providerType = $first->type ?: '-';



            if ($scope === ApiToken::SCOPE_SYSTEM && isset($systemProviders[$providerType])) {

                $providerTypeLabel = $systemProviders[$providerType];

            } else {

                $providerTypeLabel = $providerType;

            }



            return (object) [

                'type' => $groupKey,

                'scope' => $scope,

                'provider_type' => $providerTypeLabel,

                'name' => $scope === ApiToken::SCOPE_SYSTEM && isset($systemProviders[$groupKey])

                    ? $systemProviders[$groupKey]

                    : $groupKey,

                'site_id' => $first->site_id,

                'site_name' => $scope === ApiToken::SCOPE_SYSTEM

                    ? 'System'

                    : ($first->site_id ? ($siteNames[$first->site_id] ?? '-') : '-'),

                'url' => $first->url ?: '-',

                'keys' => $items->values(),

                'updated_at' => $updatedAt,

            ];

        })->values();



        return DataTables::of($rows)

            ->addColumn('keys_display', function ($model) {

                if ($model->keys->isEmpty()) {

                    return '<span class="text-muted">-</span>';

                }



                $html = '<div class="api-key-list">';

                foreach ($model->keys as $key) {

                    $token = $key->token ?: '';
                    $statusClass = $key->isExpired() ? 'api-key-token-expired' : 'api-key-token-active';

                    $html .= '<div><span class="api-key-token-value api-key-token-masked ' . $statusClass . '" data-full="' . e($token) . '" title="Click to reveal">' . e(ApiToken::maskToken($token)) . '</span></div>';

                }

                $html .= '</div>';



                return $html;

            })

            ->addColumn('last_update', function ($model) {

                if (!$model->updated_at) {

                    return '-';

                }



                return $model->updated_at instanceof \DateTimeInterface

                    ? $model->updated_at->format('Y-m-d H:i:s')

                    : date('Y-m-d H:i:s', strtotime($model->updated_at));

            })

            ->addColumn('action', function ($model) {

                $siteId = $model->site_id === null ? '' : (int) $model->site_id;

                $scopeLabel = $model->scope === ApiToken::SCOPE_SYSTEM ? 'System Feed' : 'Site API Key';

                $keyCount = $model->keys->count();



                return '<button type="button" class="btn btn-sm btn-info m-xs btn-edit-api-key"'

                    . ' data-type="' . e($model->type) . '"'

                    . ' data-site-id="' . $siteId . '"'

                    . ' data-scope="' . e($model->scope) . '">'

                    . '<span><i class="fas fa-edit"></i></span>'

                    . '</button>'

                    . '<button type="button" class="btn btn-sm btn-danger m-xs btn-delete-api-key"'

                    . ' data-type="' . e($model->type) . '"'

                    . ' data-name="' . e($model->name) . '"'

                    . ' data-site-id="' . $siteId . '"'

                    . ' data-site-name="' . e($model->site_name) . '"'

                    . ' data-scope="' . e($model->scope) . '"'

                    . ' data-scope-label="' . e($scopeLabel) . '"'

                    . ' data-key-count="' . $keyCount . '">'

                    . '<span><i class="fas fa-trash-alt"></i></span>'

                    . '</button>';

            })

            ->rawColumns(['keys_display', 'action'])

            ->make(true);

    }



    public function show(Request $request, $type)

    {

        $type = rawurldecode($type);

        $scope = $this->resolveScope($request);

        $siteId = $scope === ApiToken::SCOPE_SYSTEM ? null : $this->resolveSiteId($request, true);

        $tokens = ApiToken::getProviderTokens($type, $siteId, $scope);



        if ($tokens->isEmpty()) {

            abort(404);

        }



        $first = $tokens->first();



        return response()->json([

            'type' => $type,

            'name' => $type,

            'scope' => $scope,

            'url' => $first->url,

            'site_id' => $first->site_id,

            'keys' => $tokens->map(function ($token) {

                return [

                    'id' => $token->id,

                    'name' => $token->name,

                    'key_value' => $token->token,

                    'expires_at' => ApiToken::formatTimePicker($token->expires_at),

                    'last_used_at' => ApiToken::formatDisplayDateTime($token->last_used_at),

                    'last_ip' => $token->last_ip,

                    'whitelist_ips' => $token->whitelist_ips,

                    'created_at' => ApiToken::formatDisplayDateTime($token->created_at),

                ];

            })->values(),

        ]);

    }



    public function store(Request $request)

    {

        $request->validate([

            'name' => 'required|string|max:255',

            'url' => 'nullable|string|max:500',

            'site_id' => 'nullable|integer',

            'scope' => 'nullable|in:system,site',

            'keys' => 'nullable|array',

            'keys.*.name' => 'nullable|string|max:255',

            'keys.*.key_value' => 'nullable|string|max:512',

            'keys.*.expires_at' => 'nullable|string|max:32',

            'keys.*.whitelist_ips' => 'nullable|string|max:2000',

        ]);



        $scope = $this->resolveScope($request);

        $type = trim($request->name);

        $siteId = $scope === ApiToken::SCOPE_SYSTEM

            ? null

            : ($request->filled('site_id') ? (int) $request->site_id : null);



        if ($scope === ApiToken::SCOPE_SITE && !$siteId) {

            return response()->json([

                'message' => 'Site is required for site API keys.',

                'errors' => ['site_id' => ['Site is required for site API keys.']],

            ], 422);

        }



        $keys = $request->input('keys', []);

        if (ApiToken::isHoneypotType($type)) {

            $honeypotErrors = ApiToken::validateHoneypotKeys($keys);

            if (!empty($honeypotErrors)) {

                return $this->validationErrorResponse('Honeypot Agent key validation failed.', $honeypotErrors);

            }

        }

        $keyErrors = ApiToken::validateProviderKeys($keys, $type);



        if (!empty($keyErrors)) {

            return $this->validationErrorResponse('Duplicate API key detected.', $keyErrors);

        }



        try {

            DB::transaction(function () use ($type, $request, $siteId, $scope, $keys) {

                if (ApiToken::providerExists($type, $siteId, $scope)) {

                    ApiToken::appendProviderKeys($type, $request->url, $keys, $siteId, $scope);

                } else {

                    ApiToken::syncProvider($type, $request->url, $keys, null, $siteId, $scope);

                }

            });

        } catch (QueryException $e) {

            $duplicateResponse = $this->duplicateKeyErrorResponse($e);

            if ($duplicateResponse) {

                return $duplicateResponse;

            }



            throw $e;

        }



        $message = langapp('changes_saved_successful');

        if (ApiToken::isHoneypotType($type)) {

            $message .= ' Copy the honeypot API key now if you generated a new one — only the hash is stored.';

        }



        return ajaxResponse([

            'message' => $message,

            'redirect' => route('apikey.index'),

            'honeypot' => ApiToken::isHoneypotType($type),

        ], true, Response::HTTP_OK);

    }



    public function update(Request $request, $type)

    {

        $request->validate([

            'name' => 'required|string|max:255',

            'url' => 'nullable|string|max:500',

            'site_id' => 'nullable|integer',

            'scope' => 'nullable|in:system,site',

            'keys' => 'nullable|array',

            'keys.*.name' => 'nullable|string|max:255',

            'keys.*.key_value' => 'nullable|string|max:512',

            'keys.*.expires_at' => 'nullable|string|max:32',

            'keys.*.whitelist_ips' => 'nullable|string|max:2000',

        ]);



        $type = rawurldecode($type);

        $scope = $this->resolveScope($request);

        $siteId = $scope === ApiToken::SCOPE_SYSTEM ? null : $this->resolveSiteId($request, true);



        if (ApiToken::getProviderTokens($type, $siteId, $scope)->isEmpty()) {

            abort(404);

        }



        $newType = trim($request->name);



        if ($newType !== $type && ApiToken::providerExists($newType, $siteId, $scope)) {

            return response()->json([

                'message' => 'Provider name already exists.',

                'errors' => ['name' => ['Provider name already exists.']],

            ], 422);

        }



        $keys = $request->input('keys', []);

        if (ApiToken::isHoneypotType($newType)) {

            $honeypotErrors = ApiToken::validateHoneypotKeys($keys);

            if (!empty($honeypotErrors)) {

                return $this->validationErrorResponse('Honeypot Agent key validation failed.', $honeypotErrors);

            }

        }

        $keyErrors = ApiToken::validateProviderKeys($keys, $newType);



        if (!empty($keyErrors)) {

            return $this->validationErrorResponse('Duplicate API key detected.', $keyErrors);

        }



        try {

            DB::transaction(function () use ($type, $newType, $request, $siteId, $scope, $keys) {

                ApiToken::syncProvider(

                    $newType,

                    $request->url,

                    $keys,

                    $type,

                    $siteId,

                    $scope

                );

            });

        } catch (QueryException $e) {

            $duplicateResponse = $this->duplicateKeyErrorResponse($e);

            if ($duplicateResponse) {

                return $duplicateResponse;

            }



            throw $e;

        }



        return ajaxResponse([

            'message' => langapp('changes_saved_successful'),

            'redirect' => route('apikey.index'),

        ], true, Response::HTTP_OK);

    }



    public function destroy(Request $request, $type)

    {

        $type = rawurldecode($type);

        $scope = $this->resolveScope($request);

        $siteId = $scope === ApiToken::SCOPE_SYSTEM ? null : $this->resolveSiteId($request, true);



        if (ApiToken::getProviderTokens($type, $siteId, $scope)->isEmpty()) {

            abort(404);

        }



        ApiToken::deleteProvider($type, $siteId, $scope);



        return ajaxResponse([

            'message' => langapp('deleted_successfully'),

            'redirect' => route('apikey.index'),

        ], true, Response::HTTP_OK);

    }



    protected function resolveScope(Request $request)

    {

        return ApiToken::normalizeScope($request->input('scope', ApiToken::SCOPE_SITE));

    }



    protected function resolveSiteId(Request $request, $allowEmpty = false)

    {

        if ($request->filled('site_id')) {

            return (int) $request->site_id;

        }



        if ($request->filled('sitecode')) {

            $site = SiteSettings::where('active', 1)

                ->whereNull('deleted_at')

                ->where('code', $request->sitecode)

                ->first();



            return $site ? (int) $site->id : null;

        }



        return $allowEmpty ? null : null;

    }



    protected function allowedSiteIds()

    {

        $getRoleCustom = @get_role_custom();



        if (@$getRoleCustom['superadmin'] == 1) {

            return null;

        }



        $siteIdArr = @$getRoleCustom['site_id_arr'];



        if (!$siteIdArr || $siteIdArr->isEmpty()) {

            return [];

        }



        return $siteIdArr->pluck('site_id')->map(function ($id) {

            return (int) $id;

        })->toArray();

    }



    protected function extractGroupKey($compositeKey)

    {

        $parts = explode('::', $compositeKey);



        return end($parts) ?: $compositeKey;

    }



    protected function validationErrorResponse($message, array $errors)

    {

        return response()->json([

            'message' => $message,

            'errors' => $errors,

        ], 422);

    }



    protected function duplicateKeyErrorResponse(QueryException $e)

    {

        $errorInfo = $e->errorInfo ?? [];



        if (!isset($errorInfo[1]) || (int) $errorInfo[1] !== 1062) {

            return null;

        }



        return $this->validationErrorResponse(

            'Duplicate API key detected.',

            ['keys' => ['This API key value is already registered in the system.']]

        );

    }

}


