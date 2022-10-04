<?php

namespace App\Http\Controllers\Api;

use App\FXAssetsPort;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\R_s_s_news;
use App\TransactionScans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use Modules\WebDefacement\Entities\WebdefacmentSetting;

class ApiDashboardController extends ApiController
{
    public function count_asset(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'assets') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $request -> code;
                    if (@$get_role_custom['superadmin'] == 1) {
                        if (empty($site)) {
                            $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
                            $dataOut["countAssets"] = 0;
                            $dataOut["assetLimit"] = 0;
                            foreach ($datacountAssets as $key => $value) {
                                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
                                $countfn = count($AssetsData_data);
                                if ($countfn == 0) {
                                    $dataOut["countAssets"]++;
                                } else {
                                    $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
                                }
                            }
                        } else {
                            $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                            $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->where('assets.site_id', $SiteSettingsfor->id)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
                            $dataOut["countAssets"] = 0;
                            $dataOut["assetLimit"] = $SiteSettingsfor -> asset_limit;
                            foreach ($datacountAssets as $key => $value) {
                                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
                                $countfn = count($AssetsData_data);
                                if ($countfn == 0) {
                                    $dataOut["countAssets"]++;
                                } else {
                                    $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
                                }
                            }
                        }
                    } else {
                        $site_id_arr = $data['data']['site_id_arr'];
                        if (empty($site)) {
                            $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id', $site_id_arr)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
                            $dataOut["countAssets"] = 0;
                            $dataOut["assetLimit"] = 0;
                            foreach ($datacountAssets as $key => $value) {
                                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
                                $countfn = count($AssetsData_data);
                                if ($countfn == 0) {
                                    $dataOut["countAssets"]++;
                                } else {
                                    $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
                                }
                            }
                        } else {
                            $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                            $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id', $site_id_arr)->where('assets.site_id', $SiteSettingsfor->id)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
                            $dataOut["countAssets"] = 0;
                            $dataOut["assetLimit"] = $SiteSettingsfor -> asset_limit;
                            foreach ($datacountAssets as $key => $value) {
                                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
                                $countfn = count($AssetsData_data);
                                if ($countfn == 0) {
                                    $dataOut["countAssets"]++;
                                } else {
                                    $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
                                }
                            }
                        }
                    }
                    $assets = $dataOut;

                    $data_transcation = json_encode($assets);
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

    public function count_vulnerability(Request $request)
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if (@$get_role_custom['superadmin'] == 1) {
                        if (!$site) {
                            $CVEMapping = CVEMapping::select('id')->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();

                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                        }
                    } else {
                        if (!$site) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id',$site_id_arr)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                        }
                    }

                    $data_transcation = json_encode($CVEMapping);
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

    public function count_compromised(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'compromised') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if (@$get_role_custom['superadmin'] == 1) {
                        if (!$site) {
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('feel_type', ['darkweb', 'webserver', 'server', 'compromise', 'compromised'])->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->count();
                        }
                    } else {
                        if (!$site) {
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb', 'webserver', 'server', 'compromise', 'compromised'])->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->count();
                        }
                    }

                    $data_transcation = json_encode($DataLeakSocialRef);
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

    public function count_data_leak(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'data_leak') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $site_id_arr = $data['data']['site_id_arr'];

                    if (@$get_role_custom['superadmin'] == 1) {
                        if (!$site) {
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->whereIn('feel_type', ['social', 'darkweb_public'])->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->where('site_id', $site_id_m->id)->whereIn('feel_type', ['social', 'darkweb_public'])->count();
                        }
                    } else {
                        if (!$site) {
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->count();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_arr)->where('feel_type', ['social', 'darkweb_public'])->count();
                        }
                    }

                    $data_transcation = json_encode($DataLeakSocialRef);
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

    public function count_vulnerability_host(Request $request)
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if (@$get_role_custom['superadmin'] == 1) {
                        if (!$site) {
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                        }
                    } else {
                        if (!$site) {
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                        }
                    }

                    $vendor = [];
                    $title = [];
                    foreach ($CVEAssets as $item) {
                        $vendor[] = $item->vendor;
                        $title[] = $item->title;
                    }
                    $DataCveven = DataCveven::select('namecve', 'title', DB::raw('count(*) as total'))->whereIn('vendor', $vendor)->whereIn('title', $title)->groupBy('namecve')->get();
                    $namecve = [];
                    $check_total_namecve = array();
                    $host_name = [];
                    foreach ($DataCveven as $item) {
                        $namecve[] = $item->namecve;
                        $check_total_namecve[] = collect([
                            'total' => $item->total,
                            'namecve' => $item->namecve,
                            'title' => $item->title,
                        ]);
                    }

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                    if (@$get_role_custom['superadmin'] == 1) {
                        if(!$site) {
                            $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity', 'namecve')->get();
                        } else {
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $namecve)->groupBy('severity', 'namecve')->get();
                        }
                    } else {
                        if(!$site) {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id',$site_id_arr)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity','namecve')->get();
                        } else {
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->whereIn('site_id',$site_id_arr)->select('namecve')->get();
                            $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity','namecve')->get();
                        }
                    }

                    foreach ($CVEMapping as $value) {
                        foreach ($check_total_namecve as $item) {
                            if ($value->namecve == $item['namecve']) {
                                $value['total'] = $item['total'];
                                $value['title'] = $item['title'];
                                $host_name[] = $item['title'];
                            }
                        }
                    }
                    $result = array();
                    foreach ($host_name as $element) {
                        $result[$element] = $element;
                    }

                    $response = array(
                        'data' => $CVEMapping,
                        'host_name' => $result,
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

    public function chart_indicators(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $displayType = $data['data']['displayType'];
                    if ($displayType == 'mon') {
                        $currentMonth = 2; //year - current is 2 old is 1
                        $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', $currentMonth)->where('type', 'summary_month')->get();
                        $events = array_fill(0, (int) date('t'), 0);
                        $attribute = array_fill(0, (int) date('t'), 0);
                        foreach ($IndicatorSummaryYear as $value) {
                            $events[$value->month - 1] = $value->event_count;
                            $attribute[$value->month - 1] = $value->attribute_count;
                        }
                        $nameXAxis = array();
                        foreach ($events as $key => $value) {
                            $nameXAxis[$key] = (string) ($key + 1);
                        }
                        $nameYAxis = 'Number (Days)';
                        $nameSeriesEvent = 'Number of Event';
                        $nameSeriesAttribute = 'Number of Attribute';

                    } else {
                        $nameXAxis = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $nameYAxis = 'Number (Months)';
                        $nameSeriesEvent = 'Number of Event';
                        $nameSeriesAttribute = 'Number of Attribute';
                        $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', now()->year)->where('type', 'summary_year')->get();
                        $events = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        $attribute = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        foreach ($IndicatorSummaryYear as $item) {
                            if ($item->month == 1) {
                                $events[0] = $item->event_count;
                                $attribute[0] = $item->attribute_count;
                            } else if ($item->month == 2) {
                                $events[1] = $item->event_count;
                                $attribute[1] = $item->attribute_count;
                            } else if ($item->month == 3) {
                                $events[2] = $item->event_count;
                                $attribute[2] = $item->attribute_count;
                            } else if ($item->month == 4) {
                                $events[3] = $item->event_count;
                                $attribute[3] = $item->attribute_count;
                            } else if ($item->month == 5) {
                                $events[4] = $item->event_count;
                                $attribute[4] = $item->attribute_count;
                            } else if ($item->month == 6) {
                                $events[5] = $item->event_count;
                                $attribute[5] = $item->attribute_count;
                            } else if ($item->month == 7) {
                                $events[6] = $item->event_count;
                                $attribute[6] = $item->attribute_count;
                            } else if ($item->month == 8) {
                                $events[7] = $item->event_count;
                                $attribute[7] = $item->attribute_count;
                            } else if ($item->month == 9) {
                                $events[8] = $item->event_count;
                                $attribute[8] = $item->attribute_count;
                            } else if ($item->month == 10) {
                                $events[9] = $item->event_count;
                                $attribute[9] = $item->attribute_count;
                            } else if ($item->month == 11) {
                                $events[10] = $item->event_count;
                                $attribute[10] = $item->attribute_count;
                            } else if ($item->month == 12) {
                                $events[11] = $item->event_count;
                                $attribute[11] = $item->attribute_count;
                            }
                        }
                    }

                    $response = [
                        'events' => $events,
                        'attribute' => $attribute,
                        'nameXAxis' => $nameXAxis,
                        'nameYAxis' => $nameYAxis,
                        'nameSeriesAttribute' => $nameSeriesAttribute,
                        'nameSeriesEvent' => $nameSeriesEvent,
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

    public function load_chart(Request $request)
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
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];
                    $get_role_custom = $data['data']['get_role_custom'];
                    $model = new CVEMapping;

                    // $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    // if (@$get_role_custom['superadmin'] == 1) {
                    //     if (!$site) {
                    //         $model->get();
                    //         $high = $model->where('severity', '=', 'HIGH')->count();
                    //         $medium = $model->where('severity', '=', 'MEDIUM')->count();
                    //         $critical = $model->where('severity', '=', 'CRITICAL')->count();
                    //         $low = $model->where('severity', '=', 'LOW')->count();
                    //         $none = $model->where('severity', '=', 'NONE')->count();
                    //     } else {
                    //         $model->get();
                    //         $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                    //         $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site_id_m->id)->select('namecve')->get();
                    //         $high = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'HIGH')->count();
                    //         $medium = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'MEDIUM')->count();
                    //         $critical = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'CRITICAL')->count();
                    //         $low = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'LOW')->count();
                    //         $none = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'NONE')->count();
                    //     }
                    // } else {
                    //     if (!$site) {
                    //         $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                    //         $model->get();
                    //         $high = $model->where('severity', '=', 'HIGH')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    //         $medium = $model->where('severity', '=', 'MEDIUM')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    //         $critical = $model->where('severity', '=', 'CRITICAL')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    //         $low = $model->where('severity', '=', 'LOW')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    //         $none = $model->where('severity', '=', 'NONE')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    //     } else {
                    //         $model->get();
                    //         $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                    //         $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                    //         $high = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'HIGH')->count();
                    //         $medium = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'MEDIUM')->count();
                    //         $critical = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'CRITICAL')->count();
                    //         $low = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'LOW')->count();
                    //         $none = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'NONE')->count();
                    //     }
                    // }

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if(@get_role_custom()['superadmin'] == 1) 
                    {
                        
                        if(!$site)
                        {
                            // $model = new CVEMapping;
                            // $model->get();
                            // $high = $model->where('severity', '=', 'HIGH')->count();
                            // $medium = $model->where('severity', '=', 'MEDIUM')->count();
                            // $critical = $model->where('severity', '=', 'CRITICAL')->count();
                            // $low = $model->where('severity', '=', 'LOW')->count();
                            // $none = $model->where('severity', '=', 'NONE')->count();

                            $query = $none = DB::table('summary')
                                        ->where('status', 'Y')
                                        ->whereIn('data_text', ['Information','Low', 'medium', 'high', 'critical'])
                                        ->where('data_key_2', 'Vulnerability')
                                        ->select('data_text', DB::raw('sum(data_value) as data_value'))
                                        ->groupBy('data_text')
                                        ->orderBy('data_text')
                                        ->get();

                            $none = intval($query[2]->data_value);
                            $low = intval($query[3]->data_value);
                            $medium = intval($query[4]->data_value);
                            $high = intval($query[1]->data_value);
                            $critical = intval($query[0]->data_value); 

                        }
                        else
                        {

                            $count= DB::table('summary')->where('data_key', 'dashboardnew')->where('site', $site)->count();

                            if($count > 0){

                                $query =  $none = DB::table('summary')
                                        ->where('site', $site)
                                        ->where('status', 'Y')
                                        ->whereIn('data_text', ['Information','Low', 'medium', 'high', 'critical'])
                                        ->where('data_key_2', 'Vulnerability')
                                        ->select('data_text','data_value')
                                        ->orderBy('data_text')
                                        ->get();
                                    
                                $none = intval($query[2]->data_value);
                                $low = intval($query[3]->data_value);
                                $medium = intval($query[4]->data_value);
                                $high = intval($query[1]->data_value);
                                $critical = intval($query[0]->data_value);
                                
                            } else{
                                $none = 0;
                                $low = 0;
                                $medium = 0;
                                $high = 0;
                                $critical = 0;
                            }

                        }

                    } 
                    else 
                    {
                        if(!$site)
                        {
                            $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                            $model = new CVEMapping;
                            $model->get();
                            $high = $model->where('severity', '=', 'HIGH')->whereIn('namecve', $CVEMappingAssets_name)->count();
                            $medium = $model->where('severity', '=', 'MEDIUM')->whereIn('namecve', $CVEMappingAssets_name)->count();
                            $critical = $model->where('severity', '=', 'CRITICAL')->whereIn('namecve', $CVEMappingAssets_name)->count();
                            $low = $model->where('severity', '=', 'LOW')->whereIn('namecve', $CVEMappingAssets_name)->count();
                            $none = $model->where('severity', '=', 'NONE')->whereIn('namecve', $CVEMappingAssets_name)->count();
                        }
                        else
                        {
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site_id_m->id)->select('namecve')->get();
                            $model = new CVEMapping;
                            $model->get();
                            $high = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'HIGH')->count();
                            $medium = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'MEDIUM')->count();
                            $critical = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'CRITICAL')->count();
                            $low = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'LOW')->count();
                            $none = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'NONE')->count();
                        }
                    }

                    $response = [
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

    public function table_dashboard(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'dashboard') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $sitecode = $data['data']['sitecode'];
                    $pagename = $data['data']['pagename'];
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site_id_arr = $data['data']['site_id_arr'];

                    $site_1 = null;

                    $model = '';
                    $html = '';

                    $date_start_explode = explode(" ", $startDate);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                    // dd($date_start_time_time);

                    $date_end_explode = explode(" ", $endDate);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

                    $SiteSettings = null;

                    if ($sitecode) {
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)->where("code", $sitecode)->first();
                    }

                    $dataCVEMapping = array();
                    $dataR_s_s_news = array();
                    $DataLeakFeed_social = array();
                    $DataLeakFeed_compromised = array();
                    $data_fx_otx_events = array();
                    $WebdefacmentSetting = array();
                    $TransactionScans = array();
                    if (!$pagename || $pagename == 'Vulnerability') {
                        if (!$sitecode) {
                            if (@check_permission_site_custom_api($data['data']['user_id'], 'vulnerabilities')) {
                                $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                                $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                                // if(isset($SiteSettings->id)){
                                $dataCVEMapping = $dataCVEMapping->whereIn("cve_asset.site_id",$site_id_arr);
                                // }
                                $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                                $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                            }
                        } else {
                            if (@check_permission_site_custom_api($data['data']['user_id'], 'vulnerabilities')) {
                                $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$SiteSettings->id)->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                                $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                                // if(isset($SiteSettings->id)){
                                $dataCVEMapping = $dataCVEMapping->where("cve_asset.site_id",$SiteSettings->id);
                                // }
                                $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                                $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                            }
                        }
                    }

                    if (!$pagename || $pagename == 'News') {
                        if (@check_permission_site_custom_api($data['data']['user_id'], 'news')) {
                            // $dataR_s_s_news_th = R_s_s_news::select('title_th as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("news/public/news/detail/",code ,"/th") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            $dataR_s_s_news_th = R_s_s_news::select('title_th as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("news/detail/",code ,"") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                                ->where(function ($query) {
                                    $query->whereNotNull('title_th')->where('title_th', '!=', ''); //detail_th
                                })->get()->toArray();
                            // $dataR_s_s_news_en = R_s_s_news::select('title_en as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("news/public/news/detail/",code ,"/en") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            $dataR_s_s_news_en = R_s_s_news::select('title_en as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("news/detail/",code ,"") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                                ->where(function ($query) {
                                    $query->whereNotNull('title_en')->where('title_en', '!=', ''); //detail_en
                                })->get()->toArray();
                            $dataR_s_s_news = array_merge($dataR_s_s_news_th, $dataR_s_s_news_en);
                        }
                    }

                    if (!$pagename || $pagename == 'Data Leak') {
                        if (@check_permission_site_custom_api($data['data']['user_id'], 'data_leak')) {
                            if ($get_role_custom['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                                if (!$sitecode) {
                                    $DataLeakFeed_social = DataLeakFeedTemp::select('id', 'feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('status', 1)->whereIn('feed_type', 'social', 'darkweb_public')->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                    foreach ($DataLeakFeed_social as $key => $value) {
                                        $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->whereNull('deleted_at')->where('status', 1)->where('data_leak_feed_id', $value["id"])->first();
                                        if ($leak_socail_ref_temps) {
                                            if (isset($SiteSettings->id)) {
                                                $pos = strpos($leak_socail_ref_temps->site_id, $SiteSettings->id . "");
                                                if ($pos === false) {
                                                    unset($DataLeakFeed_social[$key]);
                                                    continue;
                                                }
                                            }
                                            $site = SiteSettings::select('name')->whereIn('id', explode(",", $leak_socail_ref_temps->site_id))->get();
                                            $name_site = '';
                                            foreach ($site as $val) {
                                                $name_site .= $val->name . ' ,';
                                            }
                                            $name_site = rtrim($name_site, " ,");
                                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                                        } else {
                                            unset($DataLeakFeed_social[$key]);
                                        }
                                    }
                                } else {
                                    $DataLeakFeed_social = DataLeakFeedTemp::select('id', 'feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('status', 1)->whereIn('feed_type', 'social', 'darkweb_public')->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                    foreach ($DataLeakFeed_social as $key => $value) {
                                        $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->whereNull('deleted_at')->where('status', 1)->where('data_leak_feed_id', $value["id"])->where('site_id', $SiteSettings->id)->first();
                                        if ($leak_socail_ref_temps) {
                                            if (isset($SiteSettings->id)) {
                                                $pos = strpos($leak_socail_ref_temps->site_id, $SiteSettings->id . "");
                                                if ($pos === false) {
                                                    unset($DataLeakFeed_social[$key]);
                                                    continue;
                                                }
                                            }
                                            $site = SiteSettings::select('name')->whereIn('id', explode(",", $leak_socail_ref_temps->site_id))->get();
                                            $name_site = '';
                                            foreach ($site as $val) {
                                                $name_site .= $val->name . ' ,';
                                            }
                                            $name_site = rtrim($name_site, " ,");
                                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                                        } else {
                                            unset($DataLeakFeed_social[$key]);
                                        }
                                    }
                                }
                            } else {
                                if (!$sitecode) {
                                    $DataLeakFeed_social = DataLeakFeed::select('id', 'feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->whereIn('feel_type', ['social', 'darkweb_public'])->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                    foreach ($DataLeakFeed_social as $key => $value) {
                                        $leak_socail_ref_temps = DataLeakSocialRef::select('site_id')->where('status',1)->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->whereIn('site_id', $site_id_arr)->first();
                                        if ($leak_socail_ref_temps) {
                                            $site = SiteSettings::select('name')->where('id', $leak_socail_ref_temps->site_id)->get();
                                            $name_site = '';
                                            if ($site) {
                                                foreach ($site as $val) {
                                                    $name_site .= $val->name . ' ,';
                                                }
                                            }
                                            $name_site = rtrim($name_site, " ,");
                                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                                        } else {
                                            unset($DataLeakFeed_social[$key]);
                                        }
                                    }
                                } else {
                                    $DataLeakFeed_social = DataLeakFeed::select('id', 'feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->whereIn('feel_type', ['social', 'darkweb_public'])->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                    foreach ($DataLeakFeed_social as $key => $value) {
                                        $leak_socail_ref_temps = DataLeakSocialRef::select('site_id')->where('status',1)->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->where('site_id', $SiteSettings->id)->first();
                                        if ($leak_socail_ref_temps) {
                                            $site = SiteSettings::select('name')->whereIn('id', explode(",", $leak_socail_ref_temps->site_id))->get();
                                            $name_site = '';
                                            foreach ($site as $val) {
                                                $name_site .= $val->name . ' ,';
                                            }
                                            $name_site = rtrim($name_site, " ,");
                                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                                        } else {
                                            unset($DataLeakFeed_social[$key]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // if (!$pagename||$pagename=='Compromised') {
                    //     if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
                    //         $DataLeakFeed_compromised = DataLeakFeed::select('id','source_name as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Compromised" AS pagename'))->whereNull('deleted_at')->where('feel_type','!=', 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    //         foreach ($DataLeakFeed_compromised as $key => $value) {
                    //             $leak_socail_ref = DataLeakSocialRef::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
                    //             if($leak_socail_ref){
                    //                 if(isset($SiteSettings->id)){
                    //                     $pos = strpos($leak_socail_ref->site_id, $SiteSettings->id."");
                    //                     if ($pos === false) {
                    //                         unset($DataLeakFeed_compromised[$key]);
                    //                         continue;
                    //                     }
                    //                 }
                    //                 $site = SiteSettings::select('name')->where('id', $leak_socail_ref->site_id)->first();
                    //                 $name_site='';
                    //                 if($site){
                    //                     $name_site = $site->id;
                    //                 }
                    //                 $DataLeakFeed_compromised[$key]["sitename"] = $name_site;
                    //             }else{
                    //                 unset($DataLeakFeed_compromised[$key]);
                    //             }
                    //         }
                    //     }else{
                    //         $DataLeakFeed_compromised = DataLeakFeed::select('id','source_name as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Compromised" AS pagename'))->whereNull('deleted_at')->where('feel_type', '!=' , 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    //         foreach ($DataLeakFeed_compromised as $key => $value) {
                    //             $leak_socail_ref = DataLeakSocialRef::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
                    //             if($leak_socail_ref){
                    //                 $site = SiteSettings::select('name')->where('id', $leak_socail_ref->site_id)->first();
                    //                 $name_site = '';
                    //                 if($site){
                    //                     $name_site = $site->id;
                    //                 }
                    //                 $DataLeakFeed_compromised[$key]["sitename"] = $name_site;
                    //             }else{
                    //                 unset($DataLeakFeed_compromised[$key]);
                    //             }
                    //         }
                    //     }
                    // }

                    if (!$pagename || $pagename == 'Compromised') {
                        if (@check_permission_site_custom_api($data['data']['user_id'], 'compromised')) {
                            if ($get_role_custom['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                                if (!$sitecode) {
                                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_feed.status', 1)->where('data_leak_socail_ref.status', 1)->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                                    if (isset($SiteSettings->id)) {
                                        $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    }
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                                } else {
                                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_feed.status', 1)->where('data_leak_socail_ref.status', 1)->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                                    // if(isset($SiteSettings->id)){
                                    //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    // }
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                                }
                            } else {
                                if (!$sitecode) {
                                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_feed.status', 1)->where('data_leak_socail_ref.status', 1)->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');

                                    // if(isset($SiteSettings->id)){
                                    //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    // } else {
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->whereIn('site_id', $site_id_arr);
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                                    // }
                                } else {
                                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_feed.status', 1)->where('data_leak_socail_ref.status', 1)->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');

                                    // if(isset($SiteSettings->id)){
                                    //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    // } else {
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                                    // }
                                }
                            }
                        }
                    }

                    if (!$pagename || $pagename == 'Web Defacement') {
                        if (@check_permission_site_custom_api($data['data']['user_id'], 'web_defacement')) {
                            if ($get_role_custom['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                                if (!$sitecode) {
                                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    // $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id');
                                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');

                                    // if(isset($SiteSettings->id)){
                                    //     $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                                    // } else {
                                    // $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                                    // }
                                } else {
                                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');

                                    // if(isset($SiteSettings->id)){
                                    //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    // } else {
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                                    // }
                                }
                            } else {
                                if (!$sitecode) {
                                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement/detail/",fx_webdefacment_setting.code) AS link , "Web Defacement" AS pagename , CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                                    $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();

                                } else {
                                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                                    // if(isset($SiteSettings->id)){
                                    //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                                    // } else {
                                    $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                                    // }
                                }
                            }
                        }
                    }

                    if (!$pagename || $pagename == 'assets') {
                        if (@check_permission_site_custom_api($data['data']['user_id'], 'assets')) {
                            if ($get_role_custom['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                                if (!$sitecode) {
                                    // $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $SiteSettings->code)->first();

                                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.status', 1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                } else {
                                    // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status', 1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                }
                            } else {
                                if (!$sitecode) {
                                    // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.status', 1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                                    $TransactionScans = $TransactionScans->whereIn('transaction_scans.site_id', $site_id_arr);
                                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                } else {
                                    // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status', 1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                                    $TransactionScans = $TransactionScans->where('site.id', $SiteSettings->id)->whereIn('transaction_scans.site_id', $site_id_arr);
                                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->get()->toArray();
                                }
                            }
                        }
                    }

                    // if (!$pagename||$pagename=='Indicators') {
                    //     $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    //     $clientMD = new MongoClient($DB_MONGO_KEY);
                    //     $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

                    //     $options = [
                    //         'allowDiskUse' => TRUE
                    //     ];

                    //     $pipeline = [
                    //         [
                    //             '$match' => [
                    //                 'created_at'  => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)],
                    //             ]
                    //         ],
                    //         [
                    //             '$project' => [
                    //                 '_id' => 0,
                    //                 'sitename' => 'All Site',
                    //                 'content' => '$name',
                    //                 'datetime' => ['$dateToString'=>['format'=>'%Y-%m-%d %H:%M:%S','date'=>'$created_at','timezone'=>'Asia/Bangkok']],
                    //                 'pagename' => 'Indicators',
                    //                 'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                    //             ]
                    //         ]
                    //     ];

                    //     // dd($pipeline);
                    //     $data_fx_otx_events = $col_fx_otx_events->aggregate($pipeline,$options);

                    //     $data_fx_otx_events = $data_fx_otx_events->toArray();

                    // }

                    // $model = [];
                    $model = array_merge(@$dataCVEMapping, @$dataR_s_s_news, @$DataLeakFeed_social, @$DataLeakFeed_compromised, @$WebdefacmentSetting, @$TransactionScans);
                    $dataOut = array();
                    usort($model, function ($a, $b) {
                        $t1 = strtotime($a['datetime']);
                        $t2 = strtotime($b['datetime']);
                        return $t2 - $t1;
                    });

                    $dataOut["data"] = $model;

                    $data_transcation = json_encode($dataOut);
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

    public function cve_assets(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'assets') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];

                    $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray(); 
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->whereIn('site_id',$site_id_active)->where('active', 1)->get();
                    if ($get_role_custom == 1) {
                        if (!$site) {
                            $assets = [];
                            $Assets_data = Assets::where('status', 1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id', $value->site_id)->whereIn('site_id',$site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
                                $Domain_list = [];
                                $IP_List = [];
                                foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                    if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                        //Domain
                                        array_push($Domain_list, $AssetsData_datavalue);
                                    } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                        //IP Asset
                                        array_push($IP_List, $AssetsData_datavalue);
                                    } else {

                                    }
                                }

                                foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                    $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                                    $CPE_List = array();
                                    foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                        array_push($CPE_List, $CPE_Datavalue->result . ' : ' . $CPE_Datavalue->os_type);

                                    }
                                    if (count($Domain_list) == 0) {
                                        $Assets_data_list = array();
                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        // $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = "None";
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = "None";
                                        array_push($assets, $Assets_data_list);
                                    } else {
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $port = '';
                                            $num_main = 1;
                                            $port_all = FXAssetsPort::
                                                            select('port')
                                                            ->where('site_id', $IP_Listvalue->site_id)
                                                            ->where('asset_name', $IP_Listvalue->value)
                                                            ->get(); 
                                            $count_port = count($port_all);
                                            
                                            if($count_port > 0)
                                            {
                                                foreach($port_all as $data_port)
                                                {
                                                    if($count_port == $num_main)
                                                    {
                                                        $port = $port.$data_port->port;
                                                    }
                                                    else
                                                    {
                                                        $port = $port.$data_port->port.',';
                                                    }
                                                    $num_main++;
                                                }
                                            }
                                            else
                                            {
                                                $port = " - ";
                                            }

                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                            // $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            $Assets_data_list['port'] = $port;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $assets = [];
                            $Assets_data = Assets::where('status', 1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id', $site_id_m->id)->whereIn('site_id',$site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
                                $Domain_list = [];
                                $IP_List = [];
                                foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                    if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                        //Domain
                                        array_push($Domain_list, $AssetsData_datavalue);
                                    } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                        //IP Asset
                                        array_push($IP_List, $AssetsData_datavalue);
                                    } else {

                                    }
                                }

                                foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                    $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                                    $CPE_List = array();
                                    foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                        array_push($CPE_List, $CPE_Datavalue->result . ' : ' . $CPE_Datavalue->os_type);

                                    }
                                    if (count($Domain_list) == 0) {
                                        $Assets_data_list = array();
                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        // $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = "None";
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = "None";
                                        array_push($assets, $Assets_data_list);
                                    } else {
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $port = '';
                                            $num_main = 1;
                                            $port_all = FXAssetsPort::
                                                            select('port')
                                                            ->where('site_id', $IP_Listvalue->site_id)
                                                            ->where('asset_name', $IP_Listvalue->value)
                                                            ->get(); 
                                            $count_port = count($port_all);
                                            
                                            if($count_port > 0)
                                            {
                                                foreach($port_all as $data_port)
                                                {
                                                    if($count_port == $num_main)
                                                    {
                                                        $port = $port.$data_port->port;
                                                    }
                                                    else
                                                    {
                                                        $port = $port.$data_port->port.',';
                                                    }
                                                    $num_main++;
                                                }
                                            }
                                            else
                                            {
                                                $port = " - ";
                                            }

                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                            // $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            $Assets_data_list['port'] = $port;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }

                    } else {
                        if (!$site) {
                            $assets = [];

                            $Assets_data = Assets::whereIn('site_id', $site_id_arr)->where('status', 1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id', $value->site_id)->whereIn('site_id',$site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
                                $Domain_list = [];
                                $IP_List = [];
                                foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                    if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                        //Domain
                                        array_push($Domain_list, $AssetsData_datavalue);
                                    } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                        //IP Asset
                                        array_push($IP_List, $AssetsData_datavalue);
                                    } else {

                                    }
                                }

                                foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                    $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                                    $CPE_List = array();
                                    foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                        array_push($CPE_List, $CPE_Datavalue->result . ' : ' . $CPE_Datavalue->os_type);

                                    }
                                    if (count($Domain_list) == 0) {
                                        $Assets_data_list = array();
                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        // $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = "None";
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = "None";
                                        array_push($assets, $Assets_data_list);
                                    } else {
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $port = '';
                                            $num_main = 1;
                                            $port_all = FXAssetsPort::
                                                            select('port')
                                                            ->where('site_id', $IP_Listvalue->site_id)
                                                            ->where('asset_name', $IP_Listvalue->value)
                                                            ->get(); 
                                            $count_port = count($port_all);
                                            
                                            if($count_port > 0)
                                            {
                                                foreach($port_all as $data_port)
                                                {
                                                    if($count_port == $num_main)
                                                    {
                                                        $port = $port.$data_port->port;
                                                    }
                                                    else
                                                    {
                                                        $port = $port.$data_port->port.',';
                                                    }
                                                    $num_main++;
                                                }
                                            }
                                            else
                                            {
                                                $port = " - ";
                                            }

                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                            // $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            $Assets_data_list['port'] = $port;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        } else {
                            $site_id_m = SiteSettings::select('id')->where('code', $site)->first();
                            $assets = [];
                            $Assets_data = Assets::whereIn('site_id', $site_id_arr)->where('site_id', $site_id_m->id)->where('status', 1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id', $site_id_m->id)->whereIn('site_id',$site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
                                $Domain_list = [];
                                $IP_List = [];
                                foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                    if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                        //Domain
                                        array_push($Domain_list, $AssetsData_datavalue);
                                    } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                        //IP Asset
                                        array_push($IP_List, $AssetsData_datavalue);
                                    } else {

                                    }
                                }

                                foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                    $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                                    $CPE_List = array();
                                    foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                        array_push($CPE_List, $CPE_Datavalue->result . ' : ' . $CPE_Datavalue->os_type);

                                    }
                                    if (count($Domain_list) == 0) {
                                        $Assets_data_list = array();
                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        // $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = "None";
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = "None";
                                        array_push($assets, $Assets_data_list);
                                    } else {
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $port = '';
                                            $num_main = 1;
                                            $port_all = FXAssetsPort::
                                                            select('port')
                                                            ->where('site_id', $IP_Listvalue->site_id)
                                                            ->where('asset_name', $IP_Listvalue->value)
                                                            ->get(); 
                                            $count_port = count($port_all);
                                            
                                            if($count_port > 0)
                                            {
                                                foreach($port_all as $data_port)
                                                {
                                                    if($count_port == $num_main)
                                                    {
                                                        $port = $port.$data_port->port;
                                                    }
                                                    else
                                                    {
                                                        $port = $port.$data_port->port.',';
                                                    }
                                                    $num_main++;
                                                }
                                            }
                                            else
                                            {
                                                $port = " - ";
                                            }

                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                            // $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            $Assets_data_list['port'] = $port;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $data_transcation = json_encode($assets);
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
