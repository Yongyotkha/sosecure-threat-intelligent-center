<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bookmark;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\R_s_s_news;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\OSType;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;

class ApiCVEController extends ApiController
{
    public function vulnerabilitys_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);



            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $input = $data['data'] ?? [];
                    $request->merge($input);
                    $search = $input['search'] ?? null;
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $keywords = $input['keywords'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $check = $input['check'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $level = $input['level'] ?? null;
                    $column = $input['column'] ?? null;
                    $dir = $input['dir'] ?? null;
                    $group = $input['group'] ?? null;
                    $fix = $input['fix'] ?? null;

                    [$hasDateFilter, $date_start_date_format, $date_end_date_format] = $this->resolvePublishedDateRange($input);
                    $date_start = $date_start_date_format;
                    $date_end = $date_end_date_format;
                    $date_start_datetime_format = $date_start_date_format;
                    $date_end_datetime_format = $date_end_date_format;

                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    $site = $siteScope['site_id'];
                    $site_id_arr = $siteScope['site_id_arr'];
                    $is_superadmin = $siteScope['is_superadmin'];
                    $isFixFilter = $this->isFixFilterValue($check);

                    // Extract additional fields for By Host filters
                    $vendor = $input['vendor'] ?? null;
                    $title = $input['title'] ?? null;
                    $version = $input['version'] ?? null;
                    $edition = $input['edition'] ?? null;

                    $model = '';
                    $html = '';

                    $site_id = null;


                    if ($search == 1) {
                        $model = CVEMapping::with('get_site')->with('get_cve_asset')
                            ->select('data_datacve_mapping.*')->distinct();

                        $model = $model->whereExists(function ($q) use ($isFixFilter, $site, $site_id_arr, $is_superadmin) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets')
                                ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                                ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                ->where('cve_assets.active', 1)
                                ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                            if ($site) {
                                $q->where('data_datacve_mapping_assets.site_id', $site);
                            } elseif (!$is_superadmin && !empty($site_id_arr)) {
                                $q->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                            }
                        });

                        $CVEMappingAssets_data = CVEMappingAssets::select('namecve');

                        if (!$is_superadmin && !empty($site_id_arr)) {
                            $model = $model->whereExists(function ($q) use ($site_id_arr, $isFixFilter, $site) {
                                $q->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets')
                                    ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                    ->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr)
                                    ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                                if ($site) {
                                    $q->where('data_datacve_mapping_assets.site_id', $site);
                                }
                            });
                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.site_id', '=', $site_id);
                        } elseif (!$is_superadmin && !empty($site_id_arr)) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        if ($this->hasAssetsFilter($assets)) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data
                                ->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')
                                ->where('cve_assets.title', '=', $assets);

                            $assetsFilter = is_array($assets) ? $assets : [$assets];
                            $model = $model->whereExists(function ($q) use ($assetsFilter, $isFixFilter, $site, $site_id_arr, $is_superadmin) {
                                $q->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets as ma_assets')
                                    ->join('cve_assets as ca_assets', 'ma_assets.cve_asset_id', '=', 'ca_assets.id')
                                    ->whereColumn('ma_assets.namecve', 'data_datacve_mapping.namecve')
                                    ->whereIn('ca_assets.title', $assetsFilter)
                                    ->where('ca_assets.active', 1)
                                    ->where('ma_assets.is_fix', $isFixFilter);

                                if ($site) {
                                    $q->where('ma_assets.site_id', $site);
                                } elseif (!$is_superadmin && !empty($site_id_arr)) {
                                    $q->whereIn('ma_assets.site_id', $site_id_arr);
                                }
                            });
                        }

                        if ($keywords) {
                            $model = $model->where('data_datacve_mapping.namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($hasDateFilter) {
                            $model = $model->whereBetween('published', [$date_start_date_format, $date_end_date_format]);
                        }

                        if ($check == 1) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 1);
                        } else {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                        }

                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }

                    } else {
                        $model = CVEMapping::with('get_site')->with('get_cve_asset')
                            ->select('data_datacve_mapping.*')->distinct();

                        $model = $model->whereExists(function ($q) use ($isFixFilter, $site, $site_id_arr, $is_superadmin) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets')
                                ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                                ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                ->where('cve_assets.active', 1)
                                ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                            if ($site) {
                                $q->where('data_datacve_mapping_assets.site_id', $site);
                            } elseif (!$is_superadmin && !empty($site_id_arr)) {
                                $q->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                            }
                        });

                        $CVEMappingAssets_data = CVEMappingAssets::select('namecve');

                        if (!$is_superadmin && !empty($site_id_arr)) {
                            $model = $model->whereExists(function ($q) use ($site_id_arr, $isFixFilter, $site) {
                                $q->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets')
                                    ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                    ->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr)
                                    ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                                if ($site) {
                                    $q->where('data_datacve_mapping_assets.site_id', $site);
                                }
                            });
                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.site_id', '=', $site_id);
                        } elseif (!$is_superadmin && !empty($site_id_arr)) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', $isFixFilter);
                    }



                    if ($level) {
                        if (in_array($level, ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'])) {
                            $model = $model->where('severity', $level);
                        } elseif ($level === 'NONE') {
                            $model = $model->where(function ($query) {
                                $query->where('severity', 'NONE')->orWhere('severity', '');
                            });
                        }

                    }

                    $count_model = $model->count();

                    if ($count_model != 0) {
                        $id = '';
                        $actor_id = array();

                        $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
                        $client = new MongoClient($DB_MONGO_KEY);
                        if (app()->environment('local')) {
                            $collection_actor = $client->sosecure_threatintelligent->fx_otx_adversaries;
                            $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                        } else {
                            $collection_actor = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                            $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                        }
                    }



                    if (empty($site)) {

                        $CVEMappingAssets_data = CVEMappingAssets::query()
                            ->select(
                                'z.vendor',
                                'z.title as namecve',
                                'z.version',
                                'z.edition',
                                DB::raw('GROUP_CONCAT(DISTINCT s.name SEPARATOR ", ") as site_names'),
                                DB::raw('GROUP_CONCAT(DISTINCT z.IP SEPARATOR ", ") as IPs'),
                                DB::raw('GROUP_CONCAT(DISTINCT z.Hostname SEPARATOR ", ") as Hostnames'),
                                DB::raw('GROUP_CONCAT(DISTINCT z.id SEPARATOR ",") as asset_ids')
                            )
                            ->join('cve_assets as z', 'data_datacve_mapping_assets.cve_asset_id', '=', 'z.id')
                            ->leftJoin('site as s', 'data_datacve_mapping_assets.site_id', '=', 's.id')
                            ->where('data_datacve_mapping_assets.is_fix', 0)
                            ->groupBy('z.vendor', 'z.title', 'z.version', 'z.edition');
                    } else {


                        $CVEMappingAssets_data = CVEMappingAssets::distinct()->select(
                            'z.vendor',
                            'z.title as namecve',
                            'z.version',
                            'z.edition',
                            DB::raw("(select GROUP_CONCAT(a.IP) from (SELECT distinct  c.IP  FROM fx_cve_assets as c where  c.vendor=fx_z.vendor and c.title = fx_z.title and c.version = fx_z.version and c.edition = fx_z.edition and c.site_id = fx_z.site_id) as a ) as IP"),
                            DB::raw("(select GROUP_CONCAT(a.Hostname) from (SELECT distinct  c.Hostname  FROM fx_cve_assets as c where  c.vendor=fx_z.vendor and c.title = fx_z.title and c.version = fx_z.version and c.edition = fx_z.edition and c.site_id = fx_z.site_id) as a ) as Hostname"),
                            DB::raw("(select GROUP_CONCAT(a.id) from (SELECT distinct  c.id  FROM fx_cve_assets as c where  c.vendor=fx_z.vendor and c.title = fx_z.title and c.version = fx_z.version and c.edition = fx_z.edition and c.site_id = fx_z.site_id) as a ) as id"),

                        )
                            ->Join('cve_assets as z', 'data_datacve_mapping_assets.cve_asset_id', '=', 'z.id')
                            //   ->where('data_datacve_mapping_assets.namecve', $request->cvename)
                            ->where('data_datacve_mapping_assets.site_id', $site);
                    }

                    if ($assets) {
                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('z.title', '=', $assets);
                    }

                    if ($check == 1) {
                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', $check);
                    } else if ($check == 2) {
                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                    } else {
                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                    }

                    //$group = 1;
                    if ($group == 2) {
                        $siteId = $site;
                        $prefix = DB::getTablePrefix();

                        $CVEMappingAssets_data = CVEMappingAssets::query()
                            ->select(
                                DB::raw('GROUP_CONCAT(DISTINCT ' . $prefix . 'site.name SEPARATOR ", ") as site_names'),
                                'cve_assets.vendor',
                                'cve_assets.title',
                                'cve_assets.version',
                                'cve_assets.edition',
                                'cve_assets.Hostname',
                                DB::raw('GROUP_CONCAT(DISTINCT ' . $prefix . 'cve_assets.IP SEPARATOR ", ") as IPs'),
                                DB::raw('GROUP_CONCAT(DISTINCT ' . $prefix . 'cve_assets.Hostname SEPARATOR ", ") as Hostnames'),
                                DB::raw('GROUP_CONCAT(DISTINCT ' . $prefix . 'cve_assets.id SEPARATOR ",") as asset_ids'),

                                DB::raw('GROUP_CONCAT(
                                            DISTINCT CONCAT_WS(" | ",
                                                ' . $prefix . 'cve_assets.vendor,
                                                ' . $prefix . 'cve_assets.title,
                                                ' . $prefix . 'cve_assets.version,
                                                ' . $prefix . 'cve_assets.edition
                                            )
                                        SEPARATOR "<br>") as cpe_list')
                            )
                            ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                            ->join('data_datacve_mapping', 'data_datacve_mapping_assets.namecve', '=', 'data_datacve_mapping.namecve')
                            ->leftJoin('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id')
                            ->where('cve_assets.active', 1)
                            ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                            $CVEMappingAssets_data
                            ->when(!empty($site), fn($q) => $q->where('data_datacve_mapping_assets.site_id', $site))
                            ->when(empty($site) && !$is_superadmin && !empty($site_id_arr), fn($q) => $q->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr))
                            ->when(!empty($vendor), fn($q) => $q->where('cve_assets.vendor', $vendor))
                            ->when(!empty($title), fn($q) => $q->where('cve_assets.title', $title))
                            ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                            ->when(!empty($version) && $version !== '*', fn($q) => $q->where('cve_assets.version', $version))
                            ->when(!empty($edition) && $edition !== '*', fn($q) => $q->where('cve_assets.edition', $edition))
                            ->when(
                                !empty($keywords),
                                fn($q) =>
                                $q->where(function ($sub) use ($keywords) {
                                    $kw = '%' . trim($keywords) . '%';
                                    $sub->where('cve_assets.title', 'like', $kw)
                                        ->orWhere('cve_assets.vendor', 'like', $kw)
                                        ->orWhere('cve_assets.Hostname', 'like', $kw)
                                        ->orWhere('cve_assets.IP', 'like', $kw)
                                        ->orWhere('data_datacve_mapping_assets.namecve', 'like', $kw)
                                        ->orWhere('data_datacve_mapping.description', 'like', $kw);
                                })
                            )
                            ->when(!empty($request->level), fn($q) => $q->where('data_datacve_mapping.severity', $request->level))
                            ->when(
                                $hasDateFilter,
                                fn($q) => $q->whereBetween('data_datacve_mapping.published', [$date_start_date_format, $date_end_date_format])
                            );

                            $CVEMappingAssets_data->groupBy(
                                'cve_assets.Hostname',
                            );



                            $result = DataTables::of($CVEMappingAssets_data)
                                ->editColumn('chk', fn() => "")
                                ->addColumn(
                                    'name_cve',
                                    fn($m) => '<span class="d-inline">' . e($m->title) . '</span>'
                                )
                                ->addColumn('description', function ($m) {
                                    $hostname = !empty($m->Hostnames) ? $m->Hostnames : '-';
                                    $site = !empty($m->site_names) ? $m->site_names : '-';
                                    $cpe = !empty($m->cpe_list) ? $m->cpe_list : '-';
                                    $ips = !empty($m->IPs) ? $m->IPs : '-';
                                    return "
                                        <div style='color:#3869d4;font-weight:800;font-size:14px'>
                                            {$hostname}
                                        </div>
                                        <div class='text-muted small'>
                                            <strong>Site:</strong> {$site}<br>
                                        </div>
                                    ";
                                })
                                ->addColumn('cvss_severity', fn() => '')
                                ->addColumn('transaction', fn() => '')
                                ->addColumn('vendor_text', fn($m) => $m->vendor ?? '-')
                                ->addColumn('title_text', fn($m) => $m->title ?? '-')
                                ->addColumn('version_text', fn($m) => $m->version ?? '-')
                                ->addColumn('edition_text', fn($m) => $m->edition ?? '-')
                                ->addColumn('hostname_text', fn($m) => $m->Hostname ?? '-')
                                ->rawColumns(['name_cve', 'description'])
                                ->toJson();


                        $data_transcation = json_encode($result);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                        
                    }else if($group == 1){

                        $prefix = DB::getTablePrefix();
                        $siteId = $site;

                        $CVEMappingAssets_data = \Modules\MonitoringVulnerabilitys\Entities\CVEMapping::query()
                            ->select(
                                DB::raw("{$prefix}data_datacve_mapping.namecve"),
                                DB::raw("COUNT(DISTINCT {$prefix}data_datacve_mapping_assets.site_id) as site_count"),
                                DB::raw("GROUP_CONCAT(DISTINCT {$prefix}site.name SEPARATOR ', ') as site_names"),
                                DB::raw("MAX({$prefix}data_datacve_mapping.published) as published"),
                                DB::raw("MAX({$prefix}data_datacve_mapping.modified) as modified"),
                                DB::raw("MAX({$prefix}data_datacve_mapping.cvss_score) as cvss_score"),
                                DB::raw("MAX({$prefix}data_datacve_mapping.severity) as severity")
                            )
                            ->join('data_datacve_mapping_assets', 'data_datacve_mapping_assets.namecve', '=', 'data_datacve_mapping.namecve')
                            ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                            ->leftJoin('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id')
                            ->where('cve_assets.active', 1)
                            ->where('data_datacve_mapping_assets.is_fix', $isFixFilter);

                        // ✅ ฟิลเตอร์ทั้งหมด
                        $CVEMappingAssets_data
                            ->when(!empty($siteId), fn($q) => $q->where('data_datacve_mapping_assets.site_id', $siteId))
                            ->when(empty($siteId) && !$is_superadmin && !empty($site_id_arr), fn($q) => $q->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr))
                            ->when(!empty($vendor), fn($q) => $q->where('data_datacve_mapping_assets.vendor', $vendor))
                            ->when(!empty($title), fn($q) => $q->where('data_datacve_mapping_assets.title', $title))
                            ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                            ->when(
                                !empty($keywords),
                                fn($q) =>
                                $q->where(function ($sub) use ($keywords) {
                                    $kw = '%' . trim($keywords) . '%';
                                    $sub->where('data_datacve_mapping_assets.namecve', 'like', $kw)
                                        ->orWhere('data_datacve_mapping.description', 'like', $kw);
                                })
                            )
                            ->when(!empty($level), fn($q) => $q->where('data_datacve_mapping.severity', $level))
                            ->when(
                                $hasDateFilter,
                                fn($q) => $q->whereBetween('data_datacve_mapping.published', [$date_start_date_format, $date_end_date_format])
                            );

                        $CVEMappingAssets_data->groupBy('data_datacve_mapping.namecve');
                        $cveSources = DB::table('data_cve_sources')
                            ->select('namecve', DB::raw("GROUP_CONCAT(source SEPARATOR ', ') as src"))
                            ->groupBy('namecve')
                            ->pluck('src', 'namecve');

                        $CVEMappingAssets_data
                            ->orderBy('data_datacve_mapping.published', 'DESC')
                            ->groupBy('data_datacve_mapping.namecve');

                        $result = DataTables::of($CVEMappingAssets_data)
                            ->editColumn('chk', fn() => "")
                            ->addColumn('description', function ($m) use ($cveSources) {

                                $badge = get_CVSS_Severity_status($m->cvss_score ?? '-', $m->severity ?: 'NONE', 'badg');

                                $siteNames = $m->site_names ?: '-';
                                $published = $m->published ?: '-';
                                $modified  = $m->modified ?: '-';

                                $namecve = e($m->namecve);
                                $srcRaw = $cveSources[$m->namecve] ?? null;
                                $srcText = '(Local Data)';

                                if (!empty($srcRaw)) {
                                    $arr = array_map('trim', explode(',', $srcRaw));
                                    $final = [];

                                    foreach ($arr as $item) {
                                        $lower = strtolower($item);

                                        if ($lower === 'online') {
                                            $final[] = 'Online feed';
                                        } else {
                                            if (!in_array('Local Data', $final)) {
                                                $final[] = 'Local Data';
                                            }
                                        }
                                    }

                                    $srcText = '(' . implode(', ', $final) . ')';
                                }

                                return "
                                    <div style='color:#3869d4;'>
                                        {$badge}
                                        <span style='margin-left:8px;font-weight:800'>{$namecve} </span>{$srcText}
                                    </div>
                                    <div class='text-muted small'>
                                        <strong>Site:</strong> {$siteNames}<br>
                                        <strong>Published:</strong> {$published}<br>
                                        <strong>Modified:</strong> {$modified}
                                    </div>
                                ";
                            })
                            ->rawColumns(['description'])
                            ->toJson();
                        //$data_transcation = json_encode(['original'=>['data'=>['group'=>$group]]]);
                        $data_transcation = json_encode($result);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }else if($group==3){

                        $prefix = DB::getTablePrefix();
                        $siteId = $site;

                        // 1. ค้นหา Asset ID ที่มี CVE ตรงกับ Keyword (Query หรือ)
                        $assetIdsFromCVE = [];
                        if (!empty($keywords)) {
                            $kw = '%' . trim($keywords) . '%';
                            $assetIdsFromCVE = DB::table('data_datacve_mapping_assets')
                                ->join('data_datacve_mapping', 'data_datacve_mapping_assets.namecve', '=', 'data_datacve_mapping.namecve')
                                ->where('data_datacve_mapping_assets.namecve', 'like', $kw)
                                ->when(!empty($siteId), fn($q) => $q->where('data_datacve_mapping_assets.site_id', $siteId))
                                ->when(empty($siteId) && !$is_superadmin && !empty($site_id_arr), fn($q) => $q->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr))
                                ->when($hasDateFilter, function ($q) use ($date_start_date_format, $date_end_date_format) {
                                    $q->whereBetween('data_datacve_mapping.published', [$date_start_date_format, $date_end_date_format]);
                                })
                                ->when($this->isCheckFilterProvided($check), function ($q) use ($check) {
                                    $this->applyIsFixConstraint($q, $check, 'data_datacve_mapping_assets.is_fix');
                                }, function ($q) {
                                    $q->where('data_datacve_mapping_assets.is_fix', 0);
                                })
                                ->when(!empty($level), fn($q) => $q->where('data_datacve_mapping.severity', strtoupper($level)))
                                ->pluck('data_datacve_mapping_assets.cve_asset_id')
                                ->toArray();
                        }

                        $ByIPs = DB::table('cve_assets')
                            ->select(
                                DB::raw("{$prefix}cve_assets.IP AS ip"),
                                DB::raw("COALESCE(NULLIF(TRIM({$prefix}cve_assets.Hostname), ''), '-') AS Hostnames"),
                                DB::raw("COALESCE({$prefix}site.name, '-') AS site_names"),

                                DB::raw("
                                    GROUP_CONCAT(
                                        DISTINCT CONCAT(
                                            {$prefix}cve_assets.vendor, ' | ',
                                            {$prefix}cve_assets.title, ' | ',
                                            COALESCE({$prefix}cve_assets.version, '-'), ' | ',
                                            COALESCE({$prefix}cve_assets.edition, '-')
                                        )
                                        SEPARATOR ';;'
                                    ) AS cpe_list
                                ")
                            )
                            ->leftJoin('site', 'cve_assets.site_id', '=', 'site.id');

                        $ByIPs
                            ->where('cve_assets.active', 1) // ✅ Active Assets Only
                            ->when(!empty($siteId), fn($q) => $q->where('cve_assets.site_id', $siteId))
                            ->when(empty($siteId) && !$is_superadmin && !empty($site_id_arr), fn($q) => $q->whereIn('cve_assets.site_id', $site_id_arr))
                            ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                            ->when(
                                !empty($keywords),
                                fn($q) =>
                                $q->where(function ($sub) use ($keywords, $assetIdsFromCVE) {
                                    $kw = '%' . trim($keywords) . '%';
                                    $sub->where('cve_assets.IP', 'like', $kw)
                                        ->orWhere('cve_assets.Hostname', 'like', $kw);

                                    // ถ้ามี Asset ID ที่เจอจาก namecve ให้เอามา OR ด้วย
                                    if (!empty($assetIdsFromCVE)) {
                                        $sub->orWhereIn('cve_assets.id', $assetIdsFromCVE);
                                    }
                                })
                            )
                            // ✅ ALWAYS apply base CVE filters (is_fix, site_id, level)
                            // Date filter is CONDITIONAL inside the whereExists
                            ->whereExists(function ($sub) use ($date_start_date_format, $date_end_date_format, $siteId, $check, $level, $assets, $site_id_arr, $is_superadmin, $hasDateFilter) {
                                $sub->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets')
                                    ->join('data_datacve_mapping', 'data_datacve_mapping_assets.namecve', '=', 'data_datacve_mapping.namecve')
                                    ->whereColumn('data_datacve_mapping_assets.cve_asset_id', 'cve_assets.id');

                                if ($hasDateFilter) {
                                    $sub->whereBetween('data_datacve_mapping.published', [$date_start_date_format, $date_end_date_format]);
                                }

                                // ✅ ALWAYS: Filter by is_fix (status)
                                $this->applyIsFixConstraint($sub, $check, 'data_datacve_mapping_assets.is_fix');

                                // ✅ ALWAYS: Filter by Site ID
                                if (!empty($siteId)) {
                                    $sub->where('data_datacve_mapping_assets.site_id', $siteId);
                                } elseif (!$is_superadmin && !empty($site_id_arr)) {
                                    $sub->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                                }
                                
                                // ✅ ALWAYS: Filter by Level (Severity)
                                if (!empty($level)) {
                                    $sub->where('data_datacve_mapping.severity', strtoupper($level));
                                }
                                
                                // ✅ ALWAYS: Filter by Assets (Product/Title)
                                if (!empty($assets)) {
                                    $assetsFilter = is_array($assets) ? $assets : [$assets];
                                    $sub->join('cve_assets as ca_filter', 'data_datacve_mapping_assets.cve_asset_id', '=', 'ca_filter.id')
                                        ->whereIn('ca_filter.title', $assetsFilter);
                                }
                            });

                        // ⭐ group by IP + Hostname (แยก row สำหรับแต่ละ hostname)
                        $ByIPs->groupBy('cve_assets.IP', 'cve_assets.Hostname');

                        // Log::info('SQL Group 3 Debug: ' . $ByIPs->toSql(), $ByIPs->getBindings());
                        //Log::info('Group 3 Request:', $request->only(['isDateSearch', 'startDate', 'endDate', 'assets', 'site', 'check', 'level']));
                        // Log::info('Group 3 Request:', [
                        //     'isDateSearch' => $isDateSearch,
                        //     'startDate' => $date_start,
                        //     'endDate' => $date_end,
                        //     'assets' => $assets,
                        //     'site' => $site,
                        //     'check' => $check,
                        //     'level' => $level,
                        // ]);

                        $result = DataTables::of($ByIPs)
                            ->editColumn('description', function ($m) {

                                // 🔥 แปลง CPE list เป็น array
                                $cpeItems = $m->cpe_list
                                    ? explode(';;', $m->cpe_list)
                                    : [];

                                // ทำเป็น inline list คั่นด้วยช่องว่าง
                                $cpeHtml = '';
                                foreach ($cpeItems as $index => $cpe) {
                                    if ($index > 0) {
                                        $cpeHtml .= ' '; // เพิ่มช่องว่างระหว่างรายการ
                                    }
                                    $cpeHtml .= "- {$cpe}";
                                }

                                return "
                                    <div style='color:#3869d4;'>
                                        <span style='font-weight:800'>IP: {$m->ip}</span>
                                    </div>

                                    <div class='text-muted small'>
                                        <strong>Hostname:</strong> {$m->Hostnames}<br>
                                        <strong>CPE:</strong>{$cpeHtml}<br>
                                        <strong>Site:</strong> {$m->site_names}<br>
                                    </div>
                                ";
                            })
                            ->rawColumns(['description'])
                            ->toJson();

                        $data_transcation = json_encode($result);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }
       

                   
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_index(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $input = $data['data'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $site_id_arr = $siteScope['site_id_arr'];
                    $scopedSiteId = $siteScope['site_id'];
                    $is_superadmin = $siteScope['is_superadmin'];
                    $response['page'] = langapp('vulnerabilitys');

                    $countAssetsQuery = CVEAssets::where('active', '=', 1);
                    $countMappingQuery = CVEMapping::query();
                    $countIsFixQuery = CVEMappingAssets::where('is_fix', '=', 1);

                    if ($scopedSiteId) {
                        $countAssetsQuery->where('site_id', $scopedSiteId);
                        $mappingNames = CVEMappingAssets::where('site_id', $scopedSiteId)->select('namecve');
                        $countMappingQuery->whereIn('namecve', $mappingNames);
                        $countIsFixQuery->where('data_datacve_mapping_assets.site_id', $scopedSiteId);
                    } elseif (!$is_superadmin && !empty($site_id_arr)) {
                        $countAssetsQuery->whereIn('site_id', $site_id_arr);
                        $mappingNames = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve');
                        $countMappingQuery->whereIn('namecve', $mappingNames);
                        $countIsFixQuery->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                    }

                    $response['count_CVEAssets'] = $countAssetsQuery->count();
                    $response['count_CVEMapping'] = $countMappingQuery->count();
                    $response['count_isFix'] = $countIsFixQuery->count();

                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    if ($is_superadmin) {
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->select('title', 'site_id')->distinct()->orderBy('title')->get();
                    } else if (!empty($site_id_arr)) {
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->select('title', 'site_id')->distinct()->orderBy('title')->get();
                    } else if ($scopedSiteId) {
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->where('site_id', $scopedSiteId)->select('title', 'site_id')->distinct()->orderBy('title')->get();
                    } else {
                        $response['cve_assets'] = collect();
                    }

                    $response['site_settings'] = $SiteSettings;

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_load_cve(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            //return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $get_role_custom_first = @get_role_custom();
                    // $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    // $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    // $data_transcation = json_encode(['original'=>['data'=>[
                    //     'auth_site'=>$auth_site,
                    //     'get_role_custom_first'=>$get_role_custom_first,
                    //     'SiteSettings'=>$SiteSettings,
                    //     'site_id_arr'=>$site_id_arr
                    // ]]]);
                    // $data_transcation = json_encode($result);
                    // $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    // return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);


                    $input = $data['data'] ?? [];
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    $site = $siteScope['site_id'];
                    $site_id_arr = $siteScope['site_id_arr'];
                    $is_superadmin = $siteScope['is_superadmin'];
                    $startDate = $input['startDate'] ?? null;
                    $endDate = $input['endDate'] ?? null;
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $check = $input['check'] ?? null;
                    $level = $input['level'] ?? null;
                    $hostname = $input['hostname'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $siteId = $site;
                    $assetLimit = null;
                    [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
                    // ===== BASE QUERIES =====
                    $CVEAssets = CVEAssets::query()->where('active', 1);
                    $CVEMappingAssets = CVEMappingAssets::query()
                        ->select('data_datacve_mapping_assets.*')
                        ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                        ->where('cve_assets.active', 1); // Only Active Assets

                    $CVEMapping = CVEMapping::query();

                            // ===== SITE FILTER =====
                    if ($siteId) {
                        $SiteSettings = SiteSettings::withTrashed()->find($siteId);
                        $assetLimit = $SiteSettings ? ($SiteSettings->asset_limit ?? null) : null;

                        $CVEAssets->where('site_id', $siteId);
                        $CVEMappingAssets->where('data_datacve_mapping_assets.site_id', $siteId);

                        $CVEMapping->whereIn('namecve', function ($q) use ($siteId) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets')
                                ->where('site_id', $siteId);
                        });
                    } elseif (!$is_superadmin && !empty($site_id_arr)) {
                        $CVEAssets->whereIn('site_id', $site_id_arr);
                        $CVEMappingAssets->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        $CVEMapping->whereIn('namecve', function ($q) use ($site_id_arr) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets')
                                ->whereIn('site_id', $site_id_arr);
                        });
                    } else {
                        $CVEMapping->whereExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets')
                                ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve');
                        });
                    }

                    // ===== ASSETS FILTER =====
                    if ($this->hasAssetsFilter($assets)) {
                        $assets = is_array($assets) ? $assets : [$assets];

                        $CVEAssets->whereIn('title', $assets);

                        // Access cve_assets columns directly due to join
                        $CVEMappingAssets->whereIn('cve_assets.title', $assets);

                        $CVEMapping->whereIn('namecve', function ($q) use ($assets, $siteId, $site_id_arr, $is_superadmin) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets as ma')
                                ->join('cve_assets as a', 'a.id', '=', 'ma.cve_asset_id')
                                ->whereIn('a.title', $assets);

                            if (!empty($siteId)) {
                                $q->where('ma.site_id', $siteId);
                            } elseif (!$is_superadmin && !empty($site_id_arr)) {
                                $q->whereIn('ma.site_id', $site_id_arr);
                            }
                        });
                    }

                    // ===== HOSTNAME FILTER =====
                    if ($hostname) {
                        $hostname = $hostname;

                        $CVEAssets->where('Hostname', $hostname);
                        $CVEMappingAssets->where('cve_assets.Hostname', $hostname);

                        $CVEMapping->whereIn('namecve', function ($q) use ($hostname, $siteId) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets as ma')
                                ->join('cve_assets as a', 'a.id', '=', 'ma.cve_asset_id')
                                ->where('a.Hostname', $hostname);

                            if (!empty($siteId)) {
                                $q->where('ma.site_id', $siteId);
                            }
                        });
                    }

                    // ===== JOIN TO FILTER INVALID/ORPHAN CVEs & ALLOW FILTERING ON CVE FIELDS =====
                    $needsCveJoin = $hasDateFilter ||
                        $keywords ||
                        $level ||
                        !empty($datatype);

                    if ($needsCveJoin) {
                        $CVEMappingAssets->join('data_datacve_mapping as dm_filter', 'data_datacve_mapping_assets.namecve', '=', 'dm_filter.namecve');
                    }

                    // ===== DATE RANGE FILTER (APPLY TO CVEMapping & CVEMappingAssets) =====
                    if ($hasDateFilter) {
                        $CVEMapping->whereBetween('published', [$date_start, $date_end]);
                        if ($needsCveJoin) {
                            $CVEMappingAssets->whereBetween('dm_filter.published', [$date_start, $date_end]);
                        }
                    }

                    // ===== KEYWORDS FILTER =====
                    if ($keywords) {
                        $kw = '%' . trim($keywords) . '%';
                        $CVEMapping->where(function ($q) use ($kw) {
                            $q->where('namecve', 'like', $kw)
                                ->orWhere('description', 'like', $kw);
                        });
                        if ($needsCveJoin) {
                            $CVEMappingAssets->where(function ($q) use ($kw) {
                                $q->where('data_datacve_mapping_assets.namecve', 'like', $kw)
                                    ->orWhere('dm_filter.description', 'like', $kw);
                            });
                        }
                    }

                    // ===== SEVERITY FILTER =====
                    if ($level) {
                        $level = strtoupper($level);
                        if ($level === 'NONE') {
                            $CVEMapping->where(function ($q) {
                                $q->where('severity', 'NONE')->orWhereNull('severity');
                            });
                            if ($needsCveJoin) {
                                $CVEMappingAssets->where(function ($q) {
                                    $q->where('dm_filter.severity', 'NONE')->orWhereNull('dm_filter.severity');
                                });
                            }
                        } else {
                            $CVEMapping->where('severity', $level);
                            if ($needsCveJoin) {
                                $CVEMappingAssets->where('dm_filter.severity', $level);
                            }
                        }
                    }

                    if (!empty($datatype)) {
                        $CVEMapping->whereIn('severity', $datatype);
                        if ($needsCveJoin) {
                            $CVEMappingAssets->whereIn('dm_filter.severity', $datatype);
                        }
                    }

                    // Clone for calculating is_fix stats before applying the is_fix filters
                    $countMappingAssets = clone $CVEMappingAssets;

                    // ===== REMEDIATION FILTER (Check) =====
                    if ($check) {
                        if ($check == 1) { // Fixed
                            $CVEMappingAssets->where('data_datacve_mapping_assets.is_fix', 1);

                            $CVEMapping->whereExists(function ($q) use ($siteId) {
                                $q->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets as ma')
                                    ->whereColumn('ma.namecve', 'data_datacve_mapping.namecve')
                                    ->where('ma.is_fix', 1);

                                if ($siteId) {
                                    $q->where('ma.site_id', $siteId);
                                }
                            });
                        } elseif ($check == 2) { // Not Fixed
                            $CVEMappingAssets->where('data_datacve_mapping_assets.is_fix', 0);

                            $CVEMapping->whereExists(function ($q) use ($siteId) {
                                $q->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets as ma')
                                    ->whereColumn('ma.namecve', 'data_datacve_mapping.namecve')
                                    ->where('ma.is_fix', 0);

                            });
                        }
                    } else {
                        // Default to Not Fixed (0) if check is filter not provided
                        $CVEMappingAssets->where('data_datacve_mapping_assets.is_fix', 0);

                        $CVEMapping->whereExists(function ($q) use ($siteId) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets as ma')
                                ->whereColumn('ma.namecve', 'data_datacve_mapping.namecve')
                                ->where('ma.is_fix', 0);

                            if ($siteId) {
                                $q->where('ma.site_id', $siteId);
                            }
                        });
                    }

                    // ===== COUNTS =====
                    if (
                        $hasDateFilter ||
                        $keywords ||
                        $level ||
                        $datatype ||
                        $check ||
                        $this->hasAssetsFilter($assets)
                    ) {
                        $count_CVEAssets = (clone $CVEMappingAssets)
                            ->distinct('cve_asset_id')
                            ->count('cve_asset_id');
                    } else {
                        $count_CVEAssets = $CVEAssets->count();
                    }

                    // นับจำนวน Vulnerabilities (Unique CVEs) จาก mapping assets ที่ filter แล้ว
                    $count_CVEMapping = (clone $CVEMappingAssets)
                        ->distinct('data_datacve_mapping_assets.namecve')
                        ->count('data_datacve_mapping_assets.namecve');

                    // นับ is_fix / ทั้งหมด (Findings - ใช้ DISTINCT เพราะ JOIN กับ dm_filter อาจสร้าง duplicates)
                    $count_isFix = (clone $countMappingAssets)
                        ->where('data_datacve_mapping_assets.is_fix', 1)
                        ->distinct('data_datacve_mapping_assets.id')
                        ->count('data_datacve_mapping_assets.id');

                    $count_isFix_all = (clone $countMappingAssets)
                        ->distinct('data_datacve_mapping_assets.id')
                        ->count('data_datacve_mapping_assets.id');

                    // ===== RESPONSE =====
                    $result = [
                        'page' => langapp('vulnerabilitys'),
                        'count_CVEAssets' => number_format($count_CVEAssets),
                        'assetLimit' => $assetLimit,
                        'count_CVEMapping' => number_format($count_CVEMapping),
                        'count_isFix' => number_format($count_isFix) . '/' . number_format($count_isFix_all),
                    ];
                    $data_transcation = json_encode($result);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            try {
                $header = $request->bearerToken();
                $mode = $request->mode;
                $data_request = $request->data;
                $data = $this->dataFalse($header, $mode, $data_request);
                if ($data !== false && isset($data['site']['data']['id'])) {
                    $this->saveLog($data['site']['data']['id'], json_encode($response));
                }
            } catch (\Exception $logError) {
                // ignore logging failures
            }

            return response()->json($response);
        }
    }

    public function vulnerabilitys_load_cve_bk(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            //return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                      $get_role_custom_first = @get_role_custom();
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    $data_transcation = json_encode(['original'=>['data'=>[
                        'auth_site'=>$auth_site,
                        'get_role_custom_first'=>$get_role_custom_first,
                        'SiteSettings'=>$SiteSettings,
                        'site_id_arr'=>$site_id_arr
                    ]]]);
                    //$data_transcation = json_encode($result);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);



                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    $assets = $data['data']['assets'];

                    $model_count_CVEAssets = CVEAssets::where("active", '=', 1);
                    $model_count_CVEMapping = new CVEMapping;
                    $model_count_isFix = CVEMappingAssets::where("is_fix", '=', 1);
                    $model_count_isFix_all = new CVEMappingAssets;

   

                    if (empty($site)) {
                        $model_count_CVEAssets = $model_count_CVEAssets->whereIn('site_id', $site_id_arr);
                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $model_count_CVEMapping = $model_count_CVEMapping->whereIn('namecve', $CVEMappingAssets_name)->select('namecve')->distinct();
                        $model_count_isFix = $model_count_isFix->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        $model_count_isFix_all = $model_count_isFix_all->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                    } else {
                        $model_count_CVEAssets = $model_count_CVEAssets->where('site_id', $site);
                        $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site)->select('namecve')->get();
                        $model_count_CVEMapping = $model_count_CVEMapping->whereIn('namecve', $CVEMappingAssets_name)->select('namecve')->distinct();
                        $model_count_isFix = $model_count_isFix->where('data_datacve_mapping_assets.site_id', $site);
                        $model_count_isFix_all = $model_count_isFix_all->where('data_datacve_mapping_assets.site_id', $site);
                    }


                    if ($assets) {

                        $model_count_CVEAssets = $model_count_CVEAssets->where('title', $assets);

                        $CVEMappingAssets_name = CVEMappingAssets::select('namecve')->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')->where('cve_assets.title', '=', $assets)->get();
                        $model_count_CVEMapping = $model_count_CVEMapping->whereIn('namecve', $CVEMappingAssets_name)->select('namecve')->distinct();

                        $model_count_isFix = $model_count_isFix->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')->where('cve_assets.title', '=', $assets);

                        $model_count_isFix_all = $model_count_isFix_all->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')->where('cve_assets.title', '=', $assets);
                    }



                    $response['page'] = langapp('vulnerabilitys');
                    $response['count_CVEAssets'] = $model_count_CVEAssets->count();
                    $response['count_CVEMapping'] = $model_count_CVEMapping->count();
                    $response['count_isFix'] = $model_count_isFix->count() . '/' . $model_count_isFix_all->count();

                                                                                 $data_transcation = json_encode(['original'=>['data'=>['auth_site'=>$auth_site]]]);
                        //$data_transcation = json_encode($result);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);



                    $sitecode = $request->code;
                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $sitecode)->first();
                    if ($SiteSettingsfor) {
                        $response["assetLimit"] = $SiteSettingsfor->asset_limit;
                    }

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_count(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            //return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $startDate = $data['data']['startDate'];
                    // $endDate = $data['data']['endDate'];
                    // $count = $data['data']['count'];
                    // $site = $data['data']['site'];
                    // $get_role_custom_first = $data['data']['get_role_custom_first'];
                    // $keywords = $data['data']['keywords'];
                    // $assets = $data['data']['assets'];
                    // $isDateSearch = $data['data']['isDateSearch'];
                    // $check = $data['data']['check'];
                    // $datatype = $data['data']['datatype'];
                    // $level = $data['data']['level'];

                    $input = $data['data'] ?? [];
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
                    $count = $input['count_'] ?? $input['count'] ?? null;
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $site = $siteScope['site_id'];
                    $site_id_arr = $siteScope['site_id_arr'];
                    $is_superadmin = $siteScope['is_superadmin'];
                    $check = $input['check'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $level = $input['level'] ?? null;
               
                    $model = '';
                    $html = '';

                    if ($count == 1) {
                        $prefix = DB::getTablePrefix();

                        // Start with Maps and Join necessary tables
                        $model = CVEMapping::query()
                            ->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve')
                            ->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')
                            ->where('cve_assets.active', 1);

                        if (!$is_superadmin && !empty($site_id_arr)) {
                            $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        if ($site) {
                            $model->where('data_datacve_mapping_assets.site_id', $site);
                        }

                        if ($this->hasAssetsFilter($assets)) {
                            $model->where('cve_assets.title', $assets);
                        }

                        if ($keywords) {
                            $kw = '%' . trim($keywords) . '%';
                            $model->where(function ($q) use ($kw) {
                                $q->where('data_datacve_mapping.namecve', 'LIKE', $kw)
                                    ->orWhere('data_datacve_mapping.description', 'LIKE', $kw);
                            });
                        }

                        if ($hasDateFilter) {
                            $model->whereBetween('data_datacve_mapping.published', [$date_start, $date_end]);
                        }

                        if ($check) {
                            if ($check == 1) {
                                $model->where('data_datacve_mapping_assets.is_fix', 1);
                            } elseif ($check == 2) {
                                $model->where('data_datacve_mapping_assets.is_fix', 0);
                            }
                        } else {
                            $model->where('data_datacve_mapping_assets.is_fix', 0);
                        }

                        if ($datatype) {
                            $model->whereIn('data_datacve_mapping.severity', $datatype);
                        }

                        if ($level) {
                            $level = strtoupper($level);
                            if ($level === 'NONE') {
                                $model->where(function ($q) {
                                    $q->where('data_datacve_mapping.severity', 'NONE')
                                        ->orWhereNull('data_datacve_mapping.severity');
                                });
                            } else {
                                $model->where('data_datacve_mapping.severity', $level);
                            }
                        }

                        // --- Aggregation ---
                        $stats = $model->selectRaw("
                            COUNT(DISTINCT {$prefix}data_datacve_mapping.namecve) as total,
                            COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'HIGH' THEN {$prefix}data_datacve_mapping.namecve END) as high,
                            COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'MEDIUM' THEN {$prefix}data_datacve_mapping.namecve END) as medium,
                            COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'LOW' THEN {$prefix}data_datacve_mapping.namecve END) as low,
                            COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'CRITICAL' THEN {$prefix}data_datacve_mapping.namecve END) as critical,
                            COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'NONE' OR {$prefix}data_datacve_mapping.severity = '' OR {$prefix}data_datacve_mapping.severity IS NULL THEN {$prefix}data_datacve_mapping.namecve END) as none
                        ")->first();

                        $count = $stats->total ?? 0;
                        $high = $stats->high ?? 0;
                        $medium = $stats->medium ?? 0;
                        $low = $stats->low ?? 0;
                        $critical = $stats->critical ?? 0;
                        $none = $stats->none ?? 0;

                    } else {
                        // ===== 2. SUMMARY MODE (Optimization) =====
                        $query = DB::table('summary')
                            ->where('status', 'Y')
                            ->where('data_key_2', 'Vulnerability')
                            ->whereIn('data_text', ['Information', 'Low', 'Medium', 'High', 'Critical']);

                        if ($site) {
                            $site_code = DB::table('site')->where('id', $site)->value('code');
                            if ($site_code) {
                                $query->where('site', $site_code);
                                $summaries = $query->pluck('data_value', 'data_text');
                            } else {
                                $summaries = collect([]);
                            }
                        } else {
                            $summaries = $query->select('data_text', DB::raw('SUM(data_value) as sum_val'))
                                ->groupBy('data_text')
                                ->pluck('sum_val', 'data_text');
                        }

                        $none = intval($summaries['Information'] ?? 0);
                        $low = intval($summaries['Low'] ?? 0);
                        $medium = intval($summaries['Medium'] ?? 0);
                        $high = intval($summaries['High'] ?? 0);
                        $critical = intval($summaries['Critical'] ?? 0);

                        $count = $none + $low + $medium + $high + $critical;
                    }



                    $response = [
                        "html" => $html,
                        "count" => $count,
                        "count_high" => $high,
                        "count_medium" => $medium,
                        "count_critical" => $critical,
                        "count_low" => $low,
                        "count_none" => $none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_count_bk(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];

                    $model = '';
                    $html = '';

                    if ($count == 1) {
                        $model = CVEMapping::select('data_datacve_mapping.*')->distinct();
                        $model = $model->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve');
                        $model = $model->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id');

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }


                        if ($site) {
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }
                        if ($assets) {
                            $model = $model->where('cve_assets.title', '=', $assets);
                        }
                        if ($keywords) {
                            $model = $model->where('data_datacve_mapping.namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $date_start = $startDate;
                            $date_end = $endDate;

                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));


                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));

                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }

                        if ($check == 1) {
                            $model = $model->where('data_datacve_mapping_assets.is_fix', '=', $check);
                        }


                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }

                    } else {
                        $model = CVEMapping::select('data_datacve_mapping.*')->distinct();
                        $model = $model->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve');
                        $model = $model->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id');

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }

                        if ($site) {
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }

                        if ($assets) {
                            $model = $model->where('cve_assets.title', '=', $assets);
                        }
                    }

                    if ($level) {
                        if ($level == 'critical') {
                            $model = $model->where('severity', 'CRITICAL');
                        } else if ($level == 'high') {
                            $model = $model->where('severity', 'HIGH');
                        } else if ($level == 'medium') {
                            $model = $model->where('severity', 'MEDIUM');
                        } else if ($level == 'low') {
                            $model = $model->where('severity', 'LOW');
                        } else if ($level == 'none') {
                            $model = $model->where(function ($query) use ($request) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                        }
                    }

                    $model = $model->get();

                    $count_assets = $model->groupBy('cveven_id')->count();
                    $count = $model->count();
                    $high = $model->where('severity', '=', 'HIGH')->count();
                    $medium = $model->where('severity', '=', 'MEDIUM')->count();
                    $critical = $model->where('severity', '=', 'CRITICAL')->count();
                    $low = $model->where('severity', '=', 'LOW')->count();
                    $none = $model->where('severity', '=', 'NONE')->count();
                    $none = $none + $model->where('severity', '=', '')->count();

                    $html .= '<ul class="total-count">
                                    <li>
                                        <h1>Total Asset</h1>
                                        <span class="color-purple">' . $count_assets . '</span>
                                    </li>
                                    <li>
                                        <h1>Total CVE</h1>
                                        <span class="color-red">' . $count . '</span>
                                    </li>
                                </ul>';

                    $response = [
                        "html" => $html,
                        "count" => $count,
                        "count_high" => $high,
                        "count_medium" => $medium,
                        "count_critical" => $critical,
                        "count_low" => $low,
                        "count_none" => $none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_top_host(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
          
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $input = $data['data'] ?? [];
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    $input['site'] = $siteScope['site_id'];
                    $input['get_role_custom_first'] = array_merge(
                        is_array($get_role_custom_first) ? $get_role_custom_first : [],
                        [
                            'site_id_arr' => $siteScope['site_id_arr'],
                            'superadmin' => $siteScope['is_superadmin'] ? 1 : 0,
                        ]
                    );
                    $count_ = $input['count_'] ?? null;
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $endDate = $input['endDate'] ?? null;
                    $startDate = $input['startDate'] ?? null;
                    $site = $input['site'] ?? null;
                    $check = $input['check'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $level = $input['level'] ?? null;

               

                    $role_custom = @check_role_custom();

                    // $data_transcation = json_encode($role_custom);
                    // $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    // return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    // wait ask team
                    if (!$role_custom['vulnerabilities']) {
                        //check_permission403();
                    }

                    ksort($input);
                    $cacheKey = 'api_count_host_' . md5(json_encode($input));

                    if (Cache::has($cacheKey)) {
                        $result = Cache::get($cacheKey);
                    } else {
                        $startTime = microtime(true);
                        $result = $this->topHostChartData($input);
                        $timeMs = round((microtime(true) - $startTime) * 1000, 2);
                        if ($timeMs > 500) {
                            \Log::warning("⚠️ Slow Query in count_host(): {$timeMs} ms | Params=" . json_encode($input));
                        } else {
                            \Log::info("count_host() completed in {$timeMs} ms");
                        }
                        Cache::put($cacheKey, $result, 60);
                    }

                    $data_transcation = json_encode($result);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
        
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_top_host_bk(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';


                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];

                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

                    if ($count == 1) {
                        $model = new CVEMapping;
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        if ($site) {
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site)->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->where('data_datacve_mapping_assets.site_id', $site);
                        }
                        if ($assets) {
                            $model = $model->where('cve_assets.title', '=', $assets);
                        }
                        if ($keywords) {
                            $model = $model->where('data_datacve_mapping.namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }

                        if ($check == 1) {
                            $model = $model->where('data_datacve_mapping_assets.is_fix', '=', $check);
                        }


                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }

                    } else {

                        $model = new CVEMapping;

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site)->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $model = $model->where('data_datacve_mapping_assets.site_id', $site);
                        }


                    }

                    if ($level) {
                        if ($level == 'critical') {
                            $model = $model->where('severity', 'CRITICAL');
                        } else if ($level == 'high') {
                            $model = $model->where('severity', 'HIGH');
                        } else if ($level == 'medium') {
                            $model = $model->where('severity', 'MEDIUM');
                        } else if ($level == 'low') {
                            $model = $model->where('severity', 'LOW');
                        } else if ($level == 'none') {
                            $model = $model->where(function ($query) use ($request) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                        }
                    }

                    $get_ip = $model
                        ->select(DB::raw('count(*) as cveven_count,title as vendor_ip
                            ,sum(severity = "HIGH") as HIGH
                            ,sum(severity = "MEDIUM") as MEDIUM
                            ,sum(severity = "LOW") as LOW
                            ,(sum(severity = "NONE") + sum(severity = "")) as NONE
                            ,sum(severity = "CRITICAL") as CRITICAL
                            '
                        ))
                        ->groupBy('cve_assets.vendor', 'cve_assets.title')
                        ->orderBy('cveven_count', 'desc')
                        ->limit(5)
                        ->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve', 'left')
                        ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id', 'left')
                        ->get();

                    $_array = array();
                    $severity_high = array();
                    $severity_critical = array();
                    $severity_medium = array();
                    $severity_low = array();
                    $severity_none = array();

                    if ($get_ip) {
                        foreach ($get_ip as $key) {
                            $vendor_ip = @$key->vendor_ip;
                            $HIGH = @$key->HIGH;
                            $MEDIUM = @$key->MEDIUM;
                            $LOW = @$key->LOW;
                            $NONE = @$key->NONE;
                            $CRITICAL = @$key->CRITICAL;

                            array_push($_array, $vendor_ip);
                            array_push($severity_high, $HIGH);
                            array_push($severity_medium, $MEDIUM);
                            array_push($severity_low, $LOW);
                            array_push($severity_none, $NONE);
                            array_push($severity_critical, $CRITICAL);
                        }
                    }

                    $severity_high = array_map(function ($value) {
                        return intval($value);
                    }, $severity_high);
                    $severity_medium = array_map(function ($value) {
                        return intval($value);
                    }, $severity_medium);
                    $severity_low = array_map(function ($value) {
                        return intval($value);
                    }, $severity_low);
                    $severity_none = array_map(function ($value) {
                        return intval($value);
                    }, $severity_none);
                    $severity_critical = array_map(function ($value) {
                        return intval($value);
                    }, $severity_critical);



                    $response = [
                        "ip" => $_array,
                        "severity_high" => $severity_high,
                        "severity_critical" => $severity_critical,
                        "severity_low" => $severity_low,
                        "severity_medium" => $severity_medium,
                        "severity_none" => $severity_none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }


    public function vulnerabilitys__fixed(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            //return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $input = $data['data'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $site = $siteScope['site_id'];
                    $keywords = $input['keywords'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $date_start = $input['date_start'] ?? $input['startDate'] ?? null;
                    $date_end = $input['date_end'] ?? $input['endDate'] ?? null;
                    $check = $input['check'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $level = $input['level'] ?? null;

                    $response = $this->remediationSeverityCounts(
                        $site,
                        $assets,
                        $keywords,
                        $isDateSearch,
                        $date_start,
                        $date_end,
                        $level,
                        $datatype,
                        $get_role_custom_first
                    );

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_change_status(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];
                    $active = $data['data']['active'];

                    $asset_id = $data['data']['asset_id'];
                    $cvename = $data['data']['cvename'];
                    $site_id = $data['data']['site_id'];
                    $group = $data['data']['group'];

                    if ($group == 2) {
                        $date_now = date('Y-m-d H:i:s');
                        $CVEAssets_data = CVEAssets::where("id", $asset_id)->first();
                        $CVEAssets_data_list = CVEAssets::where("vendor", $CVEAssets_data->vendor)
                            ->where("title", $CVEAssets_data->title)
                            ->where("version", $CVEAssets_data->version)
                            ->where("edition", $CVEAssets_data->edition)
                            ->where("site_id", $CVEAssets_data->site_id)->where("active", 1)->select('id')->get();
                        foreach ($CVEAssets_data_list as $data_asset_id) {
                            CVEMappingAssets::where('cve_asset_id', $data_asset_id->id)->where('namecve', $cvename)->where('site_id', $site_id)
                                ->update([
                                    'is_fix' => $active,
                                    'updated_fix_at' => $date_now
                                ]);

                            CVEMapping::where('cveven_id', $data_asset_id->id)->where('namecve', $cvename)
                                ->update([
                                    'is_fix' => $active,
                                    'updated_at' => $date_now
                                ]);
                        }

                    } else {
                        $date_now = date('Y-m-d H:i:s');
                        $cveMappingAsset = CVEMappingAssets::where("id", $id)->first();
                        if ($cveMappingAsset) {
                            $cveMappingAsset->is_fix = $active;
                            $cveMappingAsset->updated_fix_at = $date_now;
                            $cveMappingAsset->save();

                            CVEMapping::where('cveven_id', $cveMappingAsset->cve_asset_id)->where('namecve', $cveMappingAsset->namecve)
                                ->update([
                                    'is_fix' => $active,
                                    'updated_at' => $date_now
                                ]);
                        }
                    }

                    $response = [
                        "data" => 'success',
                        "message" => langapp('changes_saved_successful'),
                        'redirect' => route('monitoringvulnerabilitys.index'),
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_change_status_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $id = $data['data']['id'];
                    $id_asset = $data['data']['id_asset'];
                    $active = $data['data']['active'];

                    $CVEMapping = CVEMapping::where("id", $id)->first();
                    if ($CVEMapping) {
                        $CVEMapping->is_fix = $active;
                        $CVEMapping->save();

                        CVEMappingAssets::where('cve_asset_id', $CVEMapping->cveven_id)->where('namecve', $CVEMapping->namecve)
                            ->update([
                                'is_fix' => $active,
                                'updated_fix_at' => now()
                            ]);
                    }

                    $CVEAssets = CVEAssets::where("id", $id_asset)->first();

                    $response = [
                        "code" => $CVEAssets->code,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_change_isFix(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $ids = $data['data']['id'];

                    foreach ($ids as $cve_id) {
                        $cve = CVEMapping::where('id', $cve_id)->first();
                        if ($cve) {
                            $cve->is_fix = ($cve->is_fix == 1) ? 0 : 1;
                            $cve->save();

                            CVEMappingAssets::where('cve_asset_id', $cve->cveven_id)->where('namecve', $cve->namecve)
                                ->update([
                                    'is_fix' => $cve->is_fix,
                                    'updated_fix_at' => now()
                                ]);
                        }
                    }

                    $response = [
                        "message" => langapp('changes_saved_successful'),
                        'redirect' => route('monitoringvulnerabilitys.index'),
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }

    public function vulnerabilitys_change_isFix_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $ids = $data['data']['id'];
                    $id_asset = $data['data']['id_asset'];

                    foreach ($ids as $cve_id) {
                        $cve = CVEMapping::where('id', $cve_id)->first();
                        if ($cve) {
                            $cve->is_fix = ($cve->is_fix == 1) ? 0 : 1;
                            $cve->save();

                            CVEMappingAssets::where('cve_asset_id', $cve->cveven_id)->where('namecve', $cve->namecve)
                                ->update([
                                    'is_fix' => $cve->is_fix,
                                    'updated_fix_at' => now()
                                ]);
                        }
                    }

                    $CVEAssets = CVEAssets::where("id", $id_asset)->first();
                    $response = [
                        "code" => $CVEAssets->code,
                        "message" => langapp('changes_saved_successful'),
                        "redirect" => route('monitoringvulnerabilitys.asset_data_detail', ['code' => $CVEAssets->code]),
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ];
            return response()->json($response);
        }
    }

    public function vulnerabilitys_asset_data_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $code = $data['data']['code'];
                    $cve_asset = CVEAssets::where('active', 1)->where('code', $code)->with('get_site')->first();
                    $page = langapp('vulnerabilitys');

                    $response = [
                        "cve_asset" => $cve_asset,
                        "page" => $page,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_cve_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $request->merge($data['data']);
                    $isDateSearch = $data['data']['isDateSearch'];
                    $fix = $data['data']['fix'];
                    $id = $data['data']['id'];
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];

                    if ($isDateSearch || $fix) {
                        $model = CVEMapping::where('cveven_id', $id)->orderBy('published', 'desc');

                        if ($isDateSearch) {

                            // $date_start = $startDate;
                            // $date_end = $endDate;

                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                            $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';


                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                            $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }

                        if ($fix) {
                            if ($fix == 1) {
                                $model = $model->where('is_fix', 0);
                            } else if ($fix == 2) {
                                $model = $model->where('is_fix', 1);
                            }

                        }
                    } else {
                        $model = CVEMapping::where('cveven_id', $id)->where('is_fix', 0)->orderBy('published', 'desc');
                    }

                    $model->get();

                    $response = DataTables::of($model)->toJson();

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_all_asset_data(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            //return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => []]);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $request->merge($data['data']);
                    $site = $data['data']['site'];
                    $model = CVEAssets::where('active', 1)->with('get_site');

                    if ($site) {
                        $model = $model->where('site_id', $site);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if (@$get_role_custom_first['superadmin'] == 1) {


                    } else if (@$get_role_custom_first['client'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

                    } else if (@$get_role_custom_first['site_support'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr);

                    } else if (@$get_role_custom_first['site_admin'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr);

                    } else if (@$get_role_custom_first['site_client'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

                    }



                    $model->get();

                    $response = DataTables::of($model)->toJson();

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_all(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $input = $data['data'] ?? [];
                    $siteScope = $this->resolveApiSiteContext($request, $data, $input);
                    [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $site = $siteScope['site_id'];
                    $site_id_arr = $siteScope['site_id_arr'];
                    $is_superadmin = $siteScope['is_superadmin'];
                    $check = $input['check'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $level = $input['level'] ?? null;
                    $displayType = $input['displayType'] ?? null;
                    $count = $input['count_'] ?? $input['count'] ?? null;
                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];

                    $startTime = microtime(true);
                    $prefix = DB::getTablePrefix();

        
                    $model = DB::table('data_datacve_mapping AS m')
                        ->leftJoin('data_datacve_mapping_assets AS ma', 'm.namecve', '=', 'ma.namecve')
                        ->leftJoin('cve_assets AS a', 'ma.cve_asset_id', '=', 'a.id')
                        ->where('a.active', 1); // ✅ Active Assets Only

                    


                    if ($count == 1) {
                        if (!$is_superadmin && !empty($site_id_arr)) {
                            $model->whereIn('m.namecve', function ($q) use ($site_id_arr) {
                                $q->select('namecve')
                                    ->from('data_datacve_mapping_assets')
                                    ->whereIn('site_id', $site_id_arr);
                            });
                        }

                        if ($site) {
                            $model->where('ma.site_id', $site);
                        }
                        if ($this->hasAssetsFilter($assets)) {
                            $model->where('a.title', $assets);
                        }
                        if ($keywords) {
                            $model->where('m.namecve', 'LIKE', "%{$keywords}%");
                        }
                        if ($hasDateFilter) {
                            $model->whereBetween(
                                DB::raw("STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')"),
                                [$date_start, $date_end]
                            );
                        }
                        if ($datatype) {
                            $model->whereIn('m.severity', $datatype);
                        }

                        if ($check == 1) {
                            $model->where('ma.is_fix', 1);
                        } elseif ($check == 2) {
                            $model->where('ma.is_fix', 0);
                        } else {
                            $model->where('ma.is_fix', 0);
                        }
                    } else {
                        if (!$is_superadmin && !empty($site_id_arr)) {
                            $model->whereIn('m.namecve', function ($q) use ($site_id_arr) {
                                $q->select('namecve')
                                    ->from('data_datacve_mapping_assets')
                                    ->whereIn('site_id', $site_id_arr);
                            });
                        }
                        if ($site) {
                            $model->where('ma.site_id', $site);
                        }
                    }



                    if ($level) {
                        $level = strtoupper($level);
                        if ($level === 'NONE') {
                            $model->where(function ($q) {
                                $q->where('m.severity', 'NONE')->orWhere('m.severity', '');
                            });
                        } else {
                            $model->where('m.severity', $level);
                        }
                    }


                    if ($displayType == 'mon') {
                        $get_month = $model->selectRaw("
                        COUNT(DISTINCT {$prefix}m.namecve) AS count_mon,
                        DAY(STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')) AS mon
                    ")
                            ->whereRaw("MONTH(STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')) = MONTH(CURDATE())")
                            ->groupBy('mon')
                            ->orderBy('mon')
                            ->get();

                        $daysInMonth = (int) date('t');
                        $count_month = array_fill(0, $daysInMonth, 0);
                        foreach ($get_month as $r) {
                            $count_month[$r->mon - 1] = $r->count_mon;
                        }

                        $namexAxis = range(1, $daysInMonth);
                        $nameyAxis = 'Number (Days)';
                        $nameSeries = 'Number of Days';
                    } else {
                        $get_month = $model->selectRaw("
                        COUNT(DISTINCT {$prefix}m.namecve) AS count_mon,
                        MONTH(STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')) AS mon
                    ")
                            ->whereRaw("YEAR(STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')) = YEAR(CURDATE())")
                            ->groupBy('mon')
                            ->orderBy('mon')
                            ->get();

                        $count_month = array_fill(0, 12, 0);
                        foreach ($get_month as $r) {
                            $count_month[$r->mon - 1] = (int)$r->count_mon;
                        }

                        $namexAxis = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $nameyAxis = 'Number (Months)';
                        $nameSeries = 'Number of Months';
                    }

             

                    $response = [
                        'sddad' => $get_month->toArray(),
                        "nameXAxis" => $namexAxis,
                        "nameYAxis" => $nameyAxis,
                        "nameSeries" => $nameSeries,
                        "count_month" => $count_month
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_load_cve_assets(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $cvename = $data['data']['cvename'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    $assets = @$data['data']['assets'];
                    $check = @$data['data']['check'];

                    $group = @$data['data']['group'];
                    $id = @$data['data']['id'];

                    if ($group == 2) {
                        $data['page'] = langapp('vulnerabilitys');


                        $model = CVEMapping::with('get_site')->with('get_cve_asset')->select('data_datacve_mapping.*')->distinct(); //::orderBy('id', 'desc')
                        //  $model =  $model->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve');
                        //$model = $model->join('cve_assets','cve_assets.id','=','data_datacve_mapping_assets.cve_asset_id');

                        // $CVEMappingAssets_data = CVEMappingAssets::select('data_datacve_mapping_assets.namecve','site.name as site_name','data_datacve_mapping_assets.site_id','namecve as edition')->Join('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id');




                        $CVEMappingAssets_data = CVEMappingAssets::select('data_datacve_mapping_assets.namecve', 'site.name as site_name', 'data_datacve_mapping_assets.site_id', 'data_datacve_mapping.namecve as Hostname', 'data_datacve_mapping.description as IP', 'data_datacve_mapping.published', 'data_datacve_mapping.cvss_score', 'data_datacve_mapping.severity')
                            ->Join('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id')
                            ->Join('data_datacve_mapping', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve');


                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            // $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);

                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            //$model = $model->whereIn('site_id', $site_id_arr);
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);

                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            //$model = $model->whereIn('site_id', $site_id_arr);
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);

                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            //$model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }



                        if ($site) {

                            $site_id = $site;

                            //$CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id)->select('namecve')->get();
                            // $model = $model->whereIn('data_datacve_mapping.namecve',  $CVEMappingAssets_name);

                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.site_id', '=', $site_id);
                        }
                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.cve_asset_id', '=', $id);

                        $data['data'] = $CVEMappingAssets_data->get();
                        foreach ($data['data'] as $d) {
                            $html_status = '';
                            if ($d['severity'] === "") {
                                $dummyServerity = 'NONE';
                            } else {
                                $dummyServerity = $d['severity'];
                            }
                            $html_status .= get_CVSS_Severity_status($d['cvss_score'], $dummyServerity, 'badg');
                            $d['title'] = '<strong>Published:' . '</strong> ' . $d['published'];
                            $d['version'] = '';
                            $d['Hostname'] = $html_status . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $d['namecve'];
                            $d['is_fix'] = 0;
                            $CVEAssets_data = CVEAssets::where("id", $id)->first();
                            $CVEAssets_data_list = CVEAssets::where("vendor", $CVEAssets_data->vendor)
                                ->where("title", $CVEAssets_data->title)
                                ->where("version", $CVEAssets_data->version)
                                ->where("edition", $CVEAssets_data->edition)
                                ->where("site_id", $CVEAssets_data->site_id)->where("active", 1)->select('id')->get();
                            $cve_asset_id_list = array();
                            foreach ($CVEAssets_data_list as $CVEAssets_data_list_data) {
                                array_push($cve_asset_id_list, $CVEAssets_data_list_data->id);
                            }
                            $is_fix_1 = 0;
                            $CVEMappingAssets_is_fix_1 = CVEMappingAssets::whereIn('cve_asset_id', $cve_asset_id_list)->where('namecve', $d['namecve'])->where('site_id', $d['site_id'])->get();
                            foreach ($CVEMappingAssets_is_fix_1 as $CVEMappingAssets_is_fix_check) {
                                if ($CVEMappingAssets_is_fix_check->is_fix == 1) {
                                    $is_fix_1++;
                                }
                            }
                            if ($is_fix_1 == count($CVEMappingAssets_is_fix_1)) {
                                $d['is_fix'] = 1;
                            }


                        }







                        $response = [
                            'data' => $data['data']
                        ];

                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);




                    } else {








                        if (empty($site)) {
                            $data['page'] = langapp('vulnerabilitys');

                            $CVEMappingAssets_data = CVEMappingAssets::select('data_datacve_mapping_assets.id', 'data_datacve_mapping_assets.namecve', 'data_datacve_mapping_assets.namecve', 'data_datacve_mapping_assets.is_fix', 'data_datacve_mapping_assets.site_id', 'data_datacve_mapping_assets.code', 'site.name as site_name', 'cve_assets.code as cve_assets_code', 'cve_assets.vendor', 'cve_assets.title', 'cve_assets.version', 'cve_assets.edition', 'cve_assets.IP', 'cve_assets.Hostname')
                                ->Join('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id')
                                ->Join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                                ->where('data_datacve_mapping_assets.namecve', $cvename)->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        } else {
                            $data['page'] = langapp('vulnerabilitys');


                            $CVEMappingAssets_data = CVEMappingAssets::select('data_datacve_mapping_assets.id', 'data_datacve_mapping_assets.namecve', 'data_datacve_mapping_assets.namecve', 'data_datacve_mapping_assets.is_fix', 'data_datacve_mapping_assets.site_id', 'data_datacve_mapping_assets.code', 'site.name as site_name', 'cve_assets.code as cve_assets_code', 'cve_assets.vendor', 'cve_assets.title', 'cve_assets.version', 'cve_assets.edition', 'cve_assets.IP', 'cve_assets.Hostname')
                                ->Join('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id')
                                ->Join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                                ->where('data_datacve_mapping_assets.namecve', $cvename)->where('data_datacve_mapping_assets.site_id', $site)->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        }
                        if ($assets) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('cve_assets.title', '=', $assets);
                        }

                        if ($check == 1) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', $check);
                        } else if ($check == 2) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                        } else {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                        }


                        $response = [
                            'data' => $CVEMappingAssets_data->get()
                        ];

                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

                    }
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }


    public function vulnerabilitys_loadbyip(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $input      = $data['data'] ?? [];
                    $ip         = $input['ip']          ?? null;
                    $hostname   = $input['hostname']    ?? null;
                    $level      = $input['level']       ?? null;
                    $keywords   = $input['keywords']    ?? null;
                    $assets     = $input['assets']      ?? null;
                    $check      = $input['check']       ?? null;
                    [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
                    $siteScope  = $this->resolveApiSiteContext($request, $data, $input);
                    $siteId     = $siteScope['site_id'] ?? ($input['site_id'] ?? null);

                    if (empty($ip)) {
                        $response = ['data' => []];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    // Step 1: Find all asset IDs under this IP
                    $assetIds = DB::table('cve_assets')
                        ->where('active', 1)
                        ->where(function ($q) use ($ip) {
                            $q->where('IP', $ip)->orWhere('ip', $ip);
                        })
                        ->when(!empty($hostname), fn($q) => $q->where(function ($sub) use ($hostname) {
                            $sub->where('Hostname', $hostname)->orWhere('hostname', $hostname);
                        }))
                        ->when(!empty($siteId), fn($q) => $q->where('site_id', $siteId))
                        ->when($this->hasAssetsFilter($assets), function ($q) use ($assets) {
                            $arr = is_array($assets) ? $assets : [$assets];
                            $q->whereIn('title', $arr);
                        })
                        ->pluck('id')
                        ->toArray();

                    if (empty($assetIds)) {
                        $response = ['data' => []];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    // Step 2: JOIN CVE mapping with those assets
                    $query = DB::table('data_datacve_mapping_assets as a')
                        ->join('data_datacve_mapping as m', 'a.namecve', '=', 'm.namecve')
                        ->join('cve_assets as c', 'a.cve_asset_id', '=', 'c.id')
                        ->join('site as s', 'a.site_id', '=', 's.id')
                        ->select(
                            'a.id as id',
                            'm.namecve',
                            'm.cvss_score',
                            'm.severity',
                            'm.published',
                            'm.description',
                            'c.vendor',
                            'c.title',
                            'c.version',
                            'c.edition',
                            'c.hostname',
                            'c.ip',
                            'a.is_fix',
                            's.name as site_name'
                        )
                        ->whereIn('a.cve_asset_id', $assetIds)
                        ->where('c.active', 1)
                        ->when(!empty($siteId), fn($q) => $q->where('a.site_id', $siteId))
                        ->when(!empty($level), fn($q) => $q->where('m.severity', strtoupper($level)))
                        ->when($this->hasAssetsFilter($assets), function ($q) use ($assets) {
                            $arr = is_array($assets) ? $assets : [$assets];
                            $q->whereIn('c.title', $arr);
                        })
                        ->when(
                            !empty($keywords),
                            fn($q) =>
                            $q->where(function ($sub) use ($keywords) {
                                $kw = '%' . trim($keywords) . '%';
                                $sub->where('m.namecve',     'like', $kw)
                                    ->orWhere('m.description', 'like', $kw)
                                    ->orWhere('c.title',       'like', $kw)
                                    ->orWhere('c.vendor',      'like', $kw)
                                    ->orWhere('c.Hostname',    'like', $kw)
                                    ->orWhere('c.hostname',    'like', $kw)
                                    ->orWhere('c.IP',          'like', $kw)
                                    ->orWhere('c.ip',          'like', $kw);
                            })
                        )
                        ->when($hasDateFilter, fn($q) => $q->whereBetween('m.published', [$date_start, $date_end]));
                    $this->applyIsFixConstraint($query, $check, 'a.is_fix');
                    $query->groupBy('m.namecve')
                        ->orderByDesc('m.published')
                        ->orderBy('c.hostname', 'asc');

                    $queryData = $query->get();

                    // Step 3: Enrich with source_text (same pattern as loadCVEByCpe)
                    if ($queryData->isNotEmpty()) {
                        $cveNames = $queryData->pluck('namecve')->unique();

                        $cveSources = DB::table('data_cve_sources')
                            ->select(
                                'namecve',
                                DB::raw('GROUP_CONCAT(source ORDER BY source SEPARATOR ", ") as sources')
                            )
                            ->whereIn('namecve', $cveNames)
                            ->groupBy('namecve')
                            ->pluck('sources', 'namecve');

                        foreach ($queryData as $d) {
                            $raw = strtolower(trim($cveSources[$d->namecve] ?? ''));
                            $sourceMap = array_map(function ($i) {
                                return $i === 'online' ? 'Online Feed' : 'Local Data';
                            }, array_filter(array_map('trim', explode(',', $raw))));

                            if (empty($sourceMap)) {
                                $sourceMap[] = 'Local Data';
                            }

                            $d->source_text = implode(', ', array_unique($sourceMap));
                            $d->source_text_short = strlen($d->source_text) > 20
                                ? substr($d->source_text, 0, 20) . '...'
                                : $d->source_text;
                        }
                    }

                    $response = ['data' => $queryData];
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ];

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function vulnerabilitys_load_cve_by_cpe(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            if ($data['data']['menu'] !== 'vulnerabilities') {
                return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
            }
            $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
            if ($auth_site['status_code'] !== '200') {
                return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
            }

            $input        = $data['data'] ?? [];
            $vendor       = $input['vendor']       ?? null;
            $title        = $input['title']        ?? null;
            $version      = $input['version']      ?? null;
            $edition      = $input['edition']      ?? null;
            $hostname     = $input['hostname']     ?? null;
            $assets       = $input['assets']       ?? null;
            $level        = $input['level']        ?? null;
            $keywords     = $input['keywords']     ?? null;
            $search_      = $input['search_']      ?? null;
            $check        = $input['check']        ?? null;
            [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
            $siteScope    = $this->resolveApiSiteContext($request, $data, $input);
            $siteId       = $siteScope['site_id'] ?? ($input['site_id'] ?? null);

            if (empty($siteId) && empty($hostname)) {
                $response = ['data' => [], 'error' => 'Missing required parameter: site_id or hostname'];
                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }

            set_time_limit(300);

            $prefix = DB::getTablePrefix();

            $query = DB::table('data_datacve_mapping_assets as a')
                ->join('cve_assets as c', function ($join) {
                    $join->on('a.cve_asset_id', '=', 'c.id');
                })
                ->join('data_datacve_mapping as m', 'a.namecve', '=', 'm.namecve')
                ->join('site as s', 'a.site_id', '=', 's.id')
                ->select(
                    'a.id', 'a.cve_asset_id', 'a.namecve', 'a.site_id',
                    'm.description', 'm.published', 'm.cvss_score', 'm.severity',
                    'c.Hostname', 'c.IP', 'c.vendor', 'c.title', 'c.version', 'c.edition',
                    'a.is_fix', 's.name as site_name'
                )
                ->groupBy('a.namecve')
                ->where('c.active', 1)
                ->when(!empty($vendor),  fn($q) => $q->where('c.vendor', trim($vendor)))
                ->when(!empty($title),   fn($q) => $q->where('c.title', trim($title)))
                ->when(!empty($assets),  function ($q) use ($assets) {
                    $arr = is_array($assets) ? $assets : [$assets];
                    return $q->whereIn('c.title', $arr);
                })
                ->when(!empty($version) && $version !== '*', fn($q) => $q->where('c.version', trim($version)))
                ->when(!empty($edition) && $edition !== '*', fn($q) => $q->where('c.edition', trim($edition)))
                ->when(!empty($siteId),   fn($q) => $q->where('a.site_id', $siteId))
                ->when(!empty($hostname), fn($q) => $q->where('c.Hostname', trim($hostname)))
                ->when(!empty($search_) && $search_ != '1', fn($q) => $q->where(function ($sub) use ($search_) {
                    $kw = '%' . trim($search_) . '%';
                    $sub->where('a.namecve', 'like', $kw)->orWhere('m.description', 'like', $kw);
                }))
                ->when(!empty($keywords) && stripos($keywords, 'cve') !== false, fn($q) => $q->where(function ($sub) use ($keywords) {
                    $kw = '%' . trim($keywords) . '%';
                    $sub->where('a.namecve', 'like', $kw);
                }))
                ->when(!empty($level), fn($q) => $q->where('m.severity', $level))
                ->when($hasDateFilter, fn($q) => $q->whereBetween('m.published', [$date_start, $date_end]));
            $this->applyIsFixConstraint($query, $check, 'a.is_fix');
            $query->orderByDesc('m.published');

            $CVEs = $query->get();

            if ($CVEs->isEmpty()) {
                $response = ['data' => []];
                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }

            $cveAssetIds = $CVEs->pluck('cve_asset_id')->unique();
            $allFixMap   = CVEMappingAssets::whereIn('cve_asset_id', $cveAssetIds)
                ->where('site_id', $siteId)
                ->get(['cve_asset_id', 'namecve', 'is_fix'])
                ->groupBy('cve_asset_id');

            $cveNames   = $CVEs->pluck('namecve')->unique();
            $cveSources = DB::table('data_cve_sources')
                ->select('namecve', DB::raw('GROUP_CONCAT(source ORDER BY source SEPARATOR ", ") as sources'))
                ->whereIn('namecve', $cveNames)
                ->groupBy('namecve')
                ->pluck('sources', 'namecve');

            foreach ($CVEs as $d) {
                $dummy        = $d->severity ?: 'NONE';
                $html_severity = get_CVSS_Severity_status($d->cvss_score, $dummy, 'badg');
                $raw          = strtolower(trim($cveSources[$d->namecve] ?? ''));
                $sourceMap    = array_map(fn($i) => $i === 'online' ? 'Online Feed' : 'Local Data',
                    array_filter(array_map('trim', explode(',', $raw))));
                if (empty($sourceMap)) $sourceMap[] = 'Local Data';
                $d->source_text       = implode(', ', array_unique($sourceMap));
                $d->source_text_short = strlen($d->source_text) > 20 ? substr($d->source_text, 0, 20) . '...' : $d->source_text;
                $d->Hostname          = $html_severity . '&nbsp;&nbsp;&nbsp;' . e($d->namecve);
                $fixList              = $allFixMap->get($d->cve_asset_id, collect())->where('namecve', $d->namecve);
                if ($fixList->count()) {
                    $d->is_fix = ($fixList->where('is_fix', 1)->count() == $fixList->count()) ? 1 : 0;
                }
            }

            $response = ['data' => $CVEs];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => $e->getMessage()];
            $header = $request->bearerToken();
            $data = $this->dataFalse($header, $request->mode, $request->data);
            $this->saveLog($data['site']['data']['id'], json_encode($response));
            return response()->json($response);
        }
    }

    public function vulnerabilitys_load_cpe_by_asset(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $data = $this->dataFalse($header, $request->mode, $request->data);
            if ($data === false) return response()->json(['error' => 'Invalid request', 'status_code' => '400']);
            if ($data['data']['menu'] !== 'vulnerabilities') return response()->json(['error' => "Unauthorized", 'status_code' => '403']);
            $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
            if ($auth_site['status_code'] !== '200') return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);

            $input    = $data['data'] ?? [];
            $hostname = trim((string) ($input['hostname'] ?? ''));
            $ip       = trim((string) ($input['ip'] ?? ''));
            if ($ip !== '' && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
            $assets   = $input['assets'] ?? null;
            $level    = $input['level'] ?? null;
            $keywords = $input['keywords'] ?? null;
            $search_  = $input['search_'] ?? null;
            $check    = $input['check'] ?? null;
            $isDateSearch = $input['isDateSearch'] ?? null;
            $startDate = $input['startDate'] ?? null;
            $endDate = $input['endDate'] ?? null;

            [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);
            if (!$hasDateFilter && !empty($isDateSearch) && !empty($startDate) && !empty($endDate)) {
                $date_start = date('Y-m-d', strtotime($startDate));
                $date_end = date('Y-m-d', strtotime($endDate));
                $hasDateFilter = true;
            }
            $siteScope = $this->resolveApiSiteContext($request, $data, $input);
            $siteId = $siteScope['site_id'] ?? ($input['site_id'] ?? null);
            $is_superadmin = $siteScope['is_superadmin'];
            $site_id_arr = $siteScope['site_id_arr'];

            if (($hostname === '' || $hostname === '-') && $ip === '') {
                $response = ['data' => []];
                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }

            $prefix = DB::getTablePrefix();
            $query = DB::table('data_datacve_mapping_assets as m')
                ->join('cve_assets as a', 'm.cve_asset_id', '=', 'a.id')
                ->join('data_datacve_mapping as dm', 'm.namecve', '=', 'dm.namecve')
                ->select(
                    'a.vendor', 'a.title', 'a.version', 'a.edition', 'a.Hostname', 'a.IP',
                    DB::raw('COUNT(DISTINCT ' . $prefix . 'm.namecve) as cve_count')
                )
                ->where(function ($q) use ($hostname, $ip) {
                    if ($hostname !== '' && $hostname !== '-') {
                        $q->where('a.Hostname', $hostname);
                        if ($ip !== '') {
                            $q->orWhere('a.IP', $ip);
                        }
                    } elseif ($ip !== '') {
                        $q->where('a.IP', $ip);
                    }
                })
                ->where('a.active', 1);

            if (!empty($siteId)) {
                $query->where('m.site_id', $siteId);
            } elseif (!$is_superadmin && !empty($site_id_arr)) {
                $query->whereIn('m.site_id', $site_id_arr);
            }

            if (!empty($assets)) {
                $arr = is_array($assets) ? $assets : [$assets];
                $query->whereIn('a.title', $arr);
            }

            if ($hasDateFilter) {
                $query->whereBetween('dm.published', [$date_start, $date_end]);
            }

            if (!empty($search_) && $search_ != '1') {
                $kw = '%' . trim($search_) . '%';
                $query->where(function ($sub) use ($kw) {
                    $sub->where('m.namecve', 'like', $kw)
                        ->orWhere('dm.description', 'like', $kw);
                });
            }

            if (!empty($keywords)) {
                $kw = '%' . trim($keywords) . '%';
                $query->where(function ($sub) use ($kw) {
                    $sub->where('m.namecve', 'like', $kw)
                        ->orWhere('dm.description', 'like', $kw);
                });
            }

            if (!empty($level)) {
                $query->where('dm.severity', $level);
            }

            $this->applyIsFixConstraint($query, $check, 'm.is_fix');

            $cpeList = $query->groupBy('a.vendor', 'a.title', 'a.version', 'a.edition')->get();
            $response = ['data' => $cpeList];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => $e->getMessage()];
            $data = $this->dataFalse($request->bearerToken(), $request->mode, $request->data);
            $this->saveLog($data['site']['data']['id'], json_encode($response));
            return response()->json($response);
        }
    }

    public function vulnerabilitys_get_sites_by_cve(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $data = $this->dataFalse($header, $request->mode, $request->data);
            if ($data === false) return response()->json(['error' => 'Invalid request', 'status_code' => '400']);
            if ($data['data']['menu'] !== 'vulnerabilities') return response()->json(['error' => "Unauthorized", 'status_code' => '403']);
            $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
            if ($auth_site['status_code'] !== '200') return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);

            $input   = $data['data'] ?? [];
            $namecve = $input['namecve'] ?? null;
            $siteId  = $input['site_id'] ?? null;
            $assets  = $input['assets']  ?? null;
            $prefix  = DB::getTablePrefix();

            $model_has_roles = DB::table('model_has_roles')->where('model_id', $data['data']['user_id'])->first();
            $role_id = @$model_has_roles->role_id;
            $is_superadmin = ($role_id == 1);

            $site_id_arr = [];
            if (!$is_superadmin) {
                $site_id_arr = DB::table('user_site')->where('user_id', $data['data']['user_id'])->where('active', 1)->pluck('site_id')->toArray();
            }

            $sql = "
                SELECT s.id, s.name,
                    COUNT(DISTINCT CONCAT(
                        COALESCE(ca.vendor,''), COALESCE(ca.title,''),
                        COALESCE(ca.version,''), COALESCE(ca.edition,''),
                        COALESCE(ca.Hostname,''), COALESCE(ca.IP,'')
                    )) AS asset_count
                FROM {$prefix}data_datacve_mapping_assets AS m
                INNER JOIN {$prefix}site AS s ON m.site_id = s.id
                INNER JOIN {$prefix}cve_assets AS ca ON m.cve_asset_id = ca.id
                WHERE m.namecve = ? AND m.is_fix = 0
            ";
            $params = [$namecve];
            if (!empty($siteId)) { $sql .= ' AND m.site_id = ?'; $params[] = $siteId; }
            if (!empty($assets)) { $sql .= ' AND ca.title = ?';  $params[] = $assets; }

            if (!$is_superadmin) {
                if (count($site_id_arr) > 0) {
                    $placeholders = implode(',', array_fill(0, count($site_id_arr), '?'));
                    $sql .= " AND m.site_id IN ($placeholders)";
                    $params = array_merge($params, $site_id_arr);
                } else {
                    $sql .= " AND 1=0";
                }
            }

            $sql .= ' GROUP BY s.id, s.name';

            $result = DB::select($sql, $params);
            $data_transcation = json_encode($result);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => $e->getMessage()];
            $data = $this->dataFalse($request->bearerToken(), $request->mode, $request->data);
            $this->saveLog($data['site']['data']['id'], json_encode($response));
            return response()->json($response);
        }
    }

    public function vulnerabilitys_get_assets_by_site(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $data = $this->dataFalse($header, $request->mode, $request->data);
            if ($data === false) return response()->json(['error' => 'Invalid request', 'status_code' => '400']);
            if ($data['data']['menu'] !== 'vulnerabilities') return response()->json(['error' => "Unauthorized", 'status_code' => '403']);
            $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
            if ($auth_site['status_code'] !== '200') return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);

            $input   = $data['data'] ?? [];
            $namecve = $input['namecve'] ?? null;
            $siteId  = $input['site_id'] ?? null;
            $assets  = $input['assets']  ?? null;

            $result = CVEMappingAssets::query()
                ->select('cve_assets.vendor', 'cve_assets.title', 'cve_assets.version', 'cve_assets.edition', 'cve_assets.Hostname', 'cve_assets.IP')
                ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
                ->where('data_datacve_mapping_assets.namecve', $namecve)
                ->where('data_datacve_mapping_assets.site_id', $siteId)
                ->where('data_datacve_mapping_assets.is_fix', 0)
                ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                ->distinct()
                ->orderBy('cve_assets.Hostname')
                ->get();

            $data_transcation = json_encode($result);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => $e->getMessage()];
            $data = $this->dataFalse($request->bearerToken(), $request->mode, $request->data);
            $this->saveLog($data['site']['data']['id'], json_encode($response));
            return response()->json($response);
        }
    }

    private function topHostChartData(array $input): array
    {
        $prefix = DB::getTablePrefix();
        $keywords = $input['keywords'] ?? null;
        $datatype = $input['datatype'] ?? null;
        $assets = $input['assets'] ?? null;
        $endDate = $input['endDate'] ?? null;
        $startDate = $input['startDate'] ?? null;
        $site = $input['site'] ?? null;
        $check = $input['check'] ?? null;
        $isDateSearch = $input['isDateSearch'] ?? null;
        $level = $input['level'] ?? null;
        $get_role_custom_first = $input['get_role_custom_first'] ?? [];
        $site_id_arr = $this->normalizeSiteIdArray(@$get_role_custom_first['site_id_arr'] ?? []);
        $is_superadmin = (@$get_role_custom_first['superadmin'] == 1);
        [$hasDateFilter, $date_start, $date_end] = $this->resolvePublishedDateRange($input);

        $model = DB::table(DB::raw("{$prefix}data_datacve_mapping_assets AS ma"))
            ->join(DB::raw("{$prefix}data_datacve_mapping AS m"), DB::raw('m.namecve'), '=', DB::raw('ma.namecve'))
            ->join(DB::raw("{$prefix}cve_assets AS a"), DB::raw('a.id'), '=', DB::raw('ma.cve_asset_id'))
            ->where(DB::raw('a.active'), 1);

        if (!$is_superadmin && !empty($site_id_arr)) {
            $model->whereIn(DB::raw('ma.site_id'), $site_id_arr);
        }

        if ($site) {
            $model->where(DB::raw('ma.site_id'), $site);
        }

        if ($assets) {
            $model->where(DB::raw('a.title'), '=', $assets);
        }
        if ($keywords) {
            $model->where(DB::raw('m.namecve'), 'LIKE', "%{$keywords}%");
        }

        if ($hasDateFilter) {
            $model->whereBetween(DB::raw('m.published'), [$date_start, $date_end]);
        }

        if ($check) {
            if ($check == 1) {
                $model->where(DB::raw('ma.is_fix'), 1);
            } elseif ($check == 2) {
                $model->where(DB::raw('ma.is_fix'), 0);
            }
        } else {
            $model->where(DB::raw('ma.is_fix'), 0);
        }

        if ($datatype) {
            $model->whereIn(DB::raw('m.severity'), $datatype);
        }

        if ($level) {
            $level = strtoupper($level);
            if ($level == 'NONE') {
                $model->where(function ($q) {
                    $q->where(DB::raw('m.severity'), 'NONE')->orWhere(DB::raw('m.severity'), '');
                });
            } else {
                $model->where(DB::raw('m.severity'), $level);
            }
        }

        $get_ip = $model->selectRaw('
            COUNT(DISTINCT ma.id) AS cveven_count,
            a.title AS vendor_ip,
            COUNT(DISTINCT CASE WHEN m.severity = "HIGH" THEN ma.id END) AS HIGH,
            COUNT(DISTINCT CASE WHEN m.severity = "MEDIUM" THEN ma.id END) AS MEDIUM,
            COUNT(DISTINCT CASE WHEN m.severity = "LOW" THEN ma.id END) AS LOW,
            COUNT(DISTINCT CASE WHEN m.severity = "NONE" OR m.severity = "" THEN ma.id END) AS NONE,
            COUNT(DISTINCT CASE WHEN m.severity = "CRITICAL" THEN ma.id END) AS CRITICAL
        ')
            ->groupBy(DB::raw('a.vendor, a.title'))
            ->orderBy('cveven_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'ip' => $get_ip->pluck('vendor_ip'),
            'severity_high' => $get_ip->pluck('HIGH')->map(fn($v) => (int) $v),
            'severity_critical' => $get_ip->pluck('CRITICAL')->map(fn($v) => (int) $v),
            'severity_low' => $get_ip->pluck('LOW')->map(fn($v) => (int) $v),
            'severity_medium' => $get_ip->pluck('MEDIUM')->map(fn($v) => (int) $v),
            'severity_none' => $get_ip->pluck('NONE')->map(fn($v) => (int) $v),
        ];
    }

    /**
     * Count fixed asset-findings by severity (aligned with load_cve ASSETS REMEDIATION).
     */
    private function remediationSeverityCounts($site, $assets, $keywords, $isDateSearch, $date_start, $date_end, $level, $datatype, $get_role_custom_first)
    {
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        $date_start_date = $date_end_date = null;

        if (!empty($isDateSearch) && $date_start && $date_end) {
            $date_start_date = date('Y-m-d', strtotime(explode(' ', $date_start)[0]));
            $date_end_date = date('Y-m-d', strtotime(explode(' ', $date_end)[0]));
        }

        $query = CVEMappingAssets::query()
            ->select('data_datacve_mapping_assets.id', 'dm.severity')
            ->join('cve_assets', 'data_datacve_mapping_assets.cve_asset_id', '=', 'cve_assets.id')
            ->where('cve_assets.active', 1)
            ->join('data_datacve_mapping as dm', 'data_datacve_mapping_assets.namecve', '=', 'dm.namecve')
            ->where('data_datacve_mapping_assets.is_fix', 1);

        if ($site) {
            $query->where('data_datacve_mapping_assets.site_id', $site);
        } elseif (@$get_role_custom_first['superadmin'] != 1 && !empty($site_id_arr)) {
            $siteIds = collect($site_id_arr)->pluck('site_id')->filter()->values()->all();
            if (!empty($siteIds)) {
                $query->whereIn('data_datacve_mapping_assets.site_id', $siteIds);
            }
        }

        if ($assets) {
            $assets = is_array($assets) ? $assets : [$assets];
            $query->whereIn('cve_assets.title', $assets);
        }

        if ($keywords) {
            $kw = '%' . trim($keywords) . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('data_datacve_mapping_assets.namecve', 'like', $kw)
                    ->orWhere('dm.description', 'like', $kw);
            });
        }

        if ($isDateSearch && $date_start_date && $date_end_date) {
            $query->whereBetween('dm.published', [$date_start_date, $date_end_date]);
        }

        if (!empty($datatype)) {
            $query->whereIn('dm.severity', $datatype);
        }

        if ($level) {
            $level = strtolower($level);
            if ($level === 'critical') {
                $query->where('dm.severity', 'CRITICAL');
            } elseif ($level === 'high') {
                $query->where('dm.severity', 'HIGH');
            } elseif ($level === 'medium') {
                $query->where('dm.severity', 'MEDIUM');
            } elseif ($level === 'low') {
                $query->where('dm.severity', 'LOW');
            } elseif ($level === 'none') {
                $query->where(function ($q) {
                    $q->where('dm.severity', 'NONE')->orWhere('dm.severity', '')->orWhereNull('dm.severity');
                });
            }
        }

        $rows = $query->distinct('data_datacve_mapping_assets.id')->get();

        return [
            'isFix_critical' => $rows->where('severity', 'CRITICAL')->count(),
            'isFix_medium' => $rows->where('severity', 'MEDIUM')->count(),
            'isFix_high' => $rows->where('severity', 'HIGH')->count(),
            'isFix_low' => $rows->where('severity', 'LOW')->count(),
            'isFix_none' => $rows->filter(function ($row) {
                return in_array($row->severity, ['NONE', '', null], true);
            })->count(),
        ];
    }

    /**
     * Normalize site_id list from client payload (may be [{site_id: N}, ...]).
     */
    private function normalizeSiteIdArray($siteIdArr): array
    {
        if (empty($siteIdArr)) {
            return [];
        }

        return collect($siteIdArr)->map(function ($item) {
            if (is_array($item)) {
                return $item['site_id'] ?? null;
            }
            if (is_object($item)) {
                return $item->site_id ?? null;
            }

            return $item;
        })->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * AuthorizationRegister returns SiteSettings model — normalize before reading id.
     */
    private function normalizeAuthSiteData($authSiteData): ?array
    {
        if ($authSiteData === null) {
            return null;
        }
        if (is_array($authSiteData)) {
            return $authSiteData;
        }
        if (is_object($authSiteData)) {
            if (method_exists($authSiteData, 'toArray')) {
                return $authSiteData->toArray();
            }
            if (isset($authSiteData->id)) {
                return [
                    'id' => $authSiteData->id,
                    'code' => $authSiteData->code ?? null,
                ];
            }
        }

        return null;
    }

    private function hasAssetsFilter($assets): bool
    {
        if ($assets === null || $assets === '') {
            return false;
        }
        if (is_array($assets)) {
            return count(array_filter($assets, fn($v) => $v !== null && $v !== '')) > 0;
        }

        return true;
    }

    /**
     * Normalize published date range (aligned with center MonitoringVulnerabilitysController).
     *
     * @return array{0: bool, 1: ?string, 2: ?string} [active, startYmd, endYmd]
     */
    private function resolvePublishedDateRange(array $input): array
    {
        $isDateSearch = $input['isDateSearch'] ?? null;
        if (empty($isDateSearch)) {
            return [false, null, null];
        }

        $startRaw = $input['startDate'] ?? $input['date_start'] ?? null;
        $endRaw = $input['endDate'] ?? $input['date_end'] ?? null;
        if (empty($startRaw) || empty($endRaw)) {
            return [false, null, null];
        }

        return [
            true,
            date('Y-m-d', strtotime($startRaw)),
            date('Y-m-d', strtotime($endRaw)),
        ];
    }

    /**
     * Resolve effective site scope for API requests (aligned with center UI).
     */
    private function resolveVulnerabilitySiteScope(
        array $getRoleCustom,
        ?int $userId,
        $requestedSite,
        $authSiteData,
        ?string $siteCode = null,
        ?string $apiMode = null
    ): array {
        $authSiteData = $this->normalizeAuthSiteData($authSiteData);

        $authSiteId = !empty($authSiteData['id']) ? (int) $authSiteData['id'] : null;
        if ($authSiteId === null && $siteCode) {
            $siteRow = SiteSettings::where('code', $siteCode)->first();
            if ($siteRow) {
                $authSiteId = (int) $siteRow->id;
            }
        }

        $isSuperadmin = false;
        if ($userId) {
            $roleRow = DB::table('model_has_roles')->where('model_id', $userId)->first();
            if ($roleRow && (int) $roleRow->role_id === 1) {
                $isSuperadmin = true;
            }
        }

        $siteIdArr = [];
        if ($userId && !$isSuperadmin) {
            $siteIdArr = DB::table('user_site')
                ->where('user_id', $userId)
                ->where('active', 1)
                ->pluck('site_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if (empty($siteIdArr) && !$isSuperadmin) {
            $siteIdArr = $this->normalizeSiteIdArray(@$getRoleCustom['site_id_arr']);
        }

        $requestedSiteId = ($requestedSite !== null && $requestedSite !== '') ? (int) $requestedSite : null;

        // site_offline: client API is always bounded to the bearer-token site
        if ($apiMode === 'site_offline' && $authSiteId !== null && !$isSuperadmin) {
            $siteIdArr = [$authSiteId];
            $effectiveSiteId = $requestedSiteId ?: $authSiteId;
            if ($requestedSiteId !== null && $requestedSiteId !== $authSiteId) {
                $effectiveSiteId = $authSiteId;
            }
        } else {
            $effectiveSiteId = $requestedSiteId ?? $authSiteId;

            if ($effectiveSiteId === null && count($siteIdArr) === 1) {
                $effectiveSiteId = $siteIdArr[0];
            }

            if (!$isSuperadmin && $authSiteId !== null && empty($siteIdArr)) {
                $siteIdArr = [$authSiteId];
                if ($effectiveSiteId === null) {
                    $effectiveSiteId = $authSiteId;
                }
            }

            if (
                !$isSuperadmin &&
                $effectiveSiteId !== null &&
                !empty($siteIdArr) &&
                !in_array($effectiveSiteId, $siteIdArr, true)
            ) {
                $effectiveSiteId = ($authSiteId && in_array($authSiteId, $siteIdArr, true))
                    ? $authSiteId
                    : ($authSiteId ?? null);
            }
        }

        return [
            'is_superadmin' => $isSuperadmin,
            'site_id' => $effectiveSiteId,
            'site_id_arr' => $siteIdArr,
        ];
    }

    private function resolveApiSiteContext(Request $request, array $data, array $input): array
    {
        return $this->resolveVulnerabilitySiteScope(
            is_array($input['get_role_custom_first'] ?? null) ? $input['get_role_custom_first'] : [],
            isset($data['data']['user_id']) ? (int) $data['data']['user_id'] : null,
            $input['site'] ?? null,
            $data['site']['data'] ?? null,
            $request->code ?? null,
            $request->mode ?? null
        );
    }

    private function isFixFilterValue($check): int
    {
        return ($check == 1) ? 1 : 0;
    }

    private function isCheckFilterProvided($check): bool
    {
        return !($check === null || $check === '');
    }

    private function applyIsFixConstraint($query, $check, string $column = 'is_fix')
    {
        if ($this->isCheckFilterProvided($check)) {
            $query->where($column, ($check == 1) ? 1 : 0);
        } else {
            $query->where($column, 0);
        }

        return $query;
    }

    private function dataFalse($bearerToken, $mode, $data)
    {
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if ($site['status_code'] !== '200') {
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'], $site['data']['mac_address_key']);

            if ($data === false) {
                return $data;
            } else {
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }

        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    private function explode_val($val, $type = null, $url)
    {
        $result = '';
        if ($val) {
            $val_arr = explode(",", $val);
            if ($val_arr) {
                foreach ($val_arr as $tag) {
                    if ($type == 'tags') {
                        $result .= '<a href="' . $url . '/indicators/tags/' . $tag . '">' . $tag . '</a> ,';
                    } else if ($type == 'groups') {
                        $result .= '<a href="' . $url . '/indicators/groups/' . $tag . '">' . $tag . '</a> ,';
                    } else {
                        $result .= '<a href="#">' . $tag . '</a> ,';
                    }

                }
                $result = rtrim($result, ',');
            }
        } else {
            $result = '';
        }
        return $result;
    }

    public function vulnerabilitys_export(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'vulnerabilities') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $input = $data['data'] ?? [];
                    $request->merge($input);
                    
                    if (empty($request->group)) {
                        return response()->json(['error' => 'Missing group parameter'], 400);
                    }

                    if ($request->group == 1) {
                        return $this->exportByCVE($request);
                    } elseif ($request->group == 2) {
                        return $this->exportByHost($request);
                    } else {
                        return response()->json(['error' => 'Invalid export mode'], 400);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);
            $response = ['status_code' => 500, 'message' => $e->getMessage()];
            return response()->json($response, 500);
        }
    }

    private function exportByCVE(Request $request)
    {
        try {
            $hasDateFilter = !empty($request->startDate) && !empty($request->endDate);

            // ✅ Join and filter directly to avoid pulling all CVE names into memory
            $query = DB::table('data_datacve_mapping_assets as a')
                ->join('site as s', 'a.site_id', '=', 's.id')
                ->join('data_datacve_mapping as m', 'a.namecve', '=', 'm.namecve')
                ->join('cve_assets as c', function ($join) {
                    $join->on('a.cve_asset_id', '=', 'c.id')
                        ->where('c.active', 1);
                })
                ->select(
                    'a.namecve',
                    'm.cvss_score',
                    'm.severity',
                    'm.published',
                    'm.description',
                    'c.vendor',
                    'c.title',
                    'c.version',
                    'c.edition',
                    'c.Hostname as hostname',
                    'c.IP as ip',
                    'a.is_fix',
                    's.name as site_name'
                )
                ->where('a.site_id', $request->site)
                ->when($hasDateFilter, fn($q) => $q->whereBetween('m.published', [$request->startDate, $request->endDate]))
                ->when(!empty($request->level), fn($q) => $q->where('m.severity', $request->level))
                ->when(!empty($request->vendor), fn($q) => $q->where('c.vendor', $request->vendor))
                ->when(!empty($request->title), fn($q) => $q->where('c.title', $request->title))
                ->when(!empty($request->version), fn($q) => $q->where('c.version', $request->version))
                ->when(!empty($request->edition), fn($q) => $q->where('c.edition', $request->edition))
                ->when(
                    !empty($request->keywords),
                    fn($q) =>
                    $q->where(function ($sub) use ($request) {
                        $kw = '%' . trim($request->keywords) . '%';
                        $sub->where('a.namecve', 'like', $kw)
                            ->orWhere('m.description', 'like', $kw)
                            ->orWhere('c.title', 'like', $kw)
                            ->orWhere('c.vendor', 'like', $kw)
                            ->orWhere('c.Hostname', 'like', $kw)
                            ->orWhere('c.IP', 'like', $kw);
                    })
                )
                ->distinct()
                ->orderByDesc('m.published')
                ->orderBy('c.Hostname', 'asc');

            // ✅ Export CSV using in-memory stream to avoid storage path permissions error
            $filename = 'Export_By_CVEs_' . date('Ymd_His') . '.csv';
            $handle = fopen('php://temp', 'w+');

            // ✅ ใส่ BOM UTF-8 (กันภาษาไทยเพี้ยน)
            fwrite($handle, "\xEF\xBB\xBF");

            $header = [
                'CVE ID',
                'Published',
                'Operating System',
                'IP Address',
                'Advice from consultants',
                'Severity',
                'Hostname',
                'Site'
            ];
            fputcsv($handle, $header);

            // ✅ เขียนข้อมูลทีละบรรทัดโดยแบ่งเป็น chunk เพื่อประหยัด memory
            $query->chunk(1000, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $line = [
                        $row->namecve,
                        $row->published,
                        trim($row->vendor . ' ' . $row->title . ' ' . $row->version . ' ' . $row->edition),
                        $row->ip ?? '-',
                        str_replace(["\r", "\n", ","], ' ', $row->description),
                        trim($row->cvss_score . ' ' . $row->severity),
                        $row->hostname,
                        $row->site_name,
                    ];

                    fputcsv($handle, $line);
                }
            });

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            // ✅ ส่งไฟล์กลับให้ดาวน์โหลด
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function exportByHost(Request $request)
    {
        try {
            $prefix = DB::getTablePrefix();

            $query = DB::table('data_datacve_mapping_assets as a')
                ->join('site as s', 'a.site_id', '=', 's.id')
                ->join('data_datacve_mapping as m', 'a.namecve', '=', 'm.namecve')
                ->join('cve_assets as c', function ($join) {
                    $join->on('a.cve_asset_id', '=', 'c.id')
                        ->where('c.active', 1);
                })
                ->select(
                    'c.Hostname as hostname',
                    'c.IP as ip',
                    'c.vendor',
                    'c.title',
                    'c.version',
                    'c.edition',
                    'a.namecve',
                    'm.cvss_score',
                    'm.severity',
                    'm.published',
                    'a.is_fix',
                    's.name as site_name'
                )
                ->where('a.site_id', $request->site)
                ->when(!empty($request->level), fn($q) => $q->where('m.severity', $request->level))
                ->when(!empty($request->vendor), fn($q) => $q->where('c.vendor', $request->vendor))
                ->when(!empty($request->title), fn($q) => $q->where('c.title', $request->title))
                ->when(!empty($request->version), fn($q) => $q->where('c.version', $request->version))
                ->when(!empty($request->edition), fn($q) => $q->where('c.edition', $request->edition))
                ->when(!empty($request->startDate) && !empty($request->endDate), fn($q) => $q->whereBetween('m.published', [$request->startDate, $request->endDate]))
                ->when(
                    !empty($request->keywords),
                    fn($q) =>
                    $q->where(function ($sub) use ($request) {
                        $kw = '%' . trim($request->keywords) . '%';
                        $sub->where('a.namecve', 'like', $kw)
                            ->orWhere('m.description', 'like', $kw)
                            ->orWhere('c.title', 'like', $kw)
                            ->orWhere('c.vendor', 'like', $kw)
                            ->orWhere('c.Hostname', 'like', $kw)
                            ->orWhere('c.IP', 'like', $kw);
                    })
                )
                ->distinct()
                ->orderBy('c.Hostname', 'asc')
                ->orderBy('m.published', 'desc');

            // ✅ ตรวจสอบว่ามีข้อมูลหรือไม่ (ใช้ exists() แทน get() เพื่อประหยัด memory)
            if (!(clone $query)->limit(1)->exists()) {
                return response()->json(['error' => 'No data found'], 404);
            }

            $filename = 'Export_By_Host_' . date('Ymd_His') . '.csv';
            $handle = fopen('php://temp', 'w+');

            // ✅ ใส่ BOM UTF-8 (กันภาษาไทยเพี้ยน)
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Hostname',
                'IP',
                'Vendor',
                'Title',
                'Version',
                'Edition',
                'CVE ID',
                'Severity',
                'CVSS Score',
                'Published',
                'Is Fixed',
                'Site'
            ]);

            // ✅ เขียนข้อมูลทีละบรรทัดโดยแบ่งเป็น chunk เพื่อประหยัด memory
            $query->chunk(1000, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->hostname,
                        $row->ip,
                        $row->vendor,
                        $row->title,
                        $row->version,
                        $row->edition,
                        $row->namecve,
                        $row->severity,
                        $row->cvss_score,
                        $row->published,
                        $row->is_fix ? 'Yes' : 'No',
                        $row->site_name,
                    ]);
                }
            });

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

