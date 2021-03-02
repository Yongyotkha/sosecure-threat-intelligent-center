<?php

namespace App\Http\Controllers\Api;

use App\DataLeakFeed;
use App\R_s_s_news;
use App\Traits\Taggable;
use DB;
use Illuminate\Http\Request;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use MongoDB\Client as MongoClient;

class ApiSearchController extends ApiController
{
    public function searchAll(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
           
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'search'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    
                    // $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    // if($auth_site['status_code'] !== '200'){
                    //     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    // }
                    
                    $data21['dataSearch'] = array();
                    $limit = 100;
                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $clientMD = new MongoClient($DB_MONGO_KEY);
                    $keyword = '%' . $data['data']['keyword'] . '%';
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site_id_arr = @$get_role_custom['site_id_arr'];
                    if(check_permission_site_custom_api($data['data']['user_id'],'news') == 1){
                        
                        $dataWait['queryData'] = R_s_s_news::select('id', 'title_th as name', 'detail_th as content', DB::raw('CONCAT("/public/news/detail/",code ,"/th") AS link'))->where('title_th', 'LIKE', $keyword);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $dataWait2['queryData'] = R_s_s_news::select('id', 'title_en as name', 'detail_en as content', DB::raw('CONCAT("/public/news/detail/",code ,"/en" ) AS link'))->where('title_en', 'LIKE', $keyword);
                        $dataWait2['count'] = $dataWait2['queryData']->count() + $dataWait['count'];
                        if ($dataWait2['count'] > 0){
                            $dataWait2['queryData'] = $dataWait2['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $dataWait2['queryData'] = array_merge($dataWait['queryData'], $dataWait2['queryData']);
                            $dataWait2['moreDetail'] = $dataWait2['count']<101?"":$data['data']['keyword'];
                            $data21['dataSearch']["News"] = $dataWait2;
                        }
                    }

                    if(check_permission_site_custom_api($data['data']['user_id'],'vulnerabilities') == 1){
                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();

                        $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword)->whereIn('namecve', $CVEMappingAssets_name);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data21['dataSearch']["Vulnerabilities"] = $dataWait;
                        }
                    }
                    

                    if(check_permission_site_custom_api($data['data']['user_id'],'compromised') == 1){
                        $dataWait["queryData"] = DataLeakFeed::select('id', 'feedcontent as content', 'sourceid', 'keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->whereIn('feel_type', ['darkweb', 'webserver', 'server', 'compromise', 'compromised'])->where(function ($query) use ($keyword) {
                            $query->where('keyword', 'LIKE', $keyword)
                                ->orWhere('source_name', 'LIKE', $keyword);
                        });
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('updated_at', 'desc')->get()->toArray();
                            $data21['dataSearch']["Compromised"] = $dataWait;
                        }
                    }
    

                    if(check_permission_site_custom_api($data['data']['user_id'],'data_leak') == 1){
                        $dataWait["queryData"] = DataLeakFeed::select('id', 'feedcontent as content', 'sourceid', 'keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereIn('feel_type', ['social', 'darkweb_public'])->where(function ($query) use ($keyword) {
                            $query->where('keyword', 'LIKE', $keyword)
                                ->orWhere('source_name', 'LIKE', $keyword);
                        });
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('updated_at', 'desc')->get()->toArray();
                            $data21['dataSearch']["Data Leak"] = $dataWait;
                        }
                    }
                    

                    if(check_permission_site_custom_api($data['data']['user_id'],'web_defacement') == 1){
                        $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data21['dataSearch']["Web Defacement"] = $dataWait;
                        }
                    }
                    
                    if(check_permission_site_custom_api($data['data']['user_id'],'indicators') == 1){
                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                        $pipeLine = array('name' => ['$regex'=>$data['data']['keyword'], '$options' => 'i']);
                        $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
                        if($dataWait['count']>0){
                            $options = [
                                'allowDiskUse' => TRUE
                            ];
                            $pipeline = [
                                [
                                    '$match' => [
                                        'name'  => ['$regex'=>$data['data']['keyword'], '$options' => 'i'],
                                    ]
                                ],
                                [
                                    '$project' => [
                                        '_id' => 0,
                                        'id' => '$pulse_id',
                                        'name' => '$name',
                                        'content' => [ '$concat' => ['source: ','$source']],
                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                    ]
                                ],
                                [
                                    '$sort' => [
                                        'modified'  => -1,
                                    ]
                                ],
                                [
                                    '$limit' => $limit
                                ]
                            ];
                            $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                            $dataWait['queryData'] = $dataWait['queryData']->toArray();
                            $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$data['data']['keyword'];
                            $data21['dataSearch']["Events"] = $dataWait;
                        }
                    }
                    
                    $page = langapp('search');
                    $response = [
                        "data" => $data21,
                    ];
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data){
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

            if($data === false){
                return $data;
            }else{
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }

        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
