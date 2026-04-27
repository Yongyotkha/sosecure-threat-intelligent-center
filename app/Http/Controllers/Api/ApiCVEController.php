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
                    $date_start = $input['date_start'] ?? null;
                    $date_end = $input['date_end'] ?? null;
                    $search = $input['search'] ?? null;
                    $site = $input['site'] ?? null;
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
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    // Extract additional fields for By Host filters
                    $vendor = $input['vendor'] ?? null;
                    $title = $input['title'] ?? null;
                    $version = $input['version'] ?? null;
                    $edition = $input['edition'] ?? null;


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


                    $model = '';
                    $html = '';

                    $site_id = null;


                    if ($search == 1) {
                        $model = CVEMapping::with('get_site')->with('get_cve_asset')
                            ->select('data_datacve_mapping.*')->distinct();

                        $CVEMappingAssets_data = CVEMappingAssets::select('namecve');

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {


                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve');
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve');
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve');
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve');
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                            $CVEMappingAssets_data = $CVEMappingAssets_data->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.site_id', '=', $site_id);
                        }


                        if ($assets) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id');
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('cve_assets.title', '=', $assets);
                        }
                        if ($keywords) {
                            $model = $model->where('data_datacve_mapping.namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }


                        if ($check == 1) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', $check);
                        } else if ($check == 2) {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                        } else {
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);
                        }

                        if ($datatype) {
                            $model = $model->whereIn('severity', $request->datatype);
                        }

                        $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_data);


                    } else {

                        $model = CVEMapping::with('get_site')->with('get_cve_asset')
                            ->select('data_datacve_mapping.*')->distinct();

                        // ✅ แทน whereIn ด้วย whereExists
                        $model = $model->whereExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets')
                                ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                ->where('data_datacve_mapping_assets.is_fix', 0);
                        });


                        $CVEMappingAssets_data = CVEMappingAssets::select('namecve');
                        $get_role_custom_first = @get_role_custom();
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];

                        //

                        if (@$get_role_custom_first['superadmin'] != 1) {
                            if ($site_id_arr) {
                                $model = $model->whereExists(function ($q) use ($site_id_arr) {
                                    $q->select(DB::raw(1))
                                        ->from('data_datacve_mapping_assets')
                                        ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve')
                                        ->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr)
                                        ->where('data_datacve_mapping_assets.is_fix', 0);
                                });
                            }

                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.site_id', '=', $site_id);
                        }

                        $CVEMappingAssets_data = $CVEMappingAssets_data->where('data_datacve_mapping_assets.is_fix', '=', 0);





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
                            ->where('data_datacve_mapping_assets.is_fix', 0);

                            $CVEMappingAssets_data
                            ->when(!empty($site), fn($q) => $q->where('data_datacve_mapping_assets.site_id', $site))
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
                                !empty($request->isDateSearch) && !empty($date_start_date_format) && !empty($date_end_date_format),
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
                            ->leftJoin('site', 'data_datacve_mapping_assets.site_id', '=', 'site.id');


                        // ✅ ฟิลเตอร์ทั้งหมด
                        $CVEMappingAssets_data
                            ->when(!empty($siteId), fn($q) => $q->where('data_datacve_mapping_assets.site_id', $siteId))
                            ->when(!empty($vendor), fn($q) => $q->where('data_datacve_mapping_assets.vendor', $vendor))
                            ->when(!empty($title), fn($q) => $q->where('data_datacve_mapping_assets.title', $title))
                            ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                            ->when(
                                $fix,
                                fn($q) => $q->where('data_datacve_mapping_assets.is_fix', $fix)
                            )
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
                                !empty($isDateSearch) && !empty($date_start_datetime_format) && !empty($date_end_datetime_format),
                                fn($q) => $q->whereBetween('data_datacve_mapping.published', [$date_start_datetime_format, $date_end_datetime_format])
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
                                ->when(
                                    !empty($isDateSearch) && !empty($date_start) && !empty($date_end),
                                    function ($q) use ($date_start, $date_end) {
                                        $q->whereBetween('data_datacve_mapping.published', [$date_start, $date_end]);
                                    }
                                )
                                ->when($check, function ($q) use ($check) {
                                    if ($check == 1) {
                                        $q->where('data_datacve_mapping_assets.is_fix', 1);
                                    } elseif ($check == 2) {
                                        $q->where('data_datacve_mapping_assets.is_fix', 0);
                                    }
                                }, function ($q) {
                                    $q->where('data_datacve_mapping_assets.is_fix', 0);
                                })
                                ->when(!empty($level), fn($q) => $q->where('data_datacve_mapping.severity', strtoupper($level)))
                                ->pluck('data_datacve_mapping_assets.cve_asset_id')
                                ->toArray();
                        }

                        $ByIPs = DB::table('cve_assets')
                            ->select(
                                DB::raw("{$prefix}cve_assets.ip AS ip"),
                                DB::raw("{$prefix}cve_assets.hostname AS Hostnames"),
                                DB::raw("GROUP_CONCAT(DISTINCT {$prefix}site.name SEPARATOR ', ') AS site_names"),

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
                            ->when(!empty($assets), fn($q) => $q->where('cve_assets.title', $assets))
                            ->when(
                                !empty($keywords),
                                fn($q) =>
                                $q->where(function ($sub) use ($keywords, $assetIdsFromCVE) {
                                    $kw = '%' . trim($keywords) . '%';
                                    $sub->where('cve_assets.ip', 'like', $kw)
                                        ->orWhere('cve_assets.hostname', 'like', $kw);

                                    // ถ้ามี Asset ID ที่เจอจาก namecve ให้เอามา OR ด้วย
                                    if (!empty($assetIdsFromCVE)) {
                                        $sub->orWhereIn('cve_assets.id', $assetIdsFromCVE);
                                    }
                                })
                            )
                            // ✅ ALWAYS apply base CVE filters (is_fix, site_id, level)
                            // Date filter is CONDITIONAL inside the whereExists
                            ->whereExists(function ($sub) use ($date_start, $date_end, $siteId, $check) {
                                $sub->select(DB::raw(1))
                                    ->from('data_datacve_mapping_assets')
                                    ->join('data_datacve_mapping', 'data_datacve_mapping_assets.namecve', '=', 'data_datacve_mapping.namecve')
                                    ->whereColumn('data_datacve_mapping_assets.cve_asset_id', 'cve_assets.id');

                                // ✅ CONDITIONAL: Date Filter (only if isDateSearch is set)
                                if (!empty($isDateSearch) && !empty($date_start) && !empty($date_end)) {
                                    $startDate = date("Y-m-d H:i:s", strtotime($date_start));
                                    $endDate = date("Y-m-d H:i:s", strtotime($date_end));
                                    $sub->whereBetween('data_datacve_mapping.published', [$startDate, $endDate]);
                                }

                                // ✅ ALWAYS: Filter by is_fix (status)
                                if ($check) {
                                    if ($check == 1) { // Fixed
                                        $sub->where('data_datacve_mapping_assets.is_fix', 1);
                                    } elseif ($check == 2) { // Not Fixed
                                        $sub->where('data_datacve_mapping_assets.is_fix', 0);
                                    }
                                } else {
                                    $sub->where('data_datacve_mapping_assets.is_fix', 0); // Default: Unfixed
                                }

                                // ✅ ALWAYS: Filter by Site ID
                                if (!empty($siteId)) {
                                    $sub->where('data_datacve_mapping_assets.site_id', $siteId);
                                }
                                
                                // ✅ ALWAYS: Filter by Level (Severity)
                                if (!empty($level)) {
                                    $sub->where('data_datacve_mapping.severity', strtoupper($level));
                                }
                                
                                // ✅ ALWAYS: Filter by Assets (Product/Title)
                                if (!empty($assets)) {
                                    $assets = is_array($assets) ? $assets : [$assets];
                                    $sub->join('cve_assets as ca_filter', 'data_datacve_mapping_assets.cve_asset_id', '=', 'ca_filter.id')
                                        ->whereIn('ca_filter.title', $assets);
                                }
                            });

                        // ⭐ group by IP + Hostname (แยก row สำหรับแต่ละ hostname)
                        $ByIPs->groupBy('cve_assets.IP', 'cve_assets.hostname');

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
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    $response['page'] = langapp('vulnerabilitys');

                    $response['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
                    $response['count_CVEMapping'] = CVEMapping::count();
                    $response['count_isFix'] = CVEMappingAssets::where("is_fix", '=', 1)->count();

                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if (@$get_role_custom_first['superadmin'] == 1) {
           
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->select('title', 'site_id')->distinct()->orderBy('title')->get();

                    } else if (@$get_role_custom_first['client'] == 1) {


                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->select('title', 'site_id')->distinct()->orderBy('title')->get();


                    } else if (@$get_role_custom_first['site_support'] == 1) {


                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->select('title', 'site_id')->distinct()->orderBy('title')->get();
                    } else if (@$get_role_custom_first['site_admin'] == 1) {


                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->select('title', 'site_id')->distinct()->orderBy('title')->get();
                    } else if (@$get_role_custom_first['site_client'] == 1) {


                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $response['cve_assets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->select('title', 'site_id')->distinct()->orderBy('title')->get();
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
                    $site = $input['site'] ?? null;
                    $startDate = $input['startDate'] ?? null;
                    $endDate = $input['endDate'] ?? null;
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $check = $input['check'] ?? null;
                    $level = $input['level'] ?? null;
                    $hostname = $input['hostname'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $get_role_custom_first = $input['get_role_custom_first'] ?? null;
                    $site_id_arr = @$get_role_custom_first['site_id_arr'] ?? null;
                    $siteId = $site;
                    $assetLimit = null;
                    $date_start = $date_end = null;

                    if (!empty($isDateSearch) && $startDate && $endDate) {
                        $date_start = date('Y-m-d H:i:s', strtotime($startDate));
                        $date_end = date('Y-m-d H:i:s', strtotime($endDate));
                    }
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
                        $assetLimit = $SiteSettings->asset_limit ?? null;

                        $CVEAssets->where('site_id', $siteId);
                        $CVEMappingAssets->where('data_datacve_mapping_assets.site_id', $siteId);

                        $CVEMapping->whereIn('namecve', function ($q) use ($siteId) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets')
                                ->where('site_id', $siteId);
                        });
                    } else {
                        $CVEMapping->whereExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('data_datacve_mapping_assets')
                                ->whereColumn('data_datacve_mapping_assets.namecve', 'data_datacve_mapping.namecve');
                        });
                    }

                    // ===== ASSETS FILTER =====
                    if ($assets) {
                        $assets = is_array($assets) ? $assets : [$assets];

                        $CVEAssets->whereIn('title', $assets);

                        // Access cve_assets columns directly due to join
                        $CVEMappingAssets->whereIn('cve_assets.title', $assets);
                        Log::info('Assets filter applied to CVEMappingAssets:', ['assets' => $assets]);

                        $CVEMapping->whereIn('namecve', function ($q) use ($assets, $siteId) {
                            $q->select('namecve')
                                ->from('data_datacve_mapping_assets as ma')
                                ->join('cve_assets as a', 'a.id', '=', 'ma.cve_asset_id')
                                ->whereIn('a.title', $assets);

                            if (!empty($siteId)) {
                                $q->where('ma.site_id', $siteId);
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

                    // ===== ALWAYS JOIN TO FILTER INVALID/ORPHAN CVEs & ALLOW DATE FILTERING =====
                    $CVEMappingAssets->join('data_datacve_mapping as dm_filter', 'data_datacve_mapping_assets.namecve', '=', 'dm_filter.namecve');

                    // ===== DATE RANGE FILTER (APPLY TO CVEMapping & CVEMappingAssets) =====
                    if ($isDateSearch && $date_start && $date_end) {
                        $CVEMapping->whereBetween('published', [$date_start, $date_end]);
                        $CVEMappingAssets->whereBetween('dm_filter.published', [$date_start, $date_end]);
                    }

                    // ===== KEYWORDS FILTER =====
                    if ($keywords) {
                        $kw = '%' . trim($keywords) . '%';
                        $CVEMapping->where(function ($q) use ($kw) {
                            $q->where('namecve', 'like', $kw)
                                ->orWhere('description', 'like', $kw);
                        });
                        $CVEMappingAssets->where(function ($q) use ($kw) {
                            $q->where('data_datacve_mapping_assets.namecve', 'like', $kw)
                                ->orWhere('dm_filter.description', 'like', $kw);
                        });
                    }

                    // ===== SEVERITY FILTER =====
                    if ($level) {
                        $level = strtoupper($level);
                        if ($level === 'NONE') {
                            $CVEMapping->where(function ($q) {
                                $q->where('severity', 'NONE')->orWhereNull('severity');
                            });
                            $CVEMappingAssets->where(function ($q) {
                                $q->where('dm_filter.severity', 'NONE')->orWhereNull('dm_filter.severity');
                            });
                        } else {
                            $CVEMapping->where('severity', $level);
                            $CVEMappingAssets->where('dm_filter.severity', $level);
                        }
                    }

                    if (!empty($datatype)) {
                        $CVEMapping->whereIn('severity', $datatype);
                        $CVEMappingAssets->whereIn('dm_filter.severity', $datatype);
                    }

     

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
                        ($isDateSearch && $date_start && $date_end) ||
                        $keywords ||
                        $level ||
                        $datatype ||
                        $check ||
                        $assets // ✅ Also trigger filtered count when assets filter is present
                    ) {
                        $count_CVEAssets = (clone $CVEMappingAssets)
                            ->distinct('cve_asset_id')
                            ->count('cve_asset_id');
                    } else {
                        $count_CVEAssets = $CVEAssets->count();
                    }

                    // นับจำนวน Vulnerabilities ทั้งหมด (Unique CVEs)
                    $count_CVEMapping = $CVEMapping->distinct('namecve')->count('namecve');

                    // นับ is_fix / ทั้งหมด (Findings - ใช้ DISTINCT เพราะ JOIN กับ dm_filter อาจสร้าง duplicates)
                    $count_isFix = (clone $CVEMappingAssets)
                        ->where('data_datacve_mapping_assets.is_fix', 1)
                        ->distinct('data_datacve_mapping_assets.id')
                        ->count('data_datacve_mapping_assets.id');

                    // Debug: Log the query for count_isFix_all
                    //Log::info('count_isFix_all SQL:', ['sql' => (clone $CVEMappingAssets)->toSql(), 'bindings' => (clone $CVEMappingAssets)->getBindings()]);

                    $count_isFix_all = (clone $CVEMappingAssets)
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

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
                    $count = $input['count_'] ?? null;
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $endDate = $input['endDate'] ?? null;
                    $date_end = $input['date_end'] ?? null;
                    $startDate = $input['startDate'] ?? null;
                    $site = $input['site'] ?? null;
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
                            ->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id');

                        // --- Site Authorization ---
                        $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                        if (@$get_role_custom_first['superadmin'] != 1) {
                            $site_id_arr = @$get_role_custom_first['site_id_arr'] ?? [];
                            $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
                        }

                        // --- Apply Filters ---
                        if ($site) {
                            $model->where('data_datacve_mapping_assets.site_id', $site);
                        }

                        if ($assets) {
                            $model->where('cve_assets.title', $assets);
                        }

                        if ($keywords) {
                            $kw = '%' . trim($keywords) . '%';
                            $model->where(function ($q) use ($kw) {
                                $q->where('data_datacve_mapping.namecve', 'LIKE', $kw)
                                    ->orWhere('data_datacve_mapping.description', 'LIKE', $kw);
                            });
                        }

                        if ($isDateSearch && $startDate && $endDate) {
                            $date_start = date("Y-m-d", strtotime($startDate));
                            $date_end   = date("Y-m-d", strtotime($endDate));
                            $model->whereBetween('data_datacve_mapping.published', [$date_start, $date_end]);
                        }

                        if ($check) {
                            $model->where('data_datacve_mapping_assets.is_fix', $check);
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

                    // ksort($input);

                    // $cacheKey = 'count_host_' . md5(json_encode($input));

                    // if (Cache::has($cacheKey)) {
                    //     return response()->json(Cache::get($cacheKey));
                    // }


                    $startTime = microtime(true);
                    $prefix = DB::getTablePrefix();

                    // ✅ ระบุ prefix เอง พร้อม alias ชัดเจน
                    $model = DB::table(DB::raw("{$prefix}data_datacve_mapping AS m"))
                        ->leftJoin(DB::raw("{$prefix}data_datacve_mapping_assets AS ma"), DB::raw("m.namecve"), '=', DB::raw("ma.namecve"))
                        ->leftJoin(DB::raw("{$prefix}cve_assets AS a"), DB::raw("ma.cve_asset_id"), '=', DB::raw("a.id"))
                        ->where(DB::raw('a.active'), 1); // ✅ Active Assets Only

                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'] ?? [];


 
                    // ✅ role filter
                    if (@$get_role_custom_first['superadmin'] != 1) {
                        if (
                            @$get_role_custom_first['client'] == 1 ||
                            @$get_role_custom_first['site_support'] == 1 ||
                            @$get_role_custom_first['site_admin'] == 1 ||
                            @$get_role_custom_first['site_client'] == 1
                        ) {
                            $model->whereIn(DB::raw('m.namecve'), function ($query) use ($site_id_arr, $prefix) {
                                $query->select('namecve')
                                    ->from(DB::raw("{$prefix}data_datacve_mapping_assets"))
                                    ->whereIn('site_id', $site_id_arr);
                            });
                        }
                    }

                    // ✅ site filter
                    if ($site) {
                        $model->whereIn(DB::raw('m.namecve'), function ($q) use ($site, $prefix) {
                            $q->select('namecve')
                                ->from(DB::raw("{$prefix}data_datacve_mapping_assets"))
                                ->where('site_id', $site);
                        })->where(DB::raw('ma.site_id'), $site);
                    }

                    // ✅ filters
                    if ($assets) $model->where(DB::raw('a.title'), '=', $assets);
                    if ($keywords) $model->where(DB::raw('m.namecve'), 'LIKE', "%{$keywords}%");

                    if ($isDateSearch) {
                        $date_start = date('Y-m-d H:i:s', strtotime($startDate));
                        $date_end   = date('Y-m-d H:i:s', strtotime($endDate));
                        $model->whereBetween(DB::raw('m.published'), [$date_start, $date_end]);
                    }

                    // ✅ is_fix filter: default to 0 (unfixed)
                    if ($check) {
                        if ($check == 1) {
                            $model->where(DB::raw('ma.is_fix'), 1);
                        } elseif ($check == 2) {
                            $model->where(DB::raw('ma.is_fix'), 0);
                        }
                    } else {
                        $model->where(DB::raw('ma.is_fix'), 0); // Default: Unfixed
                    }
                    if ($datatype) $model->whereIn(DB::raw('m.severity'), $datatype);

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
         
                         
                    // ✅ main query
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

                    $result = [
                        "ip" => $get_ip->pluck('vendor_ip'),
                        "severity_high" => $get_ip->pluck('HIGH')->map(fn($v) => (int)$v),
                        "severity_critical" => $get_ip->pluck('CRITICAL')->map(fn($v) => (int)$v),
                        "severity_low" => $get_ip->pluck('LOW')->map(fn($v) => (int)$v),
                        "severity_medium" => $get_ip->pluck('MEDIUM')->map(fn($v) => (int)$v),
                        "severity_none" => $get_ip->pluck('NONE')->map(fn($v) => (int)$v),
                    ];

                    $timeMs = round((microtime(true) - $startTime) * 1000, 2);
                    if ($timeMs > 500) {
                        \Log::warning("⚠️ Slow Query in count_host(): {$timeMs} ms | Params=" . json_encode($input));
                    } else {
                        \Log::info("count_host() completed in {$timeMs} ms");
                    }

                    //Cache::put($cacheKey, $result, 60);

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
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    if ($count == 1) {
                        $model = CVEMapping::select('data_datacve_mapping.*')->distinct();
                        $CVEMappingAssets_name = CVEMappingAssets::select('namecve');
                        $CVEMappingAssets_name = $CVEMappingAssets_name->where('data_datacve_mapping_assets.is_fix', '=', 1);

                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = $model->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name);
                        }


                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_name = $CVEMappingAssets_name->where('data_datacve_mapping_assets.site_id', $site_id);
                        }
                        if ($assets) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id');
                            $CVEMappingAssets_name = $CVEMappingAssets_name->where('cve_assets.title', '=', $assets);
                        }
                        if ($keywords) {
                            $model = $model->where('data_datacve_mapping.namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }

                        // if($check==1){
                        //     $model = $model->where('is_fix', '=', $check);
                        // }
                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
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

                        $model->get();
                        $get_ip = $model
                            ->select(DB::raw('sum(severity = "HIGH") as HIGH
                        ,sum(severity = "MEDIUM") as MEDIUM
                        ,sum(severity = "LOW") as LOW
                        ,(sum(severity = "NONE") + sum(severity = "")) as NONE
                        ,sum(severity = "CRITICAL") as CRITICAL
                        '
                            ))
                            ->get();

                        // if($get_ip->)
                        $isFix_high = intval($get_ip[0]->HIGH);
                        $isFix_medium = intval($get_ip[0]->MEDIUM);
                        $isFix_critical = intval($get_ip[0]->CRITICAL);
                        $isFix_low = intval($get_ip[0]->LOW);
                        $isFix_none = intval($get_ip[0]->NONE);
                    } else {

                        $isFix = CVEMapping::select('data_datacve_mapping.*')->distinct();

                        $CVEMappingAssets_name = CVEMappingAssets::select('namecve');
                        $CVEMappingAssets_name = $CVEMappingAssets_name->where('data_datacve_mapping_assets.is_fix', '=', 1);

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if (@$get_role_custom_first['superadmin'] == 1) {

                        } else if (@$get_role_custom_first['client'] == 1) {

                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_support'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_admin'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        } else if (@$get_role_custom_first['site_client'] == 1) {
                            $CVEMappingAssets_name = $CVEMappingAssets_name->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);

                        }

                        if ($site) {
                            $site_id = $site;
                            $CVEMappingAssets_name = $CVEMappingAssets_name->where('data_datacve_mapping_assets.site_id', $site_id);
                        }

                        $isFix = $isFix->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name->get());

                        if ($level) {
                            if ($level == 'critical') {
                                $isFix = $isFix->where('severity', 'CRITICAL');
                            } else if ($level == 'high') {
                                $isFix = $isFix->where('severity', 'HIGH');
                            } else if ($level == 'medium') {
                                $isFix = $isFix->where('severity', 'MEDIUM');
                            } else if ($level == 'low') {
                                $isFix = $isFix->where('severity', 'LOW');
                            } else if ($level == 'none') {
                                // $isFix=$isFix->where('severity', 'NONE');
                                $isFix = $isFix->where(function ($query) use ($request) {
                                    $query->where('severity', 'NONE')
                                        ->orWhere('severity', '');
                                });


                            }
                        }


                        $isFix = $isFix->get();

                        $isFix_high = $isFix->where('severity', '=', 'HIGH')->count();
                        $isFix_medium = $isFix->where('severity', '=', 'MEDIUM')->count();
                        $isFix_critical = $isFix->where('severity', '=', 'CRITICAL')->count();
                        $isFix_low = $isFix->where('severity', '=', 'LOW')->count();
                        $isFix_none = $isFix->where('severity', '=', 'NONE')->count();
                        $isFix_none = $isFix_none + $isFix->where('severity', '=', '')->count();
                    }
                    $response = [
                        "isFix_critical" => $isFix_critical,
                        "isFix_medium" => $isFix_medium,
                        "isFix_high" => $isFix_high,
                        "isFix_low" => $isFix_low,
                        "isFix_none" => $isFix_none,
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
                                    'is_fix' => $request->active,
                                    'updated_fix_at' => $date_now
                                ]);

                        }

                    } else {
                        $date_now = date('Y-m-d H:i:s');
                        $data = CVEMappingAssets::where("id", $id)->first();
                        $data->is_fix = $active;
                        $data->updated_fix_at = $date_now;
                        $data->save();

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
                    $CVEMapping->is_fix = $active;
                    $CVEMapping->save();

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
                    $keywords = $input['keywords'] ?? null;
                    $datatype = $input['datatype'] ?? null;
                    $assets = $input['assets'] ?? null;
                    $endDate = $input['endDate'] ?? null;
                    $startDate = $input['startDate'] ?? null;
                    $site = $input['site'] ?? null;
                    $check = $input['check'] ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $level = $input['level'] ?? null;
                    $displayType = $input['displayType'] ?? null;
                    $count = $data['count_'] ?? [];

                    // Log::info('count_month Params:', [
                    //     'count_' => $count,
                    //     'site' => $site,
                    //     'assets' => $assets,
                    //     'isDateSearch' => $isDateSearch,
                    //     'startDate' => $startDate,
                    //     'endDate' => $endDate,
                    //     'check' => $check,
                    //     'level' => $level,
                    //     'displayType' => $displayType,
                    // ]);


                    $startTime = microtime(true);
                    $date_start = date('Y-m-d H:i:s', strtotime($request->startDate));
                    $date_end   = date('Y-m-d H:i:s', strtotime($request->endDate));

                    $get_role_custom_first = $input['get_role_custom_first'] ?? [];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'] ?? [];
                    $prefix = DB::getTablePrefix();

        
                    $model = DB::table('data_datacve_mapping AS m')
                        ->leftJoin('data_datacve_mapping_assets AS ma', 'm.namecve', '=', 'ma.namecve')
                        ->leftJoin('cve_assets AS a', 'ma.cve_asset_id', '=', 'a.id')
                        ->where('a.active', 1); // ✅ Active Assets Only

                    


                    if ($count == 1) {
                        if (@$get_role_custom_first['superadmin'] != 1) {
                            if (
                                @$get_role_custom_first['client'] == 1 ||
                                @$get_role_custom_first['site_support'] == 1 ||
                                @$get_role_custom_first['site_admin'] == 1 ||
                                @$get_role_custom_first['site_client'] == 1
                            ) {
                                $model->whereIn('m.namecve', function ($q) use ($site_id_arr) {
                                    $q->select('namecve')
                                        ->from('data_datacve_mapping_assets')
                                        ->whereIn('site_id', $site_id_arr);
                                });
                            }
                        }

                        if ($site) $model->where('ma.site_id', $site);
                        if ($assets) $model->where('a.title', $assets);
                        if ($keywords) $model->where('m.namecve', 'LIKE', "%{$keywords}%");
                        if ($isDateSearch) {
                            $model->whereBetween(
                                DB::raw("STR_TO_DATE(CAST(published AS CHAR), '%Y-%m-%d')"),
                                [$date_start, $date_end]
                            );
                        }
                        if ($datatype) $model->whereIn('m.severity', $datatype);

                        if ($check == 1) {
                            $model->where('ma.is_fix', 1);
                        } elseif ($check == 2) {
                            $model->where('ma.is_fix', 0);
                        } else {
                            $model->where('ma.is_fix', 0);
                        }
                    } else {
                        if (@$get_role_custom_first['superadmin'] != 1) {
                            if (
                                @$get_role_custom_first['client'] == 1 ||
                                @$get_role_custom_first['site_support'] == 1 ||
                                @$get_role_custom_first['site_admin'] == 1 ||
                                @$get_role_custom_first['site_client'] == 1
                            ) {
                                $model->whereIn('m.namecve', function ($q) use ($site_id_arr) {
                                    $q->select('namecve')
                                        ->from('data_datacve_mapping_assets')
                                        ->whereIn('site_id', $site_id_arr);
                                });
                            }
                        }
                        if ($site) $model->where('ma.site_id', $site);
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
                    $siteId     = $input['site_id']     ?? null;
                    $level      = $input['level']       ?? null;
                    $keywords   = $input['keywords']    ?? null;
                    $isDateSearch = $input['isDateSearch'] ?? null;
                    $startDate  = $input['startDate']   ?? null;
                    $endDate    = $input['endDate']     ?? null;

                    if (empty($ip)) {
                        $response = ['data' => []];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    // Step 1: Find all asset IDs under this IP
                    $assetIds = DB::table('cve_assets')
                        ->where('ip', $ip)
                        ->where('active', 1)
                        ->when(!empty($hostname), fn($q) => $q->where('hostname', $hostname))
                        ->when(!empty($siteId),   fn($q) => $q->where('site_id', $siteId))
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
                        ->when(!empty($level), fn($q) => $q->where('m.severity', strtoupper($level)))
                        ->when(
                            !empty($keywords),
                            fn($q) =>
                            $q->where(function ($sub) use ($keywords) {
                                $kw = '%' . trim($keywords) . '%';
                                $sub->where('m.namecve',     'like', $kw)
                                    ->orWhere('m.description', 'like', $kw)
                                    ->orWhere('c.title',       'like', $kw)
                                    ->orWhere('c.vendor',      'like', $kw)
                                    ->orWhere('c.hostname',    'like', $kw)
                                    ->orWhere('c.ip',          'like', $kw);
                            })
                        )
                        ->when(
                            !empty($isDateSearch) && !empty($startDate) && !empty($endDate),
                            fn($q) => $q->whereBetween('m.published', [
                                date('Y-m-d H:i:s', strtotime($startDate)),
                                date('Y-m-d H:i:s', strtotime($endDate)),
                            ])
                        )
                        ->groupBy('m.namecve')
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
            $siteId       = $input['site_id']      ?? null;
            $assets       = $input['assets']       ?? null;
            $level        = $input['level']        ?? null;
            $keywords     = $input['keywords']     ?? null;
            $search_      = $input['search_']      ?? null;
            $isDateSearch = $input['isDateSearch'] ?? null;
            $startDate    = $input['startDate']    ?? null;
            $endDate      = $input['endDate']      ?? null;
            $check        = $input['check']        ?? null;

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
                ->when(!empty($isDateSearch) && !empty($startDate) && !empty($endDate),
                    fn($q) => $q->whereBetween('m.published', [
                        date('Y-m-d H:i:s', strtotime($startDate)),
                        date('Y-m-d H:i:s', strtotime($endDate)),
                    ])
                )
                ->when(
                    isset($check) && is_numeric($check) && in_array($check, ['0', '1']),
                    fn($q) => $q->where('a.is_fix', $check),
                    fn($q) => $q->where('a.is_fix', 0)
                )
                ->orderByDesc('m.published');

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

            $input        = $data['data'] ?? [];
            $hostname     = $input['hostname']     ?? null;
            $siteId       = $input['site_id']      ?? null;
            $assets       = $input['assets']       ?? null;
            $level        = $input['level']        ?? null;
            $keywords     = $input['keywords']     ?? null;
            $search_      = $input['search_']      ?? null;
            $isDateSearch = $input['isDateSearch'] ?? null;
            $startDate    = $input['startDate']    ?? null;
            $endDate      = $input['endDate']      ?? null;
            $check        = $input['check']        ?? null;

            $prefix = DB::getTablePrefix();
            $query = DB::table('data_datacve_mapping_assets as m')
                ->join('cve_assets as a', 'm.cve_asset_id', '=', 'a.id')
                ->join('data_datacve_mapping as dm', 'm.namecve', '=', 'dm.namecve')
                ->select(
                    'a.vendor', 'a.title', 'a.version', 'a.edition', 'a.Hostname', 'a.IP',
                    DB::raw('COUNT(DISTINCT ' . $prefix . 'm.namecve) as cve_count')
                )
                ->where('a.Hostname', $hostname)
                ->where('a.active', 1);

            if (!empty($siteId)) $query->where('m.site_id', $siteId);
            if (!empty($assets)) {
                $arr = is_array($assets) ? $assets : [$assets];
                $query->whereIn('a.title', $arr);
            }
            if (!empty($isDateSearch) && !empty($startDate) && !empty($endDate)) {
                $query->whereBetween('dm.published', [date('Y-m-d H:i:s', strtotime($startDate)), date('Y-m-d H:i:s', strtotime($endDate))]);
            }
            if (!empty($search_) && $search_ != '1') {
                $kw = '%' . trim($search_) . '%';
                $query->where(fn($sub) => $sub->where('m.namecve', 'like', $kw)->orWhere('dm.description', 'like', $kw));
            }
            if (!empty($keywords) && stripos($keywords, 'cve') !== false) {
                $query->where('m.namecve', 'like', '%' . trim($keywords) . '%');
            }
            if (!empty($level)) $query->where('dm.severity', $level);
            if (isset($check) && is_numeric($check) && in_array($check, ['0', '1'])) {
                $query->where('m.is_fix', $check);
            } else {
                $query->where('m.is_fix', 0);
            }

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
}
