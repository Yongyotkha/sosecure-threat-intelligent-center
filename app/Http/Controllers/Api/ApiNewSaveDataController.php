<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SiteSettings\Entities\LogsSetting;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\site_config_email_alert;
use MongoDB\Client as MongoClient;

class ApiNewSaveDataController extends Controller
{
    public function getData(Request $request){
        try {
            $code = $request -> code;

            $siteSettings = SiteSettings::where('code', $code)->with('get_site_config_email_alert')->first();
            $LogsSetting = LogsSetting::where('site_id', $siteSettings->id)->where('type','cve')->first();
            $LogsSettingIn = LogsSetting::where('site_id', $siteSettings->id)->where('type','indicator')->first();

            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => [
                'siteSettings' => $siteSettings,
                'LogsSetting' => $LogsSetting,
                'LogsSettingIn' => $LogsSettingIn
            ]]);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    }

    public function getIndicator(){
        try {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            
            $url = '';

            $options = [
                'projection' => [
                    '_id' => 0,
                    'industries' =>1,
                    'name' => 1,
                    'groups' => 1,
                    'tags' => 1,
                    'public' => 1,
                    'is_modified' => 1,
                    'modified' => 1,
                    'count_view' => 1,
                    'indicator_count' => 1,
                    'pulse_id' => 1,
                    
                ],
                'sort' => [
                    'modified' => -1
                ],
                // 'sort' => [
                //     $order => $dir
                // ],
                'skip' => 1,
                'limit' => 25,
            ];

            $query = array( 
                'status' => 1,
                'deleted_at' => null,
            );
            
            $cursor_count = $col_fx_otx_events->count($query);
            $count_filter = $cursor_count;
            $cursor = $col_fx_otx_events->find($query,$options);
            $query['indicator_count'] = ['$ne' => 0];
            $cursor = $col_fx_otx_events->find($query,$options);
            $cursor = $cursor->toArray();

            $data_nestedData = array();
            $order_number = 1;
            if(!empty($cursor))
            {
                foreach ($cursor as $document)
                {
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document["name"];
                        $nestedData['groups'] = $document["groups"] ? $this->explode_val($document["groups"],'groups',$url) : '';
                        $nestedData['tags'] = $this->explode_val($document["tags"],'tags',$url);
                        $nestedData['industries'] = $document["industries"] ? $this->explode_val($document["industries"],'industries',$url) : '';
                        $nestedData['attr'] = '';
                        $nestedData['attrCount'] = $document["indicator_count"];
                        $nestedData['public'] = ($document["public"]);
                        $nestedData['is_modified'] = ($document["is_modified"]);
                        // $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                        $nestedData['modified'] = change_date_thai_tummai($document['modified']);
                        $nestedData['count_view'] = @$document["count_view"];
                        $nestedData['pulse_id'] = $document["pulse_id"];
                    
                        // <a href="'.rou   te('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                        // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                        
                        //------------------------------------------------------
                        $DB_MONGO_KEY = env("DB_MONGO_DEV");
                        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                        if(app()->environment('local'))
                        {
                            $collection = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
                            $collection_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
                        }
                        else
                        {
                            $collection = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries;
                            $collection_related = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                        }

                        $query_actor = [
                            'pulse_id' => $document['pulse_id'],
                            'mode' => 'indicator',
                            'join' => 'actor',
                            'delete_at' => null
                        ];
                        $option_actor = [];

                        $result_actor = $collection_related->find($query_actor,$option_actor);
                        $final_actor = $result_actor->toArray();
                        $count_actor = count($final_actor);

                        $nestedData['actor'] = $final_actor;
                        $nestedData['count_actor'] = $count_actor;

                        foreach(@$final_actor as $sel_data_act)
                        {
                            $query_sel_act = [
                                'adversary_uuid' => $sel_data_act['adversary_uuid']
                            ];
                            $option_sel_act = [];
                            $result_sel_act = $collection->findOne($query_sel_act,$option_sel_act);
                            if(@$result_sel_act['logo'])
                            {
                                $nestedData['logo'][] = $result_sel_act['logo'];
                            }
                            else
                            {
                                $nestedData['logo'][] = '/asset_salepage/images/AgentBasedDetection.png';
                            }
                        }

                        $query_camp = [
                            'pulse_id' => $document['pulse_id'],
                            'mode' => 'indicator',
                            'join' => 'campainge',
                            'delete_at' => null
                        ];
                        $option_camp = [];

                        $result_camp = $collection_related->find($query_camp,$option_camp);
                        $final_camp = $result_camp->toArray();
                        $count_camp = count($final_camp);

                        $nestedData['camp'] = $final_camp;
                        $nestedData['count_camp'] = $count_camp;
                    
                    $data_nestedData[] = $nestedData;
                        
                }
            }
            $dataOut["data"] = $data_nestedData;

            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => [
                'indicator' => $dataOut,
            ]]);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    }

    public function saveCVE(Request $request){
        try {
            $code = $request -> code;

            $siteSettings = SiteSettings::where('code', $code)->first();
            if(!empty($siteSettings)){
                $data_request = $request -> data;
                $data = $this -> dataFalse($data_request);
                $LogsSetting = LogsSetting::where('site_id', $siteSettings->id)->where('type','cve')->first();
                if($LogsSetting){
                    $LogsSetting->site_id = $siteSettings->id;
                    $LogsSetting->type = "cve";
                    $LogsSetting->content = $data['data']['text_protocal_format'];
                    $LogsSetting->save();
                }else{
                    $LogsSetting = new LogsSetting;
                    $LogsSetting->site_id = $siteSettings->id;
                    $LogsSetting->type = "cve";
                    $LogsSetting->content = $data['data']['text_protocal_format'];
                    $LogsSetting->save();
                    
                }
            }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    }

    public function saveIndicator(Request $request){
        try {
            $code = $request -> code;

            $siteSettings = SiteSettings::where('code', $code)->first();
            if(!empty($siteSettings)){
                $data_request = $request -> data;
                $data = $this -> dataFalse($data_request);
                $LogsSetting = LogsSetting::where('site_id', $siteSettings->id)->where('type','indicator')->first();
                if($LogsSetting){
                    $LogsSetting->site_id = $siteSettings->id;
                    $LogsSetting->type = "indicator";
                    $LogsSetting->content = $data['data']['text_protocal_format'];
                    $LogsSetting->save();
                }else{
                    $LogsSetting = new LogsSetting;
                    $LogsSetting->site_id = $siteSettings->id;
                    $LogsSetting->type = "indicator";
                    $LogsSetting->content = $data['data']['text_protocal_format'];
                    $LogsSetting->save();
                    
                }
            }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    }

    public function saveSetting(Request $request){
        try {
            $code = $request -> code;

            $siteSettings = SiteSettings::where('code', $code)->first();
            if(!empty($siteSettings)){
                $data_request = $request -> data;
                $data = $this -> dataFalse($data_request);
                $siteSettings->server_log_port = trim($data['data']['port']);
                $siteSettings->server_log_protocol = trim($data['data']['protocol']);
                $siteSettings->server_log_ip = trim($data['data']['ip']);
                $siteSettings->save();
    
                site_config_email_alert::where('site_id', $siteSettings->id)->delete();
                if (!empty($data['data']['email_alert'])) {
                    if (count($data['data']['email_alert']) > 0) {
                        foreach ($data['data']['email_alert'] as $email_alert) {
                            $site_config_email_alert = new site_config_email_alert;
                            $site_config_email_alert->site_id = $siteSettings->id;
                            $site_config_email_alert->email = $email_alert;
                            $site_config_email_alert->save();
                        }
                    }
                }
            }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    
    }

    private function dataFalse($data){
        try {
            $data_return = [
                'data' => json_decode($data, true),
            ];
            return $data_return;
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    private function explode_val($val,$type=null,$url) {
        $result = '';
        if($val) {
            $val_arr = explode(",",$val);
            if($val_arr) {
                foreach($val_arr as $tag) {
                    if($type == 'tags') {
                        $result .=  '<a href="'.$url.'/indicators/tags/'.$tag.'">'.$tag.'</a> ,';
                    } else if ($type == 'groups') {
                        $result .=  '<a href="'.$url.'/indicators/groups/'.$tag.'">'.$tag.'</a> ,';
                    } else {
                        $result .=  '<a href="#">'.$tag.'</a> ,';
                    }
    
                }
                $result = rtrim($result,',');
            }
        } else {
            $result = '';
        }
        return $result;
    }
}
