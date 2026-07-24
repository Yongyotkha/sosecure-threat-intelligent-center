<?php

namespace Modules\DashboardNew\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;



use App\IndicatorSummaryYear;
use Illuminate\Support\Facades\DB;
use Modules\Scans\Entities\Assets;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Yajra\DataTables\DataTables;
use App\Entities\TransactionBatchjob;

use MongoDB\BSON\UTCDateTime;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use MongoDB\Client as MongoClient;
use App\FXAssetsPort;
use App\DataLeakFeed;
use App\DataLeakSocialRef;
use App\R_s_s_news;
use App\DataLeakSocialRefTemp;
use App\DataLeakFeedTemp;
use App\Entities\CPE;
use App\leak_socail_ref_temp;
use Modules\Scans\Entities\AssetsData;
use App\TransactionScans;
use App\TransactionTimeStampScans;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use App\Credentials;

class DashboardNewController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $role_custom = @check_role_custom();
        if (!$role_custom['dashboard']) {
            check_permission403();
        }
        // $menu = array();
        // if(isset($_SESSION["menu"])){
        //     // unset($_SESSION["lastname"]);
        //     $menu = $_SESSION["menu"];
        // }

        // dd($menu[4]->get_menu_sub);

        $get_role_custom_first = @get_role_custom();
        $SiteSettings = '';
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if (@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        } else if (@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        } else if (@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        } else if (@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        } else if (@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }


        $data['site_settings'] = @$SiteSettings;



        $data['page'] = 'dashboard_home';
        // $data['page'] = langapp('dashboard');

        if (@get_role_custom()['superadmin'] == 1) {
            $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
            $data['count_CVEMapping'] = CVEMapping::count();
            $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->get();
            $data['count_compromised'] = DataLeakSocialRef::where("status", '=', 1)
                ->where("deleted_at", '=', null)
                ->where("feel_type", '!=', 'social')
                ->count();
            $data['count_dataLeak'] = DataLeakSocialRef::where("status", '=', 1)
                ->where("deleted_at", '=', null)
                ->where("feel_type", '=', 'social')
                ->count();
        } else {
            $role_custom = @check_role_custom();
            if ($role_custom['assets']) {
                $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->count();
            }
            if ($role_custom['vulnerabilities']) {
                $data['count_CVEMapping'] = CVEMapping::whereIn('site_id', $site_id_arr)->count();
            }
            if ($role_custom['assets']) {
                $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->get();
            }
            if ($role_custom['compromised']) {
                $data['count_compromised'] = DataLeakSocialRef::where("status", '=', 1)
                    ->where("deleted_at", '=', null)
                    ->where("feel_type", '!=', 'social')
                    ->whereIn('site_id', $site_id_arr)
                    ->count();
            }
            if ($role_custom['data_leak']) {
                $data['count_dataLeak'] = DataLeakSocialRef::where("status", '=', 1)
                    ->where("deleted_at", '=', null)
                    ->where("feel_type", '=', 'social')
                    ->whereIn('site_id', $site_id_arr)
                    ->count();
            }
        }


        // $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->get();                            
        // $data['site_settings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();

        return view('dashboardnew::index')->with($data);
    }
    public function detail_asset()
    {
        $data['page'] = langapp('dashboard');
        return view('dashboardnew::view_detail_asset')->with($data);
    }

    public function cve_assets(Request $request)
    {

        $input = $request->all();
        // dd($input);

        $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray();
        // dd(get_role_custom());
        if (Auth::check()) {
            $role_custom = @check_role_custom();
            if ($role_custom['assets']) {
                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if (@get_role_custom()['superadmin'] == 1) {
                    if (!$request->site) {
                        $assets = [];
                        $Assets_data = Assets::where('status', 1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id', $value->site_id)->whereIn('site_id', $site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
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
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                } else {
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::select('port')
                                            ->where('site_id', $IP_Listvalue->site_id)
                                            ->where('asset_name', $IP_Listvalue->value)
                                            ->get();
                                        $count_port = count($port_all);

                                        if ($count_port > 0) {
                                            foreach ($port_all as $data_port) {
                                                if ($count_port == $num_main) {
                                                    $port = $port . $data_port->port;
                                                } else {
                                                    $port = $port . $data_port->port . ',';
                                                }
                                                $num_main++;
                                            }
                                        } else {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    } else {
                        $site_id_m = SiteSettings::select('id')->where('code', $request->site)->whereNull('deleted_at')->where('active', 1)->first();
                        // dd($site_id_m);

                        $assets = [];
                        $Assets_data = Assets::where('status', 1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
                            // dd($AssetsData_data);

                            $Domain_list = [];
                            $IP_List = [];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                // dd($AssetsData_datavalue);
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);
                                } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                } else {
                                }
                            }
                            // dd($Domain_list);
                            // dd($IP_List);

                            foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                                // dd($IP_Listvalue);
                                $CPE_List = array();
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result . ' : ' . $CPE_Datavalue->os_type);
                                }
                                if (count($Domain_list) == 0) {
                                    $Assets_data_list = array();
                                    $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                } else {
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::select('port')
                                            ->where('site_id', $IP_Listvalue->site_id)
                                            ->where('asset_name', $IP_Listvalue->value)
                                            ->get();
                                        $count_port = count($port_all);

                                        if ($count_port > 0) {
                                            foreach ($port_all as $data_port) {
                                                if ($count_port == $num_main) {
                                                    $port = $port . $data_port->port;
                                                } else {
                                                    $port = $port . $data_port->port . ',';
                                                }
                                                $num_main++;
                                            }
                                        } else {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        $Assets_data_list['site'] = $site->name;
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
                    if (!$request->site) {
                        $assets = [];
                        $Assets_data = Assets::where('status', 1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id', $value->site_id)->whereIn('site_id', $site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
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
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                } else {
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::select('port')
                                            ->where('site_id', $IP_Listvalue->site_id)
                                            ->where('asset_name', $IP_Listvalue->value)
                                            ->get();
                                        $count_port = count($port_all);

                                        if ($count_port > 0) {
                                            foreach ($port_all as $data_port) {
                                                if ($count_port == $num_main) {
                                                    $port = $port . $data_port->port;
                                                } else {
                                                    $port = $port . $data_port->port . ',';
                                                }
                                                $num_main++;
                                            }
                                        } else {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    } else {
                        $site_id_m = SiteSettings::select('id')->where('code', $request->site)->whereNull('deleted_at')->where('active', 1)->first();
                        $assets = [];
                        $Assets_data = Assets::where('status', 1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_active)->where('asset_id', $value->id)->where('status', 1)->get();
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
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                } else {
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::select('port')
                                            ->where('site_id', $IP_Listvalue->site_id)
                                            ->where('asset_name', $IP_Listvalue->value)
                                            ->get();
                                        $count_port = count($port_all);

                                        if ($count_port > 0) {
                                            foreach ($port_all as $data_port) {
                                                if ($count_port == $num_main) {
                                                    $port = $port . $data_port->port;
                                                } else {
                                                    $port = $port . $data_port->port . ',';
                                                }
                                                $num_main++;
                                            }
                                        } else {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first();
                                        $Assets_data_list['site'] = $site->name;
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
            }
        }

        // dd($assets);
        //$assets = Assets::select('raw_data','referent',DB::raw('CONCAT("/asset?Search_Link_All=",id) AS link'))->where('status', 1)->get();
        $response = array(
            'error' => '',
            'status_code' => '200',
            'data' => @$assets
        );
        return response()->json($response);
    }

    public function assets_port(Request $request)
    {
    }

    // ============ OLD FUNCTION (COMMENTED OUT - CAUSED 504 TIMEOUT) ============
    // public function count_asset_old(Request $request)
    // {
    //     $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray();
    //     if (Auth::check()) {
    //         if (@get_role_custom()['superadmin'] == 1) {
    //             if (!$request->site) {
    //                 $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
    //                 $dataOut["countAssets"] = 0;
    //                 foreach ($datacountAssets as $key => $value) {
    //                     $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id', $site_id_active)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
    //                     $countfn = count($AssetsData_data);
    //                     if ($countfn == 0) {
    //                         $dataOut["countAssets"]++;
    //                     } else {
    //                         $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
    //                     }
    //                 }
    //             } else {
    //                 $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->site)->first();
    //                 $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->where('assets.site_id', $SiteSettingsfor->id)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
    //                 $dataOut["countAssets"] = 0;
    //                 $dataOut["assetLimit"] = $SiteSettingsfor->asset_limit;
    //                 foreach ($datacountAssets as $key => $value) {
    //                     $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id', $site_id_active)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
    //                     $countfn = count($AssetsData_data);
    //                     if ($countfn == 0) {
    //                         $dataOut["countAssets"]++;
    //                     } else {
    //                         $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
    //                     }
    //                 }
    //             }
    //         } else {
    //             $role_custom = @check_role_custom();
    //             if ($role_custom['assets']) {
    //                 $site_id_arr = @get_role_custom()['site_id_arr'];
    //                 if (!$request->site) {
    //                     $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id', $site_id_arr)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
    //                     $dataOut["countAssets"] = 0;
    //                     foreach ($datacountAssets as $key => $value) {
    //                         $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id', $site_id_active)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
    //                         $countfn = count($AssetsData_data);
    //                         if ($countfn == 0) {
    //                             $dataOut["countAssets"]++;
    //                         } else {
    //                             $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
    //                         }
    //                     }
    //                 } else {
    //                     $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->site)->first();
    //                     $datacountAssets = @Assets::select('assets.id', 'assets_datas.data_type_id', 'assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id', $site_id_arr)->where('assets.site_id', $SiteSettingsfor->id)->whereIn('assets_datas.data_type_id', [5, 6])->where('assets.status', 1)->get();
    //                     $dataOut["countAssets"] = 0;
    //                     $dataOut["assetLimit"] = $SiteSettingsfor->asset_limit;
    //                     foreach ($datacountAssets as $key => $value) {
    //                         $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id', $site_id_active)->whereIn('assets_datas.data_type_id', [1, 4])->get()->toArray();
    //                         $countfn = count($AssetsData_data);
    //                         if ($countfn == 0) {
    //                             $dataOut["countAssets"]++;
    //                         } else {
    //                             $dataOut["countAssets"] = $dataOut["countAssets"] + $countfn;
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     $assets = @$dataOut;
    //     $response = array(
    //         'error' => '',
    //         'status_code' => '200',
    //         'data' => $assets
    //     );
    //     return response()->json($response);
    // }

    // ============ OPTIMIZED VERSION - Fixed 504 Timeout ============
    // Same logic but using 2 queries instead of N+1 queries
    public function count_asset(Request $request)
    {
        $dataOut = ["countAssets" => 0];

        if (Auth::check()) {
            $isSuperAdmin = @get_role_custom()['superadmin'] == 1;
            $role_custom = @check_role_custom();
            
            // If not superadmin and no assets permission, return empty
            if (!$isSuperAdmin && !($role_custom['assets'] ?? false)) {
                return response()->json([
                    'error' => '',
                    'status_code' => '200',
                    'data' => $dataOut
                ]);
            }

            $site_id_arr = $isSuperAdmin ? null : (@get_role_custom()['site_id_arr'] ?? []);
            $targetSiteId = null;

            // Get target site if specified
            if ($request->site) {
                $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->site)->first();
                if ($SiteSettingsfor) {
                    $targetSiteId = $SiteSettingsfor->id;
                    $dataOut["assetLimit"] = $SiteSettingsfor->asset_limit;
                }
            }

            // Build base query from Assets page logic:
            // count uses expanded rows (IP list x host/domain combinations).
            $query = Assets::select('assets.id')
                ->where('assets.status', 1);

            // Apply site filters based on role and request
            if ($targetSiteId) {
                $query->where('assets.site_id', $targetSiteId);
            }
            if (!$isSuperAdmin && $site_id_arr) {
                $query->whereIn('assets.site_id', $site_id_arr);
            }

            $assetIds = $query->distinct()->pluck('assets.id')->toArray();

            if (count($assetIds) > 0) {
                // Domain list on Assets page uses [1,4,14].
                $domainCounts = AssetsData::selectRaw('asset_id, COUNT(*) as domain_count')
                    ->whereIn('asset_id', $assetIds)
                    ->whereIn('data_type_id', [1, 4, 14])
                    ->groupBy('asset_id')
                    ->pluck('domain_count', 'asset_id')
                    ->toArray();

                // IP list on Assets page includes:
                // - data_type_id in [5,6]
                // - OR any other datatype except [1,4,14,5,6,17,13,12]
                $ipCounts = AssetsData::selectRaw('asset_id, COUNT(*) as ip_count')
                    ->whereIn('asset_id', $assetIds)
                    ->where(function ($q) {
                        $q->whereIn('data_type_id', [5, 6])
                            ->orWhereNotIn('data_type_id', [1, 4, 14, 5, 6, 17, 13, 12]);
                    })
                    ->groupBy('asset_id')
                    ->pluck('ip_count', 'asset_id')
                    ->toArray();

                // Same row expansion as Assets page:
                // each IP row is duplicated by number of domains (or 1 when none).
                foreach ($assetIds as $assetId) {
                    $ipCount = (int) ($ipCounts[$assetId] ?? 0);
                    if ($ipCount === 0) {
                        continue;
                    }

                    $domainCount = $domainCounts[$assetId] ?? 0;
                    $multiplier = ($domainCount > 0) ? (int) $domainCount : 1;
                    $dataOut["countAssets"] += ($ipCount * $multiplier);
                }
            }
        }

        $response = array(
            'error' => '',
            'status_code' => '200',
            'data' => $dataOut
        );
        return response()->json($response);
    }

    /**
     * Resolve dashboard site filter (code or numeric id) to site_id.
     */
    private function resolveDashboardSiteId($siteParam): ?int
    {
        if ($siteParam === null || $siteParam === '' || $siteParam === '0' || $siteParam === 0) {
            return null;
        }

        if (is_numeric($siteParam)) {
            return (int) $siteParam;
        }

        $site = SiteSettings::where('code', $siteParam)->whereNull('deleted_at')->first();

        return $site ? $site->id : null;
    }

    /**
     * Base vulnerability query aligned with MonitoringVulnerabilitys chartVulnerabilitys().
     */
    private function buildDashboardVulnerabilityQuery(Request $request)
    {
        $siteId = $this->resolveDashboardSiteId($request->site);

        $model = CVEMapping::query()
            ->join('data_datacve_mapping_assets', 'data_datacve_mapping.namecve', '=', 'data_datacve_mapping_assets.namecve')
            ->join('cve_assets', 'cve_assets.id', '=', 'data_datacve_mapping_assets.cve_asset_id')
            ->where('cve_assets.active', 1)
            ->where('data_datacve_mapping_assets.is_fix', 0);

        $get_role_custom_first = @get_role_custom();
        if (@$get_role_custom_first['superadmin'] != 1) {
            $site_id_arr = @$get_role_custom_first['site_id_arr'] ?? [];
            if (!empty($site_id_arr)) {
                $model->whereIn('data_datacve_mapping_assets.site_id', $site_id_arr);
            }
        }

        if ($siteId) {
            $model->where('data_datacve_mapping_assets.site_id', $siteId);
        }

        return $model;
    }

    public function count_vulnerability(Request $request)
    {
        $CVEMapping = 0;

        if (Auth::check()) {
            $role_custom = @check_role_custom();

            if ($role_custom['vulnerabilities'] ?? false) {
                $prefix = DB::getTablePrefix();
                $stats = $this->buildDashboardVulnerabilityQuery($request)
                    ->selectRaw("COUNT(DISTINCT {$prefix}data_datacve_mapping.namecve) as total")
                    ->first();
                $CVEMapping = (int) ($stats->total ?? 0);
            }
        }

        $response = array(
            'error' => '',
            'status_code' => '200',
            'data' => $CVEMapping
        );
        return response()->json($response);
    }

    public function count_compromised(Request $request)
    {
        if (Auth::check()) {
            $role_custom = @check_role_custom();
            if ($role_custom['compromised']) {
                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if (@get_role_custom()['superadmin'] == 1) {
                    if (!$request->site) {
                        $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('feel_type', ['darkweb', 'webserver', 'server', 'compromise', 'compromised'])->count();
                    } else {
                        $site_id_m = SiteSettings::select('id')->where('code', $request->site)->first();
                        $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->count();
                    }
                } else {
                    if (!$request->site) {
                        $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb', 'webserver', 'server', 'compromise', 'compromised'])->count();
                    } else {
                        $site_id_m = SiteSettings::select('id')->where('code', $request->site)->first();
                        $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])->count();
                    }
                }
            }
        }


        $response = array(
            'error' => '',
            'status_code' => '200',
            'data' => @$DataLeakSocialRef
        );
        return response()->json($response);
    }

    /**
     * Base data leak count query aligned with social-datas_all_site page.
     */
    private function buildDataLeakCountQuery(Request $request)
    {
        $siteId = $this->resolveDashboardSiteId($request->site);
        $get_role = @get_role_custom();

        $site_ids = collect(@$get_role['site_id_arr'])->pluck('site_id')->filter()->values()->toArray();
        $isClientOrSiteClient = (
            (isset($get_role['client']) && (int) $get_role['client'] == 1) ||
            (isset($get_role['site_client']) && (int) $get_role['site_client'] == 1)
        );

        $query = DataLeakSocialRef::query()
            ->whereNull('deleted_at')
            ->whereHas('get_data_leak_feed', function ($q) use ($isClientOrSiteClient) {
                $q->whereNull('deleted_at')
                    ->whereIn('feel_type', ['social', 'darkweb_public', 'surface_web', 'darkweb']);

                if ($isClientOrSiteClient) {
                    $q->where('status', 1);
                }
            });

        if (@$get_role['superadmin'] != 1 && !empty($site_ids)) {
            $query->whereIn('site_id', $site_ids);
        }

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query;
    }

    public function count_data_leak(Request $request)
    {
        $count = 0;

        if (Auth::check()) {
            $role_custom = @check_role_custom();
            if ($role_custom['data_leak'] ?? false) {
                $count = $this->buildDataLeakCountQuery($request)->count();
            }
        }

        return response()->json([
            'error' => '',
            'status_code' => '200',
            'data' => $count,
        ]);
    }

    public function count_vulnerability_host(Request $request)
    {
        $result = [];
        $total_critical = [];
        $total_high = [];
        $total_medium = [];
        $total_low = [];
        $total_infomation = [];

        $role_custom = @check_role_custom();
        if (!($role_custom['vulnerabilities'] ?? false)) {
            return response()->json([
                'error' => '',
                'status_code' => '200',
                'data' => [
                    'data' => [],
                    'host_name' => [],
                    'total_critical' => [],
                    'total_high' => [],
                    'total_medium' => [],
                    'total_low' => [],
                    'total_infomation' => [],
                    'user_id' => @Auth::user()->id,
                    'side_code' => $request->site,
                ],
            ]);
        }

        if (Auth::check()) {
            $prefix = DB::getTablePrefix();
            $query = $this->buildDashboardVulnerabilityQuery($request);

            $rows = $query
                ->selectRaw("
                    {$prefix}cve_assets.title AS title,
                    COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'CRITICAL' THEN {$prefix}data_datacve_mapping.namecve END) AS status_critical,
                    COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'HIGH' THEN {$prefix}data_datacve_mapping.namecve END) AS status_high,
                    COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'MEDIUM' THEN {$prefix}data_datacve_mapping.namecve END) AS status_medium,
                    COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'LOW' THEN {$prefix}data_datacve_mapping.namecve END) AS status_low,
                    COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'NONE' OR {$prefix}data_datacve_mapping.severity = '' OR {$prefix}data_datacve_mapping.severity IS NULL THEN {$prefix}data_datacve_mapping.namecve END) AS status_infomation
                ")
                ->groupBy('cve_assets.vendor', 'cve_assets.title')
                ->orderByRaw("COUNT(DISTINCT {$prefix}data_datacve_mapping.namecve) DESC")
                ->limit(10)
                ->get();

            foreach ($rows as $host_name) {
                $result[] = $host_name->title;
                $total_critical[] = (int) $host_name->status_critical;
                $total_high[] = (int) $host_name->status_high;
                $total_medium[] = (int) $host_name->status_medium;
                $total_low[] = (int) $host_name->status_low;
                $total_infomation[] = (int) $host_name->status_infomation;
            }
        }

        return response()->json([
            'error' => '',
            'status_code' => '200',
            'data' => [
                'data' => [],
                'host_name' => $result,
                'total_critical' => $total_critical,
                'total_high' => $total_high,
                'total_medium' => $total_medium,
                'total_low' => $total_low,
                'total_infomation' => $total_infomation,
                'user_id' => @Auth::user()->id,
                'side_code' => $request->site,
            ],
        ]);
    }

    public function chart_indicators(Request $request)
    {

        $role_custom = @check_role_custom();
        if ($role_custom['indicators']) {
            if ($request->displayType == 'mon') {
                $currentMonth = 2; //year - current is 2 old is 1
                $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', $currentMonth)->where('type', 'summary_month')->get();
                $events = array_fill(0, (int)date('t'), 0);
                $attribute = array_fill(0, (int)date('t'), 0);
                foreach ($IndicatorSummaryYear  as $value) {
                    $events[$value->month - 1] = $value->event_count;
                    $attribute[$value->month - 1] = $value->attribute_count;
                }
                $nameXAxis = array();
                foreach ($events as $key => $value) {
                    $nameXAxis[$key] = (string)($key + 1);
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
                foreach ($IndicatorSummaryYear as $data) {
                    if ($data->month == 1) {
                        $events[0] = $data->event_count;
                        $attribute[0] = $data->attribute_count;
                    } else if ($data->month == 2) {
                        $events[1] = $data->event_count;
                        $attribute[1] = $data->attribute_count;
                    } else if ($data->month == 3) {
                        $events[2] = $data->event_count;
                        $attribute[2] = $data->attribute_count;
                    } else if ($data->month == 4) {
                        $events[3] = $data->event_count;
                        $attribute[3] = $data->attribute_count;
                    } else if ($data->month == 5) {
                        $events[4] = $data->event_count;
                        $attribute[4] = $data->attribute_count;
                    } else if ($data->month == 6) {
                        $events[5] = $data->event_count;
                        $attribute[5] = $data->attribute_count;
                    } else if ($data->month == 7) {
                        $events[6] = $data->event_count;
                        $attribute[6] = $data->attribute_count;
                    } else if ($data->month == 8) {
                        $events[7] = $data->event_count;
                        $attribute[7] = $data->attribute_count;
                    } else if ($data->month == 9) {
                        $events[8] = $data->event_count;
                        $attribute[8] = $data->attribute_count;
                    } else if ($data->month == 10) {
                        $events[9] = $data->event_count;
                        $attribute[9] = $data->attribute_count;
                    } else if ($data->month == 11) {
                        $events[10] = $data->event_count;
                        $attribute[10] = $data->attribute_count;
                    } else if ($data->month == 12) {
                        $events[11] = $data->event_count;
                        $attribute[11] = $data->attribute_count;
                    }
                }
            }
        } else {
            $events = [];
            $attribute = [];
            $nameXAxis = [];
            $nameYAxis = '';
            $nameSeriesAttribute = '';
            $nameSeriesEvent = '';
        }


        $response = array(
            'error' => '',
            'status_code' => '200',
            'data' => [
                'events' => $events,
                'attribute' => $attribute,
                'nameXAxis' => $nameXAxis,
                'nameYAxis' => $nameYAxis,
                'nameSeriesAttribute' => $nameSeriesAttribute,
                'nameSeriesEvent' => $nameSeriesEvent,

            ]
        );
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('dashboardnew::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('dashboardnew::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('dashboardnew::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function table_dashboard(Request $request)
    {



        $model = '';
        $html = '';

        $date_start_explode = explode(" ", $request->startDate);
        $date_start_date = @$date_start_explode[0];
        $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
        // dd($date_start_time);
        $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
        // dd($date_start_date_format);
        $date_start_time_time = date("H:i", strtotime($date_start_time));
        $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
        // dd($date_start_time_time);

        $date_end_explode = explode(" ", $request->endDate);
        $date_end_date = @$date_end_explode[0];
        $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
        // dd($date_end_time);
        $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        $date_end_time_time = date("H:i", strtotime($date_end_time));
        $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null);
        if ($request->sitecode) {
            $SiteSettings = $SiteSettings->where("code", $request->sitecode)->first();
        }
        $site_id_arr = @get_role_custom()['site_id_arr'];


        $dataCVEMapping = array();
        $dataR_s_s_news = array();
        $DataLeakFeed_social = array();
        $DataLeakFeed_compromised = array();
        $data_fx_otx_events = array();
        $WebdefacmentSetting = array();
        $TransactionScans = array();
        $role_custom = @check_role_custom();
        if (!$request->pagename || $request->pagename == 'Vulnerability') {
            // $role_custom = @check_role_custom();
            if ($role_custom['vulnerabilities']) {
                if (@get_role_custom()['superadmin'] == 1) {
                    if (!$request->sitecode) {
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))
                            ->whereBetween('data_datacve_mapping.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        
                        $dataCVEMapping = $dataCVEMapping->join('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->where('site.active','1')->groupby(['cve_asset.namecve', 'cve_asset.site_id'])->orderBy('data_datacve_mapping.created_at', 'desc')->take(50)->get()->toArray();

                        
                    } else {
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))
                            ->whereBetween('data_datacve_mapping.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        
                        $dataCVEMapping = $dataCVEMapping->join('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        // Filter by the specific requested site
                        $dataCVEMapping = $dataCVEMapping->where("cve_asset.site_id", $SiteSettings->id);
                        
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->where('site.active','1')->groupby(['cve_asset.namecve', 'cve_asset.site_id'])->orderBy('data_datacve_mapping.created_at', 'desc')->take(50)->get()->toArray();
                    }
                } else {
                    if (!$request->sitecode) {
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))
                            ->whereBetween('data_datacve_mapping.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        
                        $dataCVEMapping = $dataCVEMapping->join('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        // Filter by user allowed sites
                        $dataCVEMapping = $dataCVEMapping->whereIn("cve_asset.site_id", $site_id_arr);
                        
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->where('site.active','1')->groupby(['cve_asset.namecve', 'cve_asset.site_id'])->orderBy('data_datacve_mapping.created_at', 'desc')->take(50)->get()->toArray();

                    } else {
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))
                            ->whereBetween('data_datacve_mapping.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        
                        $dataCVEMapping = $dataCVEMapping->join('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        // Filter by specific site AND user allowed sites
                        $dataCVEMapping = $dataCVEMapping->where("cve_asset.site_id", $SiteSettings->id)->whereIn("cve_asset.site_id", $site_id_arr);
                        
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->where('site.active','1')->groupby(['cve_asset.namecve', 'cve_asset.site_id'])->orderBy('data_datacve_mapping.created_at', 'desc')->take(50)->get()->toArray();
                    }
                }
            }
        }

        if (!$request->pagename || $request->pagename == 'News') {
            // $role_custom = @check_role_custom();
            if ($role_custom['news']) {
                $dataR_s_s_news_th = R_s_s_news::select('title_th as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/news/public/news/detail/",code ,"/th") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                    ->where(function ($query) {
                        $query->whereNotNull('title_th')->where('title_th', '!=', ''); //detail_th   
                    })->orderBy('created_at', 'desc')->take(50)->get()->toArray();
                $dataR_s_s_news_en = R_s_s_news::select('title_en as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/news/public/news/detail/",code ,"/en") AS link , "News" AS pagename'))->whereBetween('created_at', array($date_start_datetime_format, $date_end_datetime_format))
                    ->where(function ($query) {
                        $query->whereNotNull('title_en')->where('title_en', '!=', ''); //detail_en
                    })->orderBy('created_at', 'desc')->take(50)->get()->toArray();
                $dataR_s_s_news = array_merge($dataR_s_s_news_th, $dataR_s_s_news_en);
            }
        }

        if (!$request->pagename || $request->pagename == 'Data Leak') {
            $prefix = DB::connection()->getTablePrefix();
            // $role_custom = @check_role_custom();
            if ($role_custom['data_leak']) {
                if (@get_role_custom()['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                    if (!$request->sitecode) {
                        $DataLeakFeed_social = DataLeakFeedTemp::select('data_leak_feed_temp.id', DB::raw('SUBSTRING(feedcontent, 1, 255) as content'), 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))
                            ->whereNull('data_leak_feed_temp.deleted_at')

                            ->where('data_leak_feed_temp.status', 1)
                            ->whereIn('data_leak_feed_temp.feed_type', ['social', 'darkweb_public'])
                            ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            ->orderBy('data_leak_feed_temp.created_at', 'desc')
                            ->take(50)
                            ->get()
                            ->toArray();

                         // Optimization: Batch Fetch
                        $feedIds = array_column($DataLeakFeed_social, 'id');
                         $refs = [];
                        $sites = [];
                        if (!empty($feedIds)) {
                            $refs = leak_socail_ref_temp::whereIn('data_leak_feed_id', $feedIds)->where('status', 1)->whereNull('deleted_at')->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = [];
                            foreach ($refs as $ref) {
                                if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); }
                            }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }

                        foreach ($DataLeakFeed_social as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                $ref = $refs[$value['id']];
                                 if (isset($SiteSettings->id)) {
                                     $pos = strpos($ref->site_id, $SiteSettings->id . ""); if ($pos === false) { unset($DataLeakFeed_social[$key]); continue; }
                                }
                                $siteIds = explode(',', $ref->site_id); $name_site = '';
                                foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                $DataLeakFeed_social[$key]["sitename"] = rtrim($name_site, " ,");
                            } else { unset($DataLeakFeed_social[$key]); }
                        }
                    } else {
                        // User specified a Site Code -> Filter strictly
                        $DataLeakFeed_social = DataLeakFeedTemp::select('data_leak_feed_temp.id', DB::raw('SUBSTRING(feedcontent, 1, 255) as content'), 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))
                            ->join('data_leak_socail_ref_temp as ref_temp', 'data_leak_feed_temp.id', '=', 'ref_temp.data_leak_feed_id')
                            ->whereNull('data_leak_feed_temp.deleted_at')

                            ->where('data_leak_feed_temp.status', 1)
                            ->whereIn('feed_type', ['social', 'darkweb_public'])
                            ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            // Server-side filter using FIND_IN_SET for CSV column
                            ->whereRaw("FIND_IN_SET(?, " . $prefix . "ref_temp.site_id)", [$SiteSettings->id])
                            ->orderBy('data_leak_feed_temp.created_at', 'desc')
                            ->take(50)
                            ->get()
                            ->toArray();
                        
                        // Just fetch site name for display since we already filtered
                         $feedIds = array_column($DataLeakFeed_social, 'id');
                         $siteNameMap = [];
                         if(!empty($feedIds)) {
                             // Re-fetch refs to get full site list for display purposes if needed, otherwise just current site
                              $refs = leak_socail_ref_temp::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                               $allSiteIds = [];
                            foreach ($refs as $ref) {
                                if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); }
                            }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                         }

                        foreach ($DataLeakFeed_social as $key => $value) {
                             // Re-attach site names logic for consistent display
                              // Since we joined, we know it's valid, but we need the full list of sites for display
                               // We can just use the $sites map built above
                               if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_social[$key]["sitename"] = rtrim($name_site, " ,");
                               }
                        }
                    }
                } else {
                    if (!$request->sitecode) {
                        // User Allowed Sites (Array) -> Filter by ANY match
                        $DataLeakFeed_social = DataLeakFeedTemp::select('data_leak_feed_temp.id', DB::raw('SUBSTRING(feedcontent, 1, 255) as content'), 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))
                             ->join('data_leak_socail_ref_temp as ref_temp', 'data_leak_feed_temp.id', '=', 'ref_temp.data_leak_feed_id')
                            ->whereNull('data_leak_feed_temp.deleted_at')

                            ->where('data_leak_feed_temp.status', 1)
                            ->whereIn('data_leak_feed_temp.feed_type', ['social', 'darkweb_public'])
                            ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            // Start Complex Filter: (site_id IN (...)) logic for CSV
                            ->where(function($query) use ($site_id_arr, $SiteSettings, $prefix) {
                                 foreach ($site_id_arr as $siteId) {
                                     $query->orWhereRaw("FIND_IN_SET(?, " . $prefix . "ref_temp.site_id)", [$siteId]);
                                 }
                                 // Ensure we also cover the case where the site list might be just the current site setting if not fully populated in array
                                 if (isset($SiteSettings->id)) {
                                     $query->orWhereRaw("FIND_IN_SET(?, " . $prefix . "ref_temp.site_id)", [$SiteSettings->id]);
                                 }
                            })

                            ->orderBy('data_leak_feed_temp.created_at', 'desc')
                            ->take(50)
                            ->get()
                            ->toArray();

                        // Optimization: Batch Fetch Display Names
                        $feedIds = array_column($DataLeakFeed_social, 'id');
                        $sites = [];
                        if (!empty($feedIds)) {
                            $refs = leak_socail_ref_temp::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = [];
                            foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }

                        foreach ($DataLeakFeed_social as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_social[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }

                    } else {
                        // Strict specific site filter for User
                         $DataLeakFeed_social = DataLeakFeedTemp::select('data_leak_feed_temp.id', DB::raw('SUBSTRING(feedcontent, 1, 255) as content'), 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))
                             ->join('data_leak_socail_ref_temp as ref_temp', 'data_leak_feed_temp.id', '=', 'ref_temp.data_leak_feed_id')
                            ->whereNull('data_leak_feed_temp.deleted_at')

                            ->where('data_leak_feed_temp.status', 1)
                            ->whereIn('data_leak_feed_temp.feed_type', ['social', 'darkweb_public'])
                            ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                             // Combine: Must be in requested site AND that site must be allowed
                             ->whereRaw("FIND_IN_SET(?, " . $prefix . "ref_temp.site_id)", [$SiteSettings->id])
                             ->where(function($query) use ($site_id_arr, $SiteSettings) {
                                 // Redundant if SiteSettings->id is already in site_id_arr (which it should be), but safe
                                 if(!in_array($SiteSettings->id, (array)$site_id_arr)) {
                                     // This case technically shouldn't happen in valid flow, but failsafe
                                      $query->whereRaw("0=1"); 
                                 }
                            })
                            ->orderBy('data_leak_feed_temp.created_at', 'desc')
                            ->take(50)
                            ->get()
                            ->toArray();
                        
                        // Optimization: Batch Fetch Display Names
                        $feedIds = array_column($DataLeakFeed_social, 'id');
                        $sites = [];
                        if (!empty($feedIds)) {
                            $refs = DataLeakSocialRef::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = [];
                            foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }
                         foreach ($DataLeakFeed_social as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_social[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }
                    }
                }
            }
        }


        // if (!$request->pagename||$request->pagename=='Compromised') {
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

        if (!$request->pagename || $request->pagename == 'Compromised') {
             if ($role_custom['compromised']) {
                 if (@get_role_custom()['superadmin'] == 1) { 
                    if (!$request->sitecode) {
                        $DataLeakFeed_compromised = DataLeakFeedTemp::select('data_leak_feed_temp.id', 'data_leak_feed_temp.feedlink as content', 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeed_darkweb") AS link , "Compromised" AS pagename'))
                            ->whereNull('data_leak_feed_temp.deleted_at')
                            //->where('data_leak_socail_ref_temp.status', 1) // Cannot query this easily before joining, but we need to verify refs exist?
                            // Actually we should filter by feed existence.
                             ->whereIn('data_leak_feed_temp.feed_type', ['darkweb', 'webserver', 'compromise', 'compromised'])
                             ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                             ->orderBy('data_leak_feed_temp.created_at', 'desc')
                             ->take(50)->get()->toArray();

                        // Batch Fetch Refs
                        $feedIds = array_column($DataLeakFeed_compromised, 'id');
                        $refs = []; $sites = [];
                        if (!empty($feedIds)) {
                            $refs = leak_socail_ref_temp::whereIn('data_leak_feed_id', $feedIds)->where('status', 1)->whereNull('deleted_at')->get()->keyBy('data_leak_feed_id');
                            $allSiteIds = [];
                            foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }
                         foreach ($DataLeakFeed_compromised as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_compromised[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }

                    } else {
                            // Filter by specific site code
                             $DataLeakFeed_compromised = DataLeakFeedTemp::select('data_leak_feed_temp.id', 'data_leak_feed_temp.feedlink as content', 'data_leak_feed_temp.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeed_darkweb") AS link , "Compromised" AS pagename'))
                                ->join('data_leak_socail_ref_temp as ref_temp', 'data_leak_feed_temp.id', '=', 'ref_temp.data_leak_feed_id')
                                ->whereNull('data_leak_feed_temp.deleted_at')
                                 ->where('ref_temp.status', 1)
                                 ->whereIn('data_leak_feed_temp.feed_type', ['darkweb', 'webserver', 'compromise', 'compromised'])
                                 ->whereBetween('data_leak_feed_temp.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                                 ->whereRaw("FIND_IN_SET(?, " . DB::connection()->getTablePrefix() . "ref_temp.site_id)", [$SiteSettings->id])
                             ->orderBy('data_leak_feed_temp.created_at', 'desc')
                             ->take(50)->get()->toArray();
                         
                         // Batch Fetch Names
                        $feedIds = array_column($DataLeakFeed_compromised, 'id');
                        $refs = []; $sites = [];
                        if (!empty($feedIds)) {
                            $refs = leak_socail_ref_temp::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = []; foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }
                         foreach ($DataLeakFeed_compromised as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_compromised[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }
                    }
                 } else {
                     if (!$request->sitecode) {
                         // Filter by Allowed Sites
                        $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))
                            ->join('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')
                            ->whereNull('data_leak_feed.deleted_at')
                            ->where('data_leak_socail_ref.status', 1)
                            ->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])
                            ->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            ->where(function($query) use ($site_id_arr, $SiteSettings, $prefix) {
                                 foreach ($site_id_arr as $siteId) {
                                     $query->orWhereRaw("FIND_IN_SET(?, " . $prefix . "data_leak_socail_ref.site_id)", [$siteId]);
                                 }
                                  if (isset($SiteSettings->id)) {
                                     $query->orWhereRaw("FIND_IN_SET(?, " . $prefix . "data_leak_socail_ref.site_id)", [$SiteSettings->id]);
                                 }
                            })
                            ->orderBy('data_leak_feed.created_at', 'desc')
                            ->take(50)->get()->toArray();

                         // Batch Fetch Names
                        $feedIds = array_column($DataLeakFeed_compromised, 'id');
                         $refs = []; $sites = [];
                        if (!empty($feedIds)) {
                             // Fix: Use correct model for Prod vs Temp? Code used DataLeakFeed (Prod) so Ref should be DataLeakSocialRef (Prod)
                             $refs = DataLeakSocialRef::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = []; foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }
                         foreach ($DataLeakFeed_compromised as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_compromised[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }

                     } else {
                         // Filter strictly by requested site
                         $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', DB::raw(' "" as sitename,CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))
                            ->join('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')
                            ->whereNull('data_leak_feed.deleted_at')
                            ->where('data_leak_socail_ref.status', 1)
                            ->whereIn('data_leak_feed.feel_type', ['darkweb', 'webserver', 'compromise', 'compromised'])
                            ->whereBetween('data_leak_feed.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                            ->whereRaw("FIND_IN_SET(?, " . $prefix . "data_leak_socail_ref.site_id)", [$SiteSettings->id])
                             ->where(function($query) use ($site_id_arr, $SiteSettings) {
                                 // Add extra safety check like before
                                 if(!in_array($SiteSettings->id, (array)$site_id_arr)) {
                                      $query->whereRaw("0=1"); 
                                 }
                            })
                            ->orderBy('data_leak_feed.created_at', 'desc')
                            ->take(50)->get()->toArray();

                          // Batch Fetch Names
                        $feedIds = array_column($DataLeakFeed_compromised, 'id');
                         $refs = []; $sites = [];
                        if (!empty($feedIds)) {
                             $refs = DataLeakSocialRef::whereIn('data_leak_feed_id', $feedIds)->get()->keyBy('data_leak_feed_id');
                             $allSiteIds = []; foreach ($refs as $ref) { if ($ref->site_id) { $ids = explode(',', $ref->site_id); foreach ($ids as $id) $allSiteIds[] = trim($id); } }
                            $allSiteIds = array_unique($allSiteIds);
                             if (!empty($allSiteIds)) $sites = SiteSettings::whereIn('id', $allSiteIds)->pluck('name', 'id')->toArray();
                        }
                         foreach ($DataLeakFeed_compromised as $key => $value) {
                             if (isset($refs[$value['id']])) {
                                  $ref = $refs[$value['id']];
                                  $siteIds = explode(',', $ref->site_id); $name_site = '';
                                  foreach ($siteIds as $sid) { $sid = trim($sid); if (isset($sites[$sid])) $name_site .= $sites[$sid] . ' ,'; }
                                  $DataLeakFeed_compromised[$key]["sitename"] = rtrim($name_site, " ,");
                             }
                        }
                     }
                 }
             }

        }

        if (!$request->pagename || $request->pagename == 'Web Defacement') {
            // $role_custom = @check_role_custom();
            if ($role_custom['web_defacement']) {
                if (@get_role_custom()['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                    if (!$request->sitecode) {
                        $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement/detail/",fx_webdefacment_setting.code) AS link , "Web Defacement" AS pagename , CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        // $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id');
                        $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                        //$WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                        $WebdefacmentSetting = $WebdefacmentSetting->orderBy('webdefacment_setting.last_check', 'desc')->take(50)->get()->toArray();
                        // }
                    } else {
                        $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                        $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                        $WebdefacmentSetting = $WebdefacmentSetting->orderBy('webdefacment_setting.last_check', 'desc')->take(50)->get()->toArray();
                    }
                } else {
                    if (!$request->sitecode) {
                        $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement/detail/",fx_webdefacment_setting.code) AS link , "Web Defacement" AS pagename , CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                        $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                        $WebdefacmentSetting = $WebdefacmentSetting->orderBy('webdefacment_setting.last_check', 'desc')->take(50)->get()->toArray();
                    } else {
                        $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active', 1)->where('webdefacment_setting.status_add', 1)->whereBetween('webdefacment_setting.created_at', array($date_start_datetime_format, $date_end_datetime_format));
                        $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                        // if(isset($SiteSettings->id)){
                        //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                        // } else {
                        $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                        $WebdefacmentSetting = $WebdefacmentSetting->orderBy('webdefacment_setting.last_check', 'desc')->take(50)->get()->toArray();
                        // }
                    }
                }
            }
        }

        if (!$request->pagename || $request->pagename == 'assets') {
            // $role_custom = @check_role_custom();
            if ($role_custom['assets']) {
                if (@get_role_custom()['superadmin'] == 1) { //|| @get_role_custom()['site_admin'] == 1
                    if (!$request->sitecode) {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $SiteSettings->code)->first();

                        $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.status', 1)->where('transaction_scans.module', '!=', 'sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                        $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                        $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                        $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->take(50)->get()->toArray();
                    } else {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                        $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status', 1)->where('transaction_scans.module', '!=', 'sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                        $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                        $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                        $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->take(50)->get()->toArray();
                    }
                } else {
                    if (!$request->sitecode) {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                        $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status', 1)->where('transaction_scans.module', '!=', 'sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                        $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                        $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                        $TransactionScans = $TransactionScans->whereIn('site_id', $site_id_arr);
                        $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->take(50)->get()->toArray();
                    } else {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                        $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code', 'transaction_scans.updated_at as datetime', 'site.name as sitename', 'site.id as site_id', DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status', 1)->where('transaction_scans.module', '!=', 'sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                        $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                        $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                        $TransactionScans = $TransactionScans->where('site.id', $SiteSettings->id)->whereIn('transaction_scans.site_id', $site_id_arr);
                        $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at', array($date_start_datetime_format, $date_end_datetime_format))->take(50)->get()->toArray();
                    }
                }
            }
        }
        /*
            if (!$request->pagename||$request->pagename=='Indicators') {
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

                $options = [
                    'allowDiskUse' => TRUE
                ];

                $pipeline = [
                    [
                        '$match' => [
                            'created_at'  => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)],
                        ]
                    ],
                    [
                        '$project' => [
                            '_id' => 0,
                            'sitename' => 'All Site',
                            'content' => '$name',
                            'datetime' => ['$dateToString'=>['format'=>'%Y-%m-%d %H:%M:%S','date'=>'$created_at','timezone'=>'Asia/Bangkok']],
                            'pagename' => 'Indicators',
                            'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                        ]
                    ]
                ];

                // dd($pipeline);
                $data_fx_otx_events = $col_fx_otx_events->aggregate($pipeline,$options);

                $data_fx_otx_events = $data_fx_otx_events->toArray();

            }
        */
        // Credential Leak Section (credential_leak_ref)
        $DataCredentials = [];
        if (!$request->pagename || $request->pagename == 'Credential Leak') {
            
            // Check permission. Using 'compromised' as it's related, or if there's a specific one.
            // Assuming 'compromised' for now as per previous context, or maybe 'data_leak'. 
            // Let's stick to 'compromised' or 'data_leak' if 'credential_leak' doesn't exist in role_custom. 
            // The user mentioned "add another one separate from data leak", so likely it shares similar permissions or has its own.
            // Since I don't see a specific 'credential_leak' permission in the code I viewed earlier, I will rely on 'compromised' or 'data_leak'.
            // However, to be safe and consistent with "Compromised" section fixes:
            if ($role_custom['compromised'] || $role_custom['data_leak']) { // Allow if either is true, or just compromised? User said "separate from data leak".
                 // Let's use 'compromised' as user associated it with "Credential Leak" in previous prompts.
                 
                 $query = DB::table('credential_leak_ref')
                    ->select('credential_leak_ref.content', 'credential_leak_ref.created_at as datetime', 'site.name as sitename', 'site.code as site_code', DB::raw('"Credential Leak" AS pagename'), 'credential_leak_ref.id')
                    ->join('site', 'credential_leak_ref.site_id', '=', 'site.id')
                    ->where('site.deleted_at', null)
                    ->where('site.active', 1)
                     ->where('credential_leak_ref.status', 1) // Assuming status 1 is active/visible
                     ->whereBetween('credential_leak_ref.created_at', array($date_start_datetime_format, $date_end_datetime_format))
                     ->orderBy('credential_leak_ref.created_at', 'desc');

                 if (@get_role_custom()['superadmin'] == 1) {
                    if ($request->sitecode) {
                        $query->where('site.id', $SiteSettings->id);
                    }
                 } else {
                     if (!$request->sitecode) {
                        $query->whereIn('site.id', $site_id_arr);
                     } else {
                        $query->where('site.id', $SiteSettings->id)->whereIn('site.id', $site_id_arr);
                     }
                 }

                 // Add link & Format Content
                 $DataCredentials = $query->take(50)->get();

                 $DataCredentials = $DataCredentials->map(function ($item) {
                     // Determine link
                     $item->link = "/data_leak_feed"; 
                     unset($item->site_code);

                     // Format content: Extract Email/Password from JSON
                     $decoded = json_decode($item->content, true);
                     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                         $email = strip_tags($decoded['email'] ?? '-');
                         $password = strip_tags($decoded['password'] ?? '-');
                         $item->content = "Email: " . $email . " | Password: " . $password;
                     } else {
                        // Fallback: strip tags from raw content if not valid JSON
                        $item->content = strip_tags($item->content);
                        // Remove newlines
                        $item->content = str_replace(array("\r", "\n"), '', $item->content);
                     }
                     
                     // Cast to array to avoid stdClass error in sort
                     return (array) $item;
                 });
                 $DataCredentials = $DataCredentials->toArray();
            }
        }

        $model = array_merge(@$dataCVEMapping, @$dataR_s_s_news, @$DataLeakFeed_social, @$DataLeakFeed_compromised, @$DataCredentials, @$WebdefacmentSetting, @$TransactionScans);
        $dataOut = array();
        usort($model, function ($a, $b) {
            $t1 = strtotime($a['datetime']);
            $t2 = strtotime($b['datetime']);
            return $t2 - $t1;
        });
       
        $dataOut["data"] =  $model;
        return response()->json($dataOut);
    }

    public function load_chart(Request $request)
    {
        $high = $medium = $critical = $low = $none = 0;

        if (Auth::check()) {
            $role_custom = @check_role_custom();
            if ($role_custom['vulnerabilities'] ?? false) {
                $prefix = DB::getTablePrefix();
                $stats = $this->buildDashboardVulnerabilityQuery($request)
                    ->selectRaw("
                        COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'HIGH' THEN {$prefix}data_datacve_mapping.namecve END) as high,
                        COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'MEDIUM' THEN {$prefix}data_datacve_mapping.namecve END) as medium,
                        COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'CRITICAL' THEN {$prefix}data_datacve_mapping.namecve END) as critical,
                        COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'LOW' THEN {$prefix}data_datacve_mapping.namecve END) as low,
                        COUNT(DISTINCT CASE WHEN {$prefix}data_datacve_mapping.severity = 'NONE' OR {$prefix}data_datacve_mapping.severity = '' OR {$prefix}data_datacve_mapping.severity IS NULL THEN {$prefix}data_datacve_mapping.namecve END) as none
                    ")
                    ->first();

                $high = (int) ($stats->high ?? 0);
                $medium = (int) ($stats->medium ?? 0);
                $critical = (int) ($stats->critical ?? 0);
                $low = (int) ($stats->low ?? 0);
                $none = (int) ($stats->none ?? 0);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'count_high' => $high,
                'count_medium' => $medium,
                'count_critical' => $critical,
                'count_low' => $low,
                'count_none' => $none,
            ]);
        }
    }
}
