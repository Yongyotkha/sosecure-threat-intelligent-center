<?php

namespace Modules\Indicators\Http\Controllers;

use Modules\SiteSettings\Entities\SiteSettings;
use App\IndicatorSummaryYear;
use Yajra\DataTables\DataTables;
use App\Entities\OtxIndicatiorData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\indicators\Entities\OTXtypeData;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use DB;
use Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
class IndicatorsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $ip;
    protected $mac;
    protected $header;
    protected $client;
    protected $urlLimit = 3;
    protected $base_url;
    protected $url_indicator_events_table;
    protected $url_indicator_events_detail_select;
    protected $url_indicator_events;
    protected $url_indicator_load_attributes_tb;
    protected $url_indicator_load_pulse_tb;
    protected $url_indicator_count_view;
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
        if(TYPE_WEB !== 'center'){
            $this->ip = config('app.ip_ad');
            $this->mac = config('app.mac_ad');
            $this->header = config('app.site_key');
            $this->client = new \GuzzleHttp\Client();
            $this->base_url = config('app.url_center').'/api/v1/'.config('app.mode').'/'.config('app.site_code');
            $this->url_indicator_events_table = $this->base_url.'/indicator/events_table';
            $this->url_indicator_events = $this->base_url.'/indicator/events';
            $this->url_indicator_events_detail_select = $this->base_url.'/indicator/events_detail_select';
            $this->url_indicator_load_attributes_tb = $this->base_url.'/indicator/events_load_attributes_tb';
            $this->url_indicator_load_pulse_tb = $this->base_url.'/indicator/events_load_pulse_tb';
            $this->url_indicator_count_view = $this->base_url.'/indicator/events_count_view';
        }
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */

    public function events()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        //<><><>
        // if(Auth::check()) {

        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(Auth::user()->hasRole('admin')) {//if admin
        //         // dd(777);
        //         $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();


        //             } else {//not support and admin
        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();
        //             }
        //         }
        //     }
        // }
        if(TYPE_WEB == 'center'){
            $get_role_custom_first = @get_role_custom();
            $SiteSettings = '';
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
            }else if(@$get_role_custom_first['client'] == 1) {
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
            }else if(@$get_role_custom_first['site_support'] == 1) {
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
            }else if(@$get_role_custom_first['site_client'] == 1) {
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
            }

            $data["attr_all"] = IndicatorSummaryYear::where("type",'summary_all')->first();
            $data["attr_current"] = IndicatorSummaryYear::where("type",'summary_current')->first();
            // DB::raw('CONCAT("[",attribute_count, "]") as data2')
            $dataForloop = IndicatorSummaryYear::select('type_name AS name','attribute_count AS data')->where("type",'summary_attr_type')->orderBy('attribute_count','desc')->take(10)->get();
            $data["attr_type"] = array();
            foreach ($dataForloop as $document) {
                array_push($data["attr_type"], array('name'=>ucwords($document->name),'data'=>[$document->data]));
            }
            $data['SiteSettings'] = $SiteSettings;
            $data['page'] = langapp('indicators');

            if(isset($this->request->Search_Link_All)){
                $data['Search_Link_All'] = $this->request->Search_Link_All;
            }else{
                $data['Search_Link_All'] = "";
            }
            return view('indicators::events')->with($data);

        }else{
            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_indicator_events = $this->url_indicator_events;


            $request_body_complete = [
                'request' => 'data',
                'Search_Link_All' => $this->request->Search_Link_All
            ];
            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_indicator_events, $form_body_complete, $authorization_key);

            if($response_complete['status_code'] == "200"){
                $data = $response_complete['data'];
                return view('indicators::events')->with($data);
            }else{
                abort(404);
            }
        } 
    }

    public function events_detail()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        $data['page'] = langapp('indicators');
        return view('indicators::events_detail')->with($data);
    }

    public function events_detail_select(Request $request, $id)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        if(TYPE_WEB == 'center'){
            $client = new Client(DB_MONGO_01);
            $collection = $client->sosecure_threatintelligent->fx_otx_events;

            $query = [
                'pulse_id' => $id
            ];

            $options = [
                'limit' => 1
            ];

            $cursor = $collection->find($query, $options)->toArray();

            $_array = array();

            $data['otx_events'] = $cursor;
            $data['page'] = langapp('indicators');
            $data['indicator_type_counts'] = $cursor[0]->indicator_type_counts->count();
            $data['count_related_pulse'] = @$cursor[0]->count_related_pulse;
            $countKey = array();
            $countVal = array();
            foreach ($cursor[0]->indicator_type_counts as $key => $value) {
                $countKey[]= ucwords($key);
                $countVal[]= $value;
            }
            $data['countKey'] = $countKey;
            $data['countVal'] = $countVal;
            

            $data['indicator_id'] = $request->id;
            
            $data['type'] = $request->type;
            $data['indicator'] = $request->indicator;

            $data['pulse_id'] = $id;
            return view('indicators::events_detail')->with($data);
        }else{
            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_indicator_events_detail_select = $this->url_indicator_events_detail_select;


            $request_body_complete = [
                'request' => 'data',
                'id' => $id,
            ];
            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_indicator_events_detail_select, $form_body_complete, $authorization_key);
            if($response_complete['status_code'] == 200){
                $cursor = $response_complete['data'];
                // dd($cursor);
                $data['otx_events'] = $cursor;
                $data['page'] = langapp('indicators');
                $data['indicator_type_counts'] = count($cursor[0]['indicator_type_counts']);
                $data['count_related_pulse'] = @$cursor[0]['count_related_pulse'];
                $countKey = array();
                $countVal = array();
                foreach ($cursor[0]['indicator_type_counts'] as $key => $value) {
                    $countKey[]= ucwords($key);
                    $countVal[]= $value;
                }
                $data['countKey'] = $countKey;
                $data['countVal'] = $countVal;

                $data['indicator_id'] = $request->id;
                
                $data['type'] = $request->type;
                $data['indicator'] = $request->indicator;

                $data['pulse_id'] = $id;
                return view('indicators::events_detail')->with($data);
            }else{
                abort(404);
            }
        }
    }

    public function attributes()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        // $client = new Client('mongodb://10.104.0.10:27017');
        // $collection = $client->sosecure_threatintelligent->fx_otx_type;

        // $query = [
        //     'status' => 1,
        // ];

        // $options = [];

        // $cursor = $collection->find($query, $options);
        // $docs = $cursor->toArray();
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent';
        $db = $client->$db_name;
        $collection = $db->fx_otx_type;
        $where = array(
            'status' => 1,
        );

        $cursor = $collection->find($where);   //This is the main line
        $data['cursor']= $cursor->toArray();
        $data['page'] = langapp('indicators');
        //$data['otx_type'] = OTXtypeData::where("status", '=', 1)->get();
        $data['SiteSettings'] = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        return view('indicators::attributes')->with($data);
    }

    public function show_detail_indicator(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        $data['page'] = langapp('indicators');
        $data['otxid'] = $request->id;
        $data['otxtype'] = $request->type;
        $data['otxindicator'] = $request->indicator;
        return view('indicators::detail_indicators')->with($data);
    }


    public function load_general(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        $reqType = $request->type;
        $reqIndicator = $request->indicator;
        $reqId = (string)$request->id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $html = '';
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;

        $options = array(
            'typeMap' => array(
                'root' => 'array',
                'document' => 'array',
            ),
        );

        $document = $col_fx_otx_indicator_detail->findOne(array('indicator_id' => $reqId),$options);
        if(!empty($document)){


            if(!empty($document["allrow"])){
                foreach ($document["allrow"] as $key => $value) {
                    if($key=="LOCATION"){
                        $value = explode("--",$value)[0];    
                    }else if($key=="CREATION DATE"||$key=="LAST MODIFIED DATE"||$key=="LAST ANALYZED DATE"||$key=="Analysis Date"){    
                        $value = date_format(date_create($value), 'l jS F Y g:ia');
                    }
                    $html .= '
                    <div class="row m-b-xs">
                    <div class="col-md-12">
                    '.$key.': <a >' . $value . '</a>
                    </div>
                    </div>';
                }
            }else if($reqType=="YARA"&&isset($document["ruleRow"])){
                $html .= '
                <div class="row m-b-xs">
                <div class="col-md-12">
                <a >' . $document["ruleRow"] . '</a>
                </div>
                </div>';
            }else{
                $html .= '
                <div class="row m-b-xs">
                <div class="col-md-12">
                No Detail
                </div>
                </div>';
            }
        }

        if ($request->ajax()) {
            $data = [
                "html" => $html,
            ];
            return response()->json($data);
        }

    }

    public function load_relatedPulse(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }

        $draw = $_POST['draw'];
        $start = (int)$_POST['start'];
        $rowperpage = (int)$_POST['length'];
        $order = 'modified';
        $dir = -1;
        
        $reqId = (string)$request->id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $html = '';
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;


        $query = [
            'indicator_id' => $reqId,
            
        ];


        $options = [
            'sort' => [
                // $order => $dir
            ],
            'skip' => $start,
            'limit' => $rowperpage,
        ];

        if($request->count_page==-1){
            $cursor_count = $col_fx_otx_events_indicator_ref->count($query);
            $count_filter = $cursor_count;
            // dd($cursor_count);
        }else{
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }
        $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
        $document_all = $cursor->toArray();



        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $options = array(
            'typeMap' => array(
                'root' => 'array',
                'document' => 'array',
            ),
        );

        $data = array();
        $order_number = $start;

        if($document_all){
            foreach ($document_all as  $value) {
                $query = [
                    'pulse_id' => $value->pulse_id
                    
                ];
                $cursor_2 = $col_fx_otx_events->findOne($query,$options);

                //  dd($value);
                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $cursor_2["name"];
                $nestedData['groups'] = explode_val($cursor_2["groups"],'groups');
                $nestedData['tags'] = explode_val($cursor_2["tags"],'tags');
                $nestedData['public'] = ($cursor_2["public"]);
                $nestedData['is_modified'] = ($cursor_2["is_modified"]);
                $nestedData['attrCount'] = $cursor_2["indicator_count"];
                $nestedData['modified'] = change_date_utc_to_thai($cursor_2['modified']);
                $nestedData['count_view'] = $cursor_2["count_view"];
                $nestedData['pulse_id'] = $cursor_2["pulse_id"];
                $data[] = $nestedData;
            }
        }

        $keysort = array_column($data, $order);
        array_multisort($keysort, SORT_DESC, $data);
        
        $dataOut["draw"] = $draw;
        $dataOut["recordsTotal"] = $cursor_count;
        $dataOut["recordsFiltered"] = $count_filter;
        $dataOut["data"] = $data;
        $dataOut["cursor"] = $cursor;
        return response()->json($dataOut);

    }

    public function load_relatedPulse_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        $reqId = $request->id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $html = '';
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
        $options = [
            'allowDiskUse' => TRUE
        ];

        $pipeline = [
            [
                '$project' => [
                    '_id' => '$_id',
                    'a' => '$$ROOT'
                ]
            ]
            ,
            [
                '$lookup' => [
                    'localField' => 'a.pulse_id',
                    'from' => 'fx_otx_events',
                    'foreignField' => 'pulse_id',
                    'as' => 'b'
                ]
            ]
            ,
            [
                '$unwind' => [
                    'path' => '$b',
                    'preserveNullAndEmptyArrays' => TRUE
                ]
            ]
            ,
            [
                '$match' => [
                    'a.indicator_id'  => $reqId,
                    'a.status'  => 1,
                ]
            ],
            [
                '$sort' => [
                    'b.modified'  => -1,
                ]
            ]
        ];
        $cursor = $col_fx_otx_events_indicator_ref->aggregate($pipeline, $options);
        $document_all = $cursor->toArray();
        $count_doc = count($document_all);
        
        
        $dataOut["draw"] = 1;
        $dataOut["recordsTotal"] = $count_doc;
        $dataOut["recordsFiltered"] = $count_doc;
        $dataOut["data"] = array_column($document_all, 'b');
        //dd($dataOut);
        return response()->json($dataOut);
        if ($request->ajax()) {
         return response()->json($dataOut);
     }

 }

 public function load_url_list(Request $request)
 {
    $role_custom = @check_role_custom();
    if(!$role_custom['indicators']) {
        check_permission403();
    }
    $OTX_KEY = env("OTX_KEY", "");
    $client = new \GuzzleHttp\Client();

    $reqType = $request->type;
    if (stripos($request->type, "file") !== false) {
        $reqType = 'file';
    } else if ($reqType == "CVE") {
        $reqType = "cve";
    } else if ($reqType == "URL") {
        $reqType = 'url';
    } else if ($reqType == "NIDS") {
        $reqType = 'nids';
    } else if ($reqType == "YARA") {
        $reqType = 'yara';
    } else if ($reqType == "BitcoinAddress") {
        $reqType = 'bitcoin-address';
    } else if ($reqType == "SSLCertFingerprint") {
        $reqType = 'ssl-cert-fingerprint';
    }
    $reqIndicator = $request->indicator;

        // https://otx.alienvault.com/otxapi/indicator/url/url_list/http%3A%2F%2Fwww.bonanzadesign-my.com%2Fgrace%2FMasterNewShit.exe?limit=10&page=1
    $isUrl_list = 0;
    $testt = 'https://otx.alienvault.com/otxapi/indicator/url/url_list/http%3A%2F%2Fwww.haromaain.com%2Fovhcloud.ovh.com%2Fmanager%2Fmoncompte%2Frenouvellement%2Fvos-service%2Fwebdomaine%2FOVHCloud%2F4870031649701203465875104976045875%2F48700316497012034658751049760%2Fgi1ztq%253D%2F?limit=10&page=1';
    if (isset($request->data_general["sections"]) && in_array("url_list", $request->data_general["sections"])) {
        $bodyData = $client->request(
            'GET',
            'https://otx.alienvault.com/otxapi/indicator/' . $reqType . '/url_list' . '/' . $reqIndicator,
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                    'X-OTX-API-KEY' => $OTX_KEY,
                ],
            ]
        )->getBody();
        $DataotxIndicator = json_decode($bodyData, true);
    } else {
        $DataotxIndicator = null;
    }
    $html = '';

    if (isset($DataotxIndicator["url_list"][0]["result"]["urlworker"]["ip"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        IP ADDRESS: <a >' . $DataotxIndicator["url_list"][0]["result"]["urlworker"]["ip"] . '</a>
        </div>
        </div>';
    }
//<img src="' . (isset($DataotxIndicator["flag_url"])?("https://otx.alienvault.com/".$DataotxIndicator["flag_url"]):"") . '" alt="">
    if (isset($DataotxIndicator["flag_title"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        LOCATION:  <a >' . $DataotxIndicator["flag_title"] . '</a>
        </div>
        </div>';
    }

    if (isset($request->data_general["hostname"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        HOSTNAME: <a >' . $request->data_general["hostname"] . '</a>
        </div>
        </div>';
    }

    if (isset($request->data_general["domain"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        DOMAIN: <a>' . $request->data_general["domain"] . '</a>
        </div>
        </div>';
    }

    if (isset($DataotxIndicator["url_list"][0]["result"]["urlworker"]["Date"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        LAST ANALYZED DATE: <a >' . $DataotxIndicator["url_list"][0]["result"]["urlworker"]["Date"] . '</a>
        </div>
        </div>';
    }

    if (isset($DataotxIndicator["url_list"][0]["result"]["safebrowsing"]["matches"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        GOOGLE SAFE BROWSING:

        <a >';

        if (empty($DataotxIndicator["url_list"][0]["result"]["safebrowsing"]["matches"])) {
                // @icon(\'solid/check\') Not identified as malicious
            $html .= 'Not identified as malicious';
        } else {
            foreach ($DataotxIndicator["url_list"][0]["result"]["safebrowsing"]["matches"] as $value) {
                $html .= $value . ' ';
            }
        }
        $html .= '</a>

        </div>
        </div>';
    } else {
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        GOOGLE SAFE BROWSING:  <a>Not analyzed</a>
        </div>
        </div>';
    }

    if (isset($DataotxIndicator["url_list"][0]["result"]["multiav"]["matches"]["matches"])) {
        $isUrl_list = 1;
        $html .= '
        <div class="row m-b-xs">
        <div class="col-md-12">
        ANTIVIRUS: <a >' . $DataotxIndicator["url_list"][0]["result"]["multiav"]["matches"]["matches"] . '</a>
        </div>
        </div>';
    }

    if ($request->ajax()) {
        $data = [
            "html" => $html,
            "out" => $DataotxIndicator,
            "rtt" => 'https://otx.alienvault.com/otxapi/indicator/' . $reqType . '/url_list' . '/' . $reqIndicator,
            "rtt2" => $testt,
        ];
        return response()->json($data);
    }

}

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('indicators::create');
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
        return view('indicators::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('indicators::edit');
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

    // public function typeData()
    // {
    //     $data = OTXtypeData::all()->toArray();
    //     return view('indicators::attributes', compact('data'));
    // }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function LoadMoreOTX(Request $request)
    { //"someField" => array('$ne' => null),   
    $role_custom = @check_role_custom();
    if(!$role_custom['indicators']) {
        check_permission403();
    }
    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");

            // $DB_MONGO_KEY = "mongodb://10.104.0.7:27017";
    $clientMD = new MongoClient($DB_MONGO_KEY);
    $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicators_data;

    $cursor = $col_fx_transaction_otx_indicators_data->find();
    $documentAll = $cursor->toArray();
            // dd($documentAll);

    $date_start = $request->startDate;
    $date_end = $request->endDate;

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
        // dd($date_end_datetime_format);
    $html = '';

            // if($request -> title || $request -> cate || $request -> related_news || $request -> lang_th || $request -> lang_en || $request -> date_start || $request -> date_end){

            //     $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')

            //      $news = $news->orderBy('created_at','desc')->paginate(10);
            //     // $news->orderBy('created_at','desc')->paginate(10);
            //     // $news = RSSNews::where('save_draft', 0);//->get()
            //     // $news -> paginate(10);//->get()
            //     // $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc')->paginate(10);//->get()
            //     // $news = $news->get();
            //     // dd($news->get());
            //     // dd($news);
            //     $news_all = $news->count();
            // }else{
            //     $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc')->paginate(10);//->get()
            // }
    $data = '';
            // dd($news);
    $_sort = [];
    $_search = array(
        'status' => 1,
        'deleted_at' => null
    );
    $_option = array(
        'limit' => PAGINATE_NUM,
        'skip' => ($request->page-1)*PAGINATE_NUM,
        'sort' => $_sort
    );


    if ($request->target == 'Recently Modified') {
        $_sort = [
            'transcation_id' => -1,
            '_id' => 1,
        ];
    } else if ($request->target == 'Least Recently Modified') {
        $_sort = [
            'transcation_id' => 1,
            '_id' => -1,
        ];
    } else if ($request->target == 'Name Descending') {
        $_sort = [
            'indicator' => -1,
        ];
    } else if ($request->target == 'Name Ascending') {
        $_sort = [
            'indicator' => 1,
        ];
    } else {
        $_sort = [
            'transcation_id' => -1,
            '_id' => 1,
        ];
    }

    if ($request->f_search == 1) {



        if ($request->keywords || $request->type || $request->startDate || $request->endDate) {      
                    // if ($request->type && $request->keywords && $request->startDate) {
                    //     $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereIn('type', $request->type)->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                    // } else if ($request->type && $request->keywords) {

                    //     $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereIn('type', $request->type);
                    // } else if ($request->startDate && $request->keywords) {

                    //     $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                    // } else if ($request->startDate && $request->type) {

                    //     $data = OtxIndicatiorData::where("status", '=', 1)->whereIn('type', $request->type)->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                    // } else if ($request->keywords) {

                    //     $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%');
                    // } else if ($request->type) {

                    //     $data = OtxIndicatiorData::where("status", '=', 1)->whereIn('type', $request->type);
                    // } else if ($request->startDate) {

                    //     $data = OtxIndicatiorData::whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                    // }
                    // $count = $data->count();
                    // $data = $data->paginate(PAGINATE_NUM);
                    // if ($request->target == 'Recently Modified') {
                    //     $data = $data->orderBy('updated_at', 'desc');
                    // } else if ($request->target == 'Least Recently Modified') {
                    //     $data = $data->orderBy('updated_at', 'asc');
                    // } else if ($request->target == 'Name Descending') {
                    //     $data = $data->orderBy('indicatior', 'desc');
                    // } else if ($request->target == 'Name Ascending') {
                    //     $data = $data->orderBy('indicatior', 'asc');
                    // }

            if ($request->keywords) {
                $_search['indicator'] = ['$regex'=>$request->keywords, '$options' => 'i'];
                        // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
            } 

            if ($request->type) {

                $_search['type'] = ['$in'=>$request->type];
                        // $_search =  array_merge($_search, array('type' => ['$in'=>$request->type]));
            }

            $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);  
            if($isDateSearch){
                if ($request->startDate&&$request->endDate) {
                    $_search['updated_at'] = ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }else if($request->startDate){
                    $_search['updated_at'] = ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                }else if($request->endDate){
                    $_search['updated_at'] = ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }
            }

        }
    } else {

                // if ($request->target == 'Recently Modified') {
                //     $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'desc');
                // } else if ($request->target == 'Least Recently Modified') {
                //     $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'asc');
                // } else if ($request->target == 'Name Descending') {
                //     $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'desc');
                // } else if ($request->target == 'Name Ascending') {
                //     $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'asc');
                // } else {
                //     $data = OtxIndicatiorData::where("status", '=', 1);
                // }
                // $data = $data->paginate(PAGINATE_NUM);
                // $count = OtxIndicatiorData::where("status", '=', 1)->count();

    }

    $_option['sort'] = $_sort;
    $cursor = $col_fx_transaction_otx_indicators_data->find(
        $_search,
        $_option
    );
    $count = $col_fx_transaction_otx_indicators_data->count($_search);
            //dd($cursor->count());
            //dateATOM
    $documentAll = $cursor->toArray();

            //    else {

            //         if ($request->target == 'Recently Modified') {
            //             $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'desc');
            //         } else if ($request->target == 'Least Recently Modified') {
            //             $data =  OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'asc');
            //         } else if ($request->target == 'Name Descending') {
            //             $data =  OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'desc');
            //         } else if ($request->target == 'Name Ascending') {
            //             $data =  OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'asc');
            //         } else {
            //             $data = OtxIndicatiorData::where("status", '=', 1);
            //         }

            //         $data = $data->paginate(PAGINATE_NUM);
            //         $count = OtxIndicatiorData::where("status", '=', 1)->count();
            //     }

            // foreach ($data as $data) {

            //     //ถ้าเป็น NIDS เอา Title มาแทน indicatior

            //     if ($data->type == "CIDR" || $data->type == "FilePath" || $data->type == "FileHash-IMPHASH" || $data->type == "Mutex" || $data->type == "URI") {
            //         $linkIndicator =  '<a>';
            //     } else {
            //         $linkIndicator =  '<a href="' . route('indicators.detail_indicators') . '?id=' . $data->id . '&&type=' . $data->type . '&&indicator=' . $data->indicatior . '">';
            //     }
            //     $html .= ' <ul class="list-indicators">
            //                 <li>
            //                     ' . $linkIndicator . '
            //                         <h1 class="primary-text">' . $data->indicatior . '</h1>



            //                         <span class="secondary-text">Type : ' . $data->type . '</span>
            //                     </a>
            //                 </li></ul>';
            // }
            // if(!empty($documentAll))
    foreach ($documentAll as $data) {
                //ถ้าเป็น NIDS เอา Title มาแทน indicatior
        if ($data->type == "CIDR"  || $data->type == "FileHash-IMPHASH"|| $data->type == "FileHash-PEHASH" || $data->type == "FilePath" || $data->type == "Mutex" || $data->type == "URI"|| $data->type == "JA3"|| $data->type == "osquery") {
            $linkIndicator =  '<a>';
        } else {
            $linkIndicator =  '<a href="' . route('indicators.detail_indicator') . '?id=' . $data->indicator_id . '&type=' . $data->type . '&indicator=' . $data->indicator . '">';
        }
        $html .='<ul class="list-indicators text-elip">
        <li>
        ' . $linkIndicator . '
        <h1 class="primary-text">' . $data->indicator . '</h1>
        <span class="secondary-text">Type : ' . $data->type . '</span>
        </a>
        </li>
        </ul>';
    }

    if ($request->ajax()) {
        $data = [
            "html" => $html,
            "count" => $count,
        ];
        return response()->json($data);
    }
}


public function tableEvents(Request $request)
{
    $role_custom = @check_role_custom();
    if(!$role_custom['indicators']) {
        check_permission403();
    }

        // $DB_MONGO_KEY = env("DB_MONGO_DEV", "mongodb://10.104.0.7:27017");

        // $client = new \MongoDB\Client($DB_MONGO_KEY);

    $f_search = $request->f_search;
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $keyword = $request->keyword;

    if($start_date) {
        $date_start_explode = explode(" ",$start_date);
        $date_start_date = @$date_start_explode[0];
        $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
            // dd($date_start_time);
        $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            // dd($date_start_date_format);
        $date_start_time_time = date("H:i", strtotime($date_start_time));
        $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
            // dd($date_start_time_time);


        $date_end_explode = explode(" ",$end_date);
        $date_end_date = @$date_end_explode[0];
        $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
            // dd($date_end_time);
        $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        $date_end_time_time = date("H:i", strtotime($date_end_time));
        $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
            // dd($date_end_time_time);



        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($date_start_datetime_format)*1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($date_end_datetime_format)*1000);
            // dd($dateStart);
    }






    $perpage = 25;

    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    } else {
        $page = 1;
    }
    $start = ($page - 1) * $perpage;

    $mongo_url = DB_MONGO_01;
    $client = new \MongoDB\Client($mongo_url);
    $db_name = 'sosecure_threatintelligent';
    $db = $client->$db_name;
    $collection = $db->fx_otx_events;
        // $where = array(
        //     'status' => 1,
        // );

    if($f_search == 'true') {
        $query = [
            '$and' => [
                [
                    '$or' => [
                        [
                            '$and' => [
                                [
                                    'name' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ],
                        [
                            '$and' => [
                                [
                                    'tags' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ],
                        [
                            '$and' => [
                                [
                                    'groups' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'created_at' => [
                            '$gte' => $dateStart//new UTCDateTime(-15644559600000)
                        ]
                    ],
                    [
                        'created_at' => [
                            '$lte' => $dateEnd//new UTCDateTime(-15644559600000)
                        ]
                    ]
                ]
            ];


        } else {
            $query = [
                'status' => 1//,
                // 'tags' => new Regex('^.*webscanner.*$', 'i')//LIKE
                // 'tags' => [//IN
                //     '$in' => [
                //         'webscanner',
                //         'test'
                //     ]
                // ]
            ];
        }

        
        $options = [
            'sort' => [
                'modified' => -1
            ],
            'skip' => $start,//10
            'limit' => $perpage//5
        ];
        
        $cursor = $collection->find($query, $options);

        $query2 = [];
        $options2 = [
            'projection' => [
                '_id' => '$_id'
            ]
        ];

        $cursor_all = $collection->find($query, $options2);

        $total_record = count($cursor_all->toArray());
        // dd($total_record);
        // $total_record = mysqli_num_rows($query2);
        $total_page = ceil($total_record / $perpage);
        $second_last = $total_page - 1; // total pages minus 1

        $offset = ($page-1) * $perpage;
        $previous_page = $page - 1;
        $next_page = $page + 1;
        $adjacents = "2";


        $pagination = '<nav>
        <ul class="pagination">';

        if($page > 1) {
            $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">First Page</a></li>';
        }

        $pagination .= '<li onclick="pagination_goto('.$previous_page.')" data-page="'.@$previous_page.'"';
        if($page <= 1) {
            $pagination .= 'class="disabled"';
        }
        $pagination .= '>';

        $pagination .= '<a ';
        if($page > 1) {
            $pagination .= 'href="#"';
        }

        $pagination .= '>Previous</a></li>';

        if ($total_page <= 10){   
            for ($counter = 1; $counter <= $total_page; $counter++){
                if ($counter == $page) {
                    $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                }else{
                    $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                }
            }
        } elseif ($total_page > 10){
            if($page <= 4) { 
                for ($counter = 1; $counter < 8; $counter++){ 
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }
                }
                $pagination .= '<li><a>...</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$second_last.')" data_page="'.$second_last.'"><a href="#">'.$second_last.'</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">'.$total_page.'</a></li>';
            } elseif ($page > 4 && $page < $total_page - 4) { 
                $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">1</a></li>';
                $pagination .= '<li onclick="pagination_goto(2)" data-page="2"><a href="#">2</a></li>';
                $pagination .= "<li><a>...</a></li>";
                for (
                   $counter = $page - $adjacents;
                   $counter <= $page + $adjacents;
                   $counter++
               ) { 
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }                  
                }   
                $pagination .= "<li><a>...</a></li>";
                $pagination .= '<li onclick="pagination_goto('.$second_last.')" data-page="'.$second_last.'"><a href="#">'.$second_last.'</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">'.$total_page.'</a></li>';
            } else {
                $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">1</a></li>';
                $pagination .= '<li onclick="pagination_goto(2)" data-page="2"><a href="#">2</a></li>';
                $pagination .= '<li><a>...</a></li>';
                for (
                   $counter = $total_page - 6;
                   $counter <= $total_page;
                   $counter++
               ) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }                   
                }
            }
        }

        $pagination .= '<li onclick="pagination_goto('.$next_page.')" data-page="'.@$next_page.'"';

        if($page >= $total_page){
            $pagination .= 'class="disabled"';
        } 
        $pagination .= ' >';

        $pagination .= '<a ';
        if($page < $total_page) {
            $pagination .= 'href="#"';
        }
        $pagination .= '>Next</a></li>';

        if($page < $total_page){
            $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">Last &rsaquo;&rsaquo;</a></li>';
        } 
        $pagination .= '</ul></nav>';






        $start_first_in_page = $start+1;
        $end_last_in_page = $start+$perpage;

        $showing_amount_text = '<div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;">Showing '.$start_first_in_page.' to '.$end_last_in_page.' of '.$total_record.' entries</div>';



        $html = '';
        $head_table = '';
        $head_table .= '<table class="table table-striped" id="table_events">
        <thead>
        <tr>
        <!--<th>
        <label>
        <input name="select_all" value="1" id="select-all" type="checkbox" />
        <span class="label-text"></span>
        </label>
        </th>-->
        <th style="width: 50px;">No</th>
        <th style="width: 500px;">Event Name</th>
        <th style="width: 200px;">Group</th>
        <th style="width: 200px;">Tags</th>
        <!--<th>Attr</th>-->
        <th style="width: 100px;">Published</th>
        <th style="width: 120px;">Last Status</th>
        <th style="width: 120px;">DateTime</th>
        <th style="width: 100px;">View</th>
        <th style="width: 120px;">Action</th>
        </tr>
        </thead>
        <tbody>';

        $html .= $head_table;





        $i = $start;
        // dd($cursor->toArray());
        foreach ($cursor as $document) {
            $i++;
            $html .= '
            <tr>
            <!--<td>
            <label>
            <input value="'.$document['_id'].'" type="checkbox" />
            <span class="label-text"></span>
            </label>
            </td>-->
            <td>'.$i.'</td>
            <td style="width: 500px;">'.$document['name'].'</td>
            <td>'.explode_val($document['groups'],'groups').'</td>
            <td>'.explode_val($document['tags'],'tags').'</td>
            <!--<td>
            <a href="#"></a>
            </td>-->
            <td>'.check_publish($document['public']).'</td>
            <td>'.check_last_status($document['is_modified']).'</td>
            <td>'.change_date_utc_to_thai($document['modified']).'</td>
            <td>'.$document['count_view'].'</td>
            <td>
            <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
            </td>
            </tr>
            ';
            // dd($document['_id']);
        }

        $html .= '</tbody>
        </table>';

        // dd($cursor);
        // $cursor = $collection->find($where,['projection'=>['_id'=>0]]);
        // $cursor = $collection->find($where);

        // $model= $cursor->toArray();

        // dd($model);
        // $model = $model[0];
        // unset($model['_id']);


        // $collection = collect(['name', 'public']);
        // $collection = collect([
        //     ['product' => 'Desk', 'price' => 200],
        //     ['product' => 'Chair', 'price' => 100],
        // ]);

        // $collection->paginate(15);
        // dd($collection);

        // $combined = $collection->combine(['George', 1]);
        // $combined->all();
        // dd($combined);
        //  dd($model[0]['TLP']);
        // $model = collect($model);
        //dd($model[0]->TLP);

        // return DataTables::of($collection->toJson())
        // ->editColumn('chk', function ( $collection) {
        //     return '<label><input type="checkbox"  name="events_id" class="events_id" value=""><span class="label-text"></span></label>';
        // })
        // ->addColumn('no', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('even_name', function ( $collection) {

        //     return $collection->product;
        // })

        // ->addColumn('group', function ( $collection) {
        
        //     return "-";
        // })
        // ->addColumn('tags', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('attr', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('published', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('last_status', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('date_time', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('view', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('action', function ( $collection) {
        //     return '-';
        // })


        // ->rawColumns(['chk', 'no', 'even_name', 'group', 'tags', 'attr', 'published', 'last_status', 'date_time', 'view', 'action'])
        // ->toJson();
        // return $html;
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "pagination" => $pagination,
                "showing_amount_text" => $showing_amount_text,
            ];
            return response()->json($data);
        }

    }

    public function load_attributes_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        // dd( $_POST['order']);
        if(TYPE_WEB == 'center'){
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];



            $start =  $row;


            $reqId = $request->pulse_id;
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $html = '';
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            
            $query = [
                'pulse_id' => $reqId,
                'type' => [
                    '$exists' => true,
                    '$ne' => null
                ],
                // 'type'=> { $exists: true, $ne: null },
            ];

            $options = [
                'skip' => $start,
                'limit' => $rowperpage,
                'sort' => [
                    'updated_at' => -1,
                ]
            ];
            
            if($request->count_page==-1){
                //$cursor_count = $col_fx_otx_events_indicator_ref->count($query);
                $cursor_count =$request->total_record;
                $count_filter = $cursor_count;
            }else{
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



            $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
            $document_all = $cursor->toArray();

            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $data = array();
            // foreach ($document_all as  $value) {
            //     $query = [
            //         'indicator_id' => $value->indicator_id

            //     ];
            //     $cursor_2 = $col_fx_otx_indicator_detail->findOne($query);

            //     //$join_fx_otx_indicator_detail[]=  array("a"=>$value,"b"=>$cursor_2);
            //     // $view = '<a href="'.route('indicators.detail_indicator').
            //     //         '?id='.$document['b']['indicator_id'].'&type='.$document['b']['type'].'&indicator='.$document['b']['indicator_name'].'" 
            //     //         class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>'; 
            //     $data[] = array( 
            //         "type"=>@$cursor_2['type'],
            //         "indicator"=>@$cursor_2['indicator_name'],
            //         "role"=>@$value['role'],
            //         "updated_at"=>@$value['updated_at'],
            //         "indicator_id"=>$value->indicator_id,
            //         "transaction_date"=>'',
            //     );

            //  }
        //     foreach ($document_all as $key => $value) {

        //      $query = [
        //         'indicator_id' => $value->indicator_id

        //     ];
        //     $cursor_2 = $col_fx_otx_indicator_detail->findOne($query);


        //     $data[] = array( 
        //         "TYPE"=>@$cursor_2['type'],
        //         "AttributeName"=>(isset($cursor_2['indicator_name'])?$cursor_2['indicator_name']:""),
        //         "ROLE"=>@$value['role'],
        //         "Date"=>(isset($value['created'])?change_date_utc_to_thai($value['created']):""),
        //         "Action"=>route('indicators.detail_indicator')."?id=".@$value['indicator_id'].
        //         '&type='.@$cursor_2['type'].'&indicator='.@$cursor_2['indicator_name']

        //     );
         

                $total_record = $cursor_count;
                $total_count_filter = $count_filter;


                $dataOut["draw"] = $_POST['draw'];
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $total_count_filter;
                $dataOut["data"] = $document_all;
                //dd($dataOut);
                return response()->json($dataOut);
                if ($request->ajax()) {
                    return response()->json($dataOut);
                }    

            }else{
                $ip = $this->ip;
                $mac = $this->mac;
                $authorization_key = $this->header;
                $url_indicator_load_attributes_tb = $this->url_indicator_load_attributes_tb;

                $draw = $request->draw;
                $row = (int)$request->start;
                $rowperpage = (int)$request->length;
                $reqId = $request->pulse_id;
                $count_page = $request->count_page;

                $request_body_complete = [
                    'draw' => $draw,
                    'row' => $row,
                    'rowperpage' => $rowperpage,
                    'count_page' => $count_page,
                    'reqId' => $reqId,
                ];

                $body_complete = json_encode($request_body_complete);
                $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
                $response_complete = $this -> reconnnect($url_indicator_load_attributes_tb, $form_body_complete, $authorization_key);

                if($response_complete['status_code'] == "200"){
                    $dataOut = $response_complete['data'];
                    return response()->json($dataOut);
                }else{
                    return response()->json($response_complete);
                }
            }
        }


        public function load_pulse_tb(Request $request){
            $role_custom = @check_role_custom();
            if(!$role_custom['indicators']) {
                check_permission403();
            }
            if(TYPE_WEB == 'center'){
                $draw = $_POST['draw'];
                $row = (int)$_POST['start'];
                $rowperpage = (int)$_POST['length'];
                $start =  $row;
                $reqId = $request->pulse_id;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $html = '';
                $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;

                $query = [
                    'main_pulse_id' => $reqId,

                ];

                $options = [
                'skip' => $start,//10
                'limit' => $rowperpage//5
            ];

            if($request->count_page==-1){
                $cursor_count = $fx_otx_events_event_ref->count($query); 
                $count_filter = $cursor_count;
            }else{
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }
            

            
            $cursor = $fx_otx_events_event_ref->find($query,$options);       
            $document_all = $cursor->toArray();

                // set_time_limit(500); 
            
                // $count_doc = count($document_all);

            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $data = array();
            $order_number = $start;

            if($document_all){
                foreach ($document_all as  $value) {
                    $query = [
                        'pulse_id' => $value->pulse_id     
                    ];
                    $document = $col_fx_otx_events->findOne($query,$options);
                    
                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document["name"];
                    $nestedData['groups'] = explode_val($document["groups"],'groups');
                    $nestedData['tags'] = explode_val($document["tags"],'tags');
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document["indicator_count"];
                    $nestedData['public'] = ($document["public"]);
                    $nestedData['is_modified'] = ($document["is_modified"]);
                    $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                    $nestedData['count_view'] = $document["count_view"];
                    $nestedData['pulse_id'] = $document["pulse_id"];

                    $data[] = $nestedData;

                }
            }
            
            $dataOut["draw"] = $draw;
            $dataOut["recordsTotal"] = $cursor_count;
            $dataOut["recordsFiltered"] = $count_filter;
            $dataOut["data"] = $data;
            $dataOut["cursor"] = $cursor;
            return response()->json($dataOut);
        }else{
            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_indicator_load_pulse_tb = $this->url_indicator_load_pulse_tb;

            $draw = $request->draw;
            $row = (int)$request->start;
            $rowperpage = (int)$request->length;
            $reqId = $request->pulse_id;
            $count_page = $request->count_page;

            $request_body_complete = [
                'draw' => $draw,
                'row' => $row,
                'rowperpage' => $rowperpage,
                'count_page' => $count_page,
                'reqId' => $reqId,
            ];

            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_indicator_load_pulse_tb, $form_body_complete, $authorization_key);
            if($response_complete['status_code'] == "200"){
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            }else{
                return response()->json($response_complete);
            }
        }
    }




    public function datatableEvent(Request $request) {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        if(TYPE_WEB == 'center'){
            $columns = array(
                            0 => 'No', // not sort 
                            1 => 'name',
                            2 => 'groups',
                            3 => 'tags',
                            4 => 'industries',
                            5 => 'public',
                            6 => 'is_modified',
                            7 => 'modified',
                            8 => 'indicator_count',
                            9 => 'pulse_id',
                        );  
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];

            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir')=='asc'?1:-1;




            $start =  $row;


            $reqId = $request->pulse_id;
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;


            $options = [
                'projection' => [
                    '_id' => 0,

                    'name' => 1,
                    'groups' => 1,
                    'tags' => 1,
                    'industries' =>1,
                    'public' => 1,
                    'is_modified' => 1,
                    'modified' => 1,
                    'count_view' => 1,
                    'indicator_count' => 1,
                    'pulse_id' => 1,

                ],
                'sort' => [
                    $order => $dir
                ],
                'skip' => $start,
                'limit' => $rowperpage,
            ];

            $query = array( 
                'status' => 1,
                'deleted_at' => null,
            );

            if($request->count_page==-1){
                $cursor_count = $col_fx_otx_events->count($query);
                $count_filter = $cursor_count;
            }else{
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



                //    if(empty($request->input('search.value'))) //internal Search
                //    {
                //         $query = [];
                //         $cursor = $col_fx_otx_events->find($query,$options);
                //    }
                //    else {
                //         $search = $request->input('search.value'); 
                //         $query = [
                //             '$or' => [
                //                 [
                //                     'name' => ['$regex'=>$search ,'$options'=>'i']
                //                 ],
                //                 [
                //                     'groups' => ['$regex'=>$search ,'$options'=>'i']
                //                 ],
                //                 [
                //                     'tags' => ['$regex'=>$search ,'$options'=>'i']
                //                 ],
                //                 [
                //                     'public' => ['$regex'=>$search ,'$options'=>'i']
                //                 ],
                //                 [
                //                     'is_modified' => ['$regex'=>$search ,'$options'=>'i']
                //                 ]
                //             ]
                //         ];


            if($request->keywords||$request->isDateSearch||$request->start_date||$request->end_date||$request->check_published || $request->industries || $request->groups)
            {

                if ($request->keywords) {
                    $query['name'] = ['$regex'=>$request->keywords, '$options' => 'i'];
                            // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                } 
                if ($request->industries) {
                    $query['industries'] = ['$regex'=>$request->industries, '$options' => 'i'];
                }
                if ($request->groups) {
                    $query['groups'] = ['$regex'=>$request->groups, '$options' => 'i'];
                }


                $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

                if($isDateSearch){
                    if ($request->startDate&&$request->endDate) {
                        $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000), '$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                    }else if($request->startDate){
                        $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                    }else if($request->endDate){
                        $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                    }
                }

                if ($request->check_published) {
                    if($request->check_published==1){
                        $query['public'] = 1;
                    }else if($request->check_published==2){
                        $query['public'] = 0;
                    }
                }
                $cursor = $col_fx_otx_events->find($query,$options);
                $count_filter = $col_fx_otx_events->count($query);
            } else {
                $cursor = $col_fx_otx_events->find($query,$options);
            }


            $query['indicator_count'] = ['$ne' => 0];
            $cursor = $col_fx_otx_events->find($query,$options);
            $cursor = $cursor->toArray();

            $data = array();
            $order_number = $start;
            if(!empty($cursor))
            {
                foreach ($cursor as $document)
                {
                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document["name"];
                    $nestedData['groups'] = explode_val($document["groups"],'groups');
                    $nestedData['tags'] = explode_val($document["tags"],'tags');
                    $nestedData['industries'] = explode_val($document["industries"]);
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document["indicator_count"];
                    $nestedData['public'] = ($document["public"]);
                    $nestedData['is_modified'] = ($document["is_modified"]);
                    $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                    $nestedData['count_view'] = $document["count_view"];
                    $nestedData['pulse_id'] = $document["pulse_id"];

                            // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                            // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>


                    $data[] = $nestedData;

                }
            }
            $dataOut["draw"] = $draw;
            $dataOut["recordsTotal"] = $cursor_count;
            $dataOut["recordsFiltered"] = $count_filter;
            $dataOut["data"] = $data;
            $dataOut["cursor"] = $cursor;
            return response()->json($dataOut);
        }else{
            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_indicator_events_table = $this->url_indicator_events_table;

            $columns = array(
                        0 => 'No', // not sort 
                        1 => 'name',
                        2 => 'groups',
                        3 => 'tags',
                        4 => 'industries',
                        5 => 'public',
                        6 => 'is_modified',
                        7 => 'modified',
                        8 => 'indicator_count',
                        9 => 'pulse_id',
                    );  
            $draw = $request->draw;
            $start = (int)$request->start;
            $length = (int)$request->length;
            $count_page = $request->count_page;
            $keywords = $request->keywords;
            $isDateSearch = $request->isDateSearch;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $check_published = $request->check_published;
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir')=='asc'?1:-1;



            $request_body_complete = [
                'draw' => $draw,
                'start' => $start,
                'length' => $length,
                'count_page' => $count_page,
                'keywords' => $keywords,
                'keywords' => $keywords,
                'isDateSearch' => $isDateSearch,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'check_published' => $check_published,
                'order' => $order,
                'dir' => $dir,
            ];
            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_indicator_events_table, $form_body_complete, $authorization_key);
            if($response_complete['status_code'] == 200){
                return response()->json($response_complete['data']);
            }else{
                return response()->json($response_complete);
            }
        }
    }



    public function count_view(Request $request) {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        if(TYPE_WEB == 'center'){
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $request->pulse_id),$options);
            if($document){
                $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                    ['_id' => $document['_id']],
                    ['$set' => [
                        'count_view' => $document['count_view']+1
                    ]
                ]
            );
            }

            if ($request->ajax()) {
                $data = [
                    "count" => $document['count_view']+1,
                ];
                return response()->json($data);
            }
        }else{
            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_indicator_count_view = $this->url_indicator_count_view;


            $request_body_complete = [
                'request' => 'data',
                'pulse_id' => $request->pulse_id
            ];
            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_indicator_count_view, $form_body_complete, $authorization_key);

            if($response_complete['status_code'] == "200"){
                if ($request->ajax()) {
                    $data = [
                        "count" => $response_complete['data']['count'],
                    ];
                    return response()->json($data);
                }
            }else{
                abort(404);
            }
        }
    }


    public function link_tags($id) {

        $data['id'] = $id;
        $data['page'] = langapp('indicators');
        return view('indicators::link_tag')->with($data);
    }


    public function table_tags(Request $request) {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }

            // $row = (int)$_POST['start'];
            // $rowperpage = (int)$_POST['length'];

        $columns = array(
                0 => 'No', // not sort 
                1 => 'name',
                2 => 'groups',
                3 => 'tags',
                4 => 'industries',
                5 => 'public',
                6 => 'is_modified',
                7 => 'modified',
                8 => 'indicator_count',
                9 => 'pulse_id',
            );  
        $draw = $_POST['draw'];
        $row = (int)$_POST['start'];
        $rowperpage = (int)$_POST['length'];

        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir')=='asc'?1:-1;




        $start =  $row;


        $reqId = $request->pulse_id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

        $query = array(
            'tags' => new Regex('^.*'.$request->tags.'.*$', 'i'),
            'status' => 1,
            'deleted_at' => null,
        );

        $options = [
            'projection' => [
                '_id' => 0,
                'name' => 1,
                'groups' => 1,
                'tags' => 1,
                'industries' => 1,
                'public' => 1,
                'is_modified' => 1,
                'modified' => 1,
                'count_view' => 1,
                'pulse_id' => 1,
                'indicator_count' => 1,
            ],
            'sort' => [
                $order => $dir
            ],
            'skip' => $start,
            'limit' => $rowperpage,
        ];



        if($request->count_page==-1){
            $cursor_count = $col_fx_otx_events->count($query);
            $count_filter = $cursor_count;
        }else{
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }

        if($request->keywords||$request->isDateSearch || $request->industries)
        {


            if ($request->keywords) {
                $query['name'] = ['$regex'=>$request->keywords, '$options' => 'i'];
                        // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
            } 
            if ($request->industries) {
                $query['industries'] = ['$regex'=>$request->industries, '$options' => 'i'];
            }
            $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

            if($isDateSearch){
                        // dd($request->startDate);
                if ($request->startDate&&$request->endDate) {

                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000), '$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }else if($request->startDate){
                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                }else if($request->endDate){
                    $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }
            }
            $cursor = $col_fx_otx_events->find($query,$options);
            $count_filter = $col_fx_otx_events->count($query);

        } else {
            $cursor = $col_fx_otx_events->find($query,$options);
        }



        $cursor = $cursor->toArray();

        $data = array();
        $order_number = $start;
        if(!empty($cursor))
        {
            foreach ($cursor as $document)
            {


                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $document["name"];
                $nestedData['groups'] = explode_val($document["groups"],'groups');
                $nestedData['tags'] = explode_val($document["tags"],'tags');
                $nestedData['industries'] = explode_val($document["industries"]);
                $nestedData['attr'] = '';
                $nestedData['attrCount'] = $document["indicator_count"];
                $nestedData['public'] = ($document["public"]);
                $nestedData['is_modified'] = ($document["is_modified"]);
                $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                $nestedData['count_view'] = $document["count_view"];
                $nestedData['pulse_id'] = $document["pulse_id"];

                            // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                            // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>


                $data[] = $nestedData;

            }
        }
        $dataOut["draw"] = $draw;
        $dataOut["recordsTotal"] = $cursor_count;
        $dataOut["recordsFiltered"] = $count_filter;
        $dataOut["data"] = $data;
        $dataOut["cursor"] = $cursor;
        return response()->json($dataOut);

    }

    public function link_group($id) {

        $data['id'] = $id;
        $data['page'] = langapp('indicators');
        return view('indicators::link_group')->with($data);
    }

    public function table_groups(Request $request) {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }

            // $row = (int)$_POST['start'];
            // $rowperpage = (int)$_POST['length'];

        $columns = array(
                0 => 'No', // not sort 
                1 => 'name',
                2 => 'groups',
                3 => 'tags',
                4 => 'industries',
                5 => 'public',
                6 => 'is_modified',
                7 => 'modified',
                8 => 'indicator_count',
                9 => 'pulse_id',
            );  
        $draw = $_POST['draw'];
        $row = (int)$_POST['start'];
        $rowperpage = (int)$_POST['length'];

        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir')=='asc'?1:-1;




        $start =  $row;


        $reqId = $request->pulse_id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

        $query = array(
            'groups' => new Regex('^.*'.$request->tags.'.*$', 'i'),
            'status' => 1,
            'deleted_at' => null,
        );

        $options = [
            'projection' => [
                '_id' => 0,
                'name' => 1,
                'groups' => 1,
                'tags' => 1,
                'industries' =>1,
                'public' => 1,
                'is_modified' => 1,
                'modified' => 1,
                'count_view' => 1,
                'pulse_id' => 1,
                'indicator_count' => 1,
            ],
            'sort' => [
                $order => $dir
            ],
            'skip' => $start,
            'limit' => $rowperpage,
        ];



        if($request->count_page==-1){
            $cursor_count = $col_fx_otx_events->count($query);
            $count_filter = $cursor_count;
        }else{
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }

        if($request->keywords||$request->isDateSearch || $request->industries)
        {


            if ($request->keywords) {
                $query['name'] = ['$regex'=>$request->keywords, '$options' => 'i'];
                    // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
            } 
            if ($request->industries) {
                $query['industries'] = ['$regex'=>$request->industries, '$options' => 'i'];
            }

            $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

            if($isDateSearch){
                        // dd($request->startDate);
                if ($request->startDate&&$request->endDate) {

                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000), '$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }else if($request->startDate){
                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                }else if($request->endDate){
                    $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate)*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }
            }
            $cursor = $col_fx_otx_events->find($query,$options);
            $count_filter = $col_fx_otx_events->count($query);

        } else {
            $cursor = $col_fx_otx_events->find($query,$options);
        }



        $cursor = $cursor->toArray();

        $data = array();
        $order_number = $start;
        if(!empty($cursor))
        {
            foreach ($cursor as $document)
            {


                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $document["name"];
                $nestedData['groups'] = explode_val($document["groups"],'groups');
                $nestedData['tags'] = explode_val($document["tags"],'tags');
                $nestedData['industries'] = explode_val($document["industries"]);
                $nestedData['attr'] = '';
                $nestedData['attrCount'] = $document["indicator_count"];
                $nestedData['public'] = ($document["public"]);
                $nestedData['is_modified'] = ($document["is_modified"]);
                $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                $nestedData['count_view'] = $document["count_view"];
                $nestedData['pulse_id'] = $document["pulse_id"];

                        // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                        // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>


                $data[] = $nestedData;

            }
        }
        $dataOut["draw"] = $draw;
        $dataOut["recordsTotal"] = $cursor_count;
        $dataOut["recordsFiltered"] = $count_filter;
        $dataOut["data"] = $data;
        $dataOut["cursor"] = $cursor;
        return response()->json($dataOut);

    }

    private function reconnnect($url, $form_body, $authorization_key){
        if(TYPE_WEB !== 'center'){
            try{
                $headers = ['Authorization' => 'Bearer ' . $authorization_key];
                $res = $this->client->request('POST', $url,  [
                    'headers' => $headers, 
                    'form_params' => [
                        'data' => $form_body
                    ]
                ]);
                $response = json_decode($res->getBody()->getContents(), true);
                if($response['status_code'] == 200){
                    $decrypt = encrypt_decrypt('decrypt', $response['data'], $authorization_key, $this->ip, $this->mac);
                    $response_data = ['message' => '', 'error' => '', 'status_code' => '200', 'data' => json_decode($decrypt, true)];
                }else{
                    $response_data = ['message' => '', 'error' => 'Not Found', 'status_code' => '404', 'data' => $response];
                }
                return $response_data;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    public function indicator_industries(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        try {
            $Indicatorindustries = IndicatorSummaryYear::where('type', 'industries')->where('status', 1)->where('industries_name', '!=', null)->where('industries_name', '!=', '')->orderBy('order')->select('industries_name')->get();
            $response_data = ['message' => '', 'status_code' => '00', 'data' => $Indicatorindustries];



        } catch (Exception $e) {
            $this->error($e->getMessage());
            $response_data = ['message' => $e->getMessage(), 'status_code' => '01', 'data' => array()];
        }

        return response()->json($response_data);

    }

    public function indicator_group(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['indicators']) {
            check_permission403();
        }
        try {
            $Indicatorgroup = IndicatorSummaryYear::where('type', 'groups')->where('status', 1)->where('industries_name', '!=', null)->where('industries_name', '!=', '')->orderBy('order')->select('industries_name')->get();
            $response_data = ['message' => '', 'status_code' => '00', 'data' => $Indicatorgroup];



        } catch (Exception $e) {
            $this->error($e->getMessage());
            $response_data = ['message' => $e->getMessage(), 'status_code' => '01', 'data' => array()];
        }

        return response()->json($response_data);
    }

    public function insert_tag()
    {
        $data['page'] = langapp('indicators');
        return view('indicators::modal.insert_tag')->with($data);
    }

}
