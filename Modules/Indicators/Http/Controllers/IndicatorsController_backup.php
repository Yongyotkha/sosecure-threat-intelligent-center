<?php

namespace Modules\Indicators\Http\Controllers;

use Modules\SiteSettings\Entities\SiteSettings;
use App\IndicatorSummaryYear;
use App\FXTechniques;
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

use Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Strings;
use App\Entities\TransactionBatchjob;
use \Carbon\Carbon;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Support\Facades\Log;
use App\Services\MispTagService;


use function PHPSTORM_META\type;

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
        if (TYPE_WEB !== 'center') {
            $this->ip = config('app.ip_ad');
            $this->mac = config('app.mac_ad');
            $this->header = config('app.site_key');
            $this->client = new \GuzzleHttp\Client();
            $this->base_url = config('app.url_center') . '/api/v1/' . config('app.mode') . '/' . config('app.site_code');
            $this->url_indicator_events_table = $this->base_url . '/indicator/events_table';
            $this->url_indicator_events = $this->base_url . '/indicator/events';
            $this->url_indicator_events_detail_select = $this->base_url . '/indicator/events_detail_select';
            $this->url_indicator_load_attributes_tb = $this->base_url . '/indicator/events_load_attributes_tb';
            $this->url_indicator_load_pulse_tb = $this->base_url . '/indicator/events_load_pulse_tb';
            $this->url_indicator_count_view = $this->base_url . '/indicator/events_count_view';
        }
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */

    public function events()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
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
        if (TYPE_WEB == 'center') {
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

            $data["attr_all"] = IndicatorSummaryYear::where("type", 'summary_all')->first();
            $data["attr_current"] = IndicatorSummaryYear::where("type", 'summary_current')->first();
            // DB::raw('CONCAT("[",attribute_count, "]") as data2')
            $dataForloop = IndicatorSummaryYear::select('type_name AS name', 'attribute_count AS data')->where("type", 'summary_attr_type')->orderBy('attribute_count', 'desc')->take(10)->get();
            $data["attr_type"] = array();
            foreach ($dataForloop as $document) {
                array_push($data["attr_type"], array('name' => ucwords($document->name), 'data' => [$document->data]));
            }
            $data['SiteSettings'] = $SiteSettings;
            $data['page'] = langapp('indicators');
            if (isset($this->request->Search_Link_All)) {
                $data['Search_Link_All'] = $this->request->Search_Link_All;
            } else {
                $data['Search_Link_All'] = "";
            }
            return view('indicators::events')->with($data); //['a'=>'value']

        } else {
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
            $response_complete = $this->reconnnect($url_indicator_events, $form_body_complete, $authorization_key);

            if ($response_complete['status_code'] == "200") {
                $data = $response_complete['data'];
                return view('indicators::events')->with($data);
            } else {
                abort(404);
            }
        }
    }

    public function events_detail()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $data['page'] = langapp('indicators');
        return view('indicators::events_detail')->with($data);
    }

    public function events_detail_select(Request $request, $id)
    {

        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
            $client = new Client(DB_MONGO_01);
            $collection = $client->sosecure_threatintelligent_dev->fx_otx_events;

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
                $countKey[] = ucwords($key);
                $countVal[] = $value;
            }
            $data['countKey'] = $countKey;
            $data['countVal'] = $countVal;


            $data['indicator_id'] = $request->id;

            $data['type'] = $request->type;
            $data['indicator'] = $request->indicator;

            $data['pulse_id'] = $id;


            if ($request->iframe) {
                return view('indicators::events_detail_search')->withHeaders('X-Frame-Options', 'ALLOWALL')->with($data);
            } else {
                return view('indicators::events_detail')->withHeaders('X-Frame-Options', 'ALLOWALL')->with($data);
            }
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_events_detail_select, $form_body_complete, $authorization_key);
            if ($response_complete['status_code'] == 200) {
                $cursor = $response_complete['data'];
                // dd($cursor);
                $data['otx_events'] = $cursor;
                $data['page'] = langapp('indicators');
                $data['indicator_type_counts'] = count($cursor[0]['indicator_type_counts']);
                $data['count_related_pulse'] = @$cursor[0]['count_related_pulse'];
                $countKey = array();
                $countVal = array();
                foreach ($cursor[0]['indicator_type_counts'] as $key => $value) {
                    $countKey[] = ucwords($key);
                    $countVal[] = $value;
                }
                $data['countKey'] = $countKey;
                $data['countVal'] = $countVal;

                $data['indicator_id'] = $request->id;

                $data['type'] = $request->type;
                $data['indicator'] = $request->indicator;

                $data['pulse_id'] = $id;
                return view('indicators::events_detail')->with($data);
            } else {
                abort(404);
            }
        }
    }

    public function iframe()
    {
        return view('indicators::events_detail_2');
    }

    public function adversaries(Request $request)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_adversaries_related;
        $where = array(
            'pulse_id' => $request->pulse_id,
        );
        $options = [];
        $cursor = $collection->find($where, $options);
        $data = [
            "data" => $cursor->toArray(),
        ];
        return response()->json($data);
    }

    public function malware(Request $request)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_malware_related;
        $where = array(
            'pulse_id' => $request->pulse_id,
        );
        $options = [];
        $cursor = $collection->find($where, $options);
        $data = [
            "data" => $cursor->toArray(),
        ];
        return response()->json($data);
    }

    public function attributes()
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        // $client = new Client('mongodb://10.104.0.10:27017');
        // $collection = $client->sosecure_threatintelligent_dev->fx_otx_type;

        // $query = [
        //     'status' => 1,
        // ];

        // $options = [];

        // $cursor = $collection->find($query, $options);
        // $docs = $cursor->toArray();
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_type;
        $where = array(
            'status' => 1,
        );

        $cursor = $collection->find($where);   //This is the main line
        $data['cursor'] = $cursor->toArray();
        $data['page'] = langapp('indicators');
        //$data['otx_type'] = OTXtypeData::where("status", '=', 1)->get();
        $data['SiteSettings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();
        return view('indicators::attributes')->with($data);
    }

    public function show_detail_indicator(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $data['page'] = langapp('indicators');
        $data['otxid'] = $request->id;
        $data['otxtype'] = $request->type;
        $data['otxindicator'] = $request->indicator;
        return view('indicators::detail_indicators')->with($data);
    }



    public function show_detail_malware(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $data['otxid'] = $request->id;
        $data['otxtype'] = $request->type;
        $data['otxindicator'] = $request->indicator;

        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_malware;
        $where = array(
            'malware_uuid' => urldecode($request->malware_uuid),
        );
        $options = [];
        $cursor = $collection->find($where, $options);
        $data['detail_malware'] = $cursor->toArray();

        $data['page'] = 'Malware Families';
        return view('indicators::detail_malware')->with($data);
    }



    public function show_detail_adversary(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $data['otxid'] = $request->id;
        $data['otxtype'] = $request->type;
        $data['otxindicator'] = $request->indicator;

        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $client = new \MongoDB\Client($DB_MONGO_KEY);
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_adversaries;
        $where = array(
            'adversary_uuid' => $request->adversary_uuid,
        );
        $options = [];
        $cursor = $collection->find($where, $options);
        $data['adversary'] = $cursor->toArray();
        $data['page'] = 'Adversary';
        return view('indicators::detail_adversary')->with($data);
    }



    public function load_general(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $reqType = $request->type;
        $reqIndicator = $request->indicator;
        $reqId = (string)$request->id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $html = '';
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_indicator_detail;

        $options = array(
            'typeMap' => array(
                'root' => 'array',
                'document' => 'array',
            ),
        );

        $document = $col_fx_otx_indicator_detail->findOne(array('indicator_id' => $reqId), $options);
        if (!empty($document)) {


            if (!empty($document["allrow"])) {
                foreach ($document["allrow"] as $key => $value) {
                    if ($key == "LOCATION") {
                        $value = explode("--", $value)[0];
                    } else if ($key == "CREATION DATE" || $key == "LAST MODIFIED DATE" || $key == "LAST ANALYZED DATE" || $key == "Analysis Date") {
                        $value = date_format(date_create($value), 'l jS F Y g:ia');
                    }
                    $html .= '
                    <div class="row m-b-xs">
                    <div class="col-md-12">
                    ' . $key . ': <a >' . $value . '</a>
                    </div>
                    </div>';
                }
            } else if ($reqType == "YARA" && isset($document["ruleRow"])) {
                $html .= '
                <div class="row m-b-xs">
                <div class="col-md-12">
                <a >' . $document["ruleRow"] . '</a>
                </div>
                </div>';
            } else {
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
        if (!$role_custom['indicators']) {
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
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;


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

        if ($request->count_page == -1) {
            $cursor_count = $col_fx_otx_events_indicator_ref->count($query);
            $count_filter = $cursor_count;
            // dd($cursor_count);
        } else {
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }
        $cursor = $col_fx_otx_events_indicator_ref->find($query, $options);
        $document_all = $cursor->toArray();



        $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
        $options = array(
            'typeMap' => array(
                'root' => 'array',
                'document' => 'array',
            ),
        );

        $data = array();
        $order_number = $start;

        if ($document_all) {
            foreach ($document_all as  $value) {
                $query = [
                    'pulse_id' => $value->pulse_id

                ];
                $cursor_2 = $col_fx_otx_events->findOne($query, $options);

                //  dd($value);
                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $cursor_2["name"];
                $nestedData['groups'] = explode_val($cursor_2["groups"], 'groups');
                $nestedData['tags'] = explode_val($cursor_2["tags"], 'tags');
                $nestedData['public'] = ($cursor_2["public"]);
                $nestedData['is_modified'] = ($cursor_2["is_modified"]);
                $nestedData['attrCount'] = $cursor_2["indicator_count"];
                $nestedData['modified'] = change_date_utc_to_thai($cursor_2['modified']);
                $nestedData['count_view'] = @$cursor_2["count_view"];
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
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $reqId = $request->id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $html = '';
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
        $options = [
            'allowDiskUse' => TRUE
        ];

        $pipeline = [
            [
                '$project' => [
                    '_id' => '$_id',
                    'a' => '$$ROOT'
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'a.pulse_id',
                    'from' => 'fx_otx_events',
                    'foreignField' => 'pulse_id',
                    'as' => 'b'
                ]
            ],
            [
                '$unwind' => [
                    'path' => '$b',
                    'preserveNullAndEmptyArrays' => TRUE
                ]
            ],
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
        if (!$role_custom['indicators']) {
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
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");

        // $DB_MONGO_KEY = "mongodb://10.104.0.7:27017";
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent_dev->fx_transaction_otx_indicators_data;

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
            'skip' => ($request->page - 1) * PAGINATE_NUM,
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
                    $_search['indicator'] = ['$regex' => $request->keywords, '$options' => 'i'];
                    // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                }

                if ($request->type) {

                    $_search['type'] = ['$in' => $request->type];
                    // $_search =  array_merge($_search, array('type' => ['$in'=>$request->type]));
                }

                $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);
                if ($isDateSearch) {
                    if ($request->startDate && $request->endDate) {
                        $_search['updated_at'] = ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format) * 1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format) * 1000)];
                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                    } else if ($request->startDate) {
                        $_search['updated_at'] = ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format) * 1000)];
                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                    } else if ($request->endDate) {
                        $_search['updated_at'] = ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format) * 1000)];
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
            if ($data->type == "CIDR"  || $data->type == "FileHash-IMPHASH" || $data->type == "FileHash-PEHASH" || $data->type == "FilePath" || $data->type == "Mutex" || $data->type == "URI" || $data->type == "JA3" || $data->type == "osquery") {
                $linkIndicator =  '<a>';
            } else {
                $linkIndicator =  '<a href="' . route('indicators.detail_indicator') . '?id=' . $data->indicator_id . '&type=' . $data->type . '&indicator=' . $data->indicator . '">';
            }
            $html .= '<ul class="list-indicators text-elip">
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
        if (!$role_custom['indicators']) {
            check_permission403();
        }

        // $DB_MONGO_KEY = env("DB_MONGO_DEV", "mongodb://10.104.0.7:27017");

        // $client = new \MongoDB\Client($DB_MONGO_KEY);

        $f_search = $request->f_search;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $keyword = $request->keyword;

        if ($start_date) {
            $date_start_explode = explode(" ", $start_date);
            $date_start_date = @$date_start_explode[0];
            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
            // dd($date_start_time);
            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            // dd($date_start_date_format);
            $date_start_time_time = date("H:i", strtotime($date_start_time));
            $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
            // dd($date_start_time_time);


            $date_end_explode = explode(" ", $end_date);
            $date_end_date = @$date_end_explode[0];
            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
            // dd($date_end_time);
            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
            $date_end_time_time = date("H:i", strtotime($date_end_time));
            $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
            // dd($date_end_time_time);



            $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($date_start_datetime_format) * 1000);
            $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($date_end_datetime_format) * 1000);
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
        $db_name = 'sosecure_threatintelligent_dev';
        $db = $client->$db_name;
        $collection = $db->fx_otx_events;
        // $where = array(
        //     'status' => 1,
        // );

        if ($f_search == 'true') {
            $query = [
                '$and' => [
                    [
                        '$or' => [
                            [
                                '$and' => [
                                    [
                                        'name' => new Regex('^.*' . $keyword . '.*$', 'i')
                                    ],
                                    [
                                        'status' => 1
                                    ]
                                ]
                            ],
                            [
                                '$and' => [
                                    [
                                        'tags' => new Regex('^.*' . $keyword . '.*$', 'i')
                                    ],
                                    [
                                        'status' => 1
                                    ]
                                ]
                            ],
                            [
                                '$and' => [
                                    [
                                        'groups' => new Regex('^.*' . $keyword . '.*$', 'i')
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
                            '$gte' => $dateStart //new UTCDateTime(-15644559600000)
                        ]
                    ],
                    [
                        'created_at' => [
                            '$lte' => $dateEnd //new UTCDateTime(-15644559600000)
                        ]
                    ]
                ]
            ];
        } else {
            $query = [
                'status' => 1 //,
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
            'skip' => $start, //10
            'limit' => $perpage //5
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

        $offset = ($page - 1) * $perpage;
        $previous_page = $page - 1;
        $next_page = $page + 1;
        $adjacents = "2";


        $pagination = '<nav>
            <ul class="pagination">';

        if ($page > 1) {
            $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">First Page</a></li>';
        }

        $pagination .= '<li onclick="pagination_goto(' . $previous_page . ')" data-page="' . @$previous_page . '"';
        if ($page <= 1) {
            $pagination .= 'class="disabled"';
        }
        $pagination .= '>';

        $pagination .= '<a ';
        if ($page > 1) {
            $pagination .= 'href="#"';
        }

        $pagination .= '>Previous</a></li>';

        if ($total_page <= 10) {
            for ($counter = 1; $counter <= $total_page; $counter++) {
                if ($counter == $page) {
                    $pagination .= '<li class="active"><a>' . $counter . '</a></li>';
                } else {
                    $pagination .= '<li onclick="pagination_goto(' . $counter . ')" data-page="' . $counter . '"><a href="#">' . $counter . '</a></li>';
                }
            }
        } elseif ($total_page > 10) {
            if ($page <= 4) {
                for ($counter = 1; $counter < 8; $counter++) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>' . $counter . '</a></li>';
                    } else {
                        $pagination .= '<li onclick="pagination_goto(' . $counter . ')" data-page="' . $counter . '"><a href="#">' . $counter . '</a></li>';
                    }
                }
                $pagination .= '<li><a>...</a></li>';
                $pagination .= '<li onclick="pagination_goto(' . $second_last . ')" data_page="' . $second_last . '"><a href="#">' . $second_last . '</a></li>';
                $pagination .= '<li onclick="pagination_goto(' . $total_page . ')" data-page="' . $total_page . '"><a href="#">' . $total_page . '</a></li>';
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
                        $pagination .= '<li class="active"><a>' . $counter . '</a></li>';
                    } else {
                        $pagination .= '<li onclick="pagination_goto(' . $counter . ')" data-page="' . $counter . '"><a href="#">' . $counter . '</a></li>';
                    }
                }
                $pagination .= "<li><a>...</a></li>";
                $pagination .= '<li onclick="pagination_goto(' . $second_last . ')" data-page="' . $second_last . '"><a href="#">' . $second_last . '</a></li>';
                $pagination .= '<li onclick="pagination_goto(' . $total_page . ')" data-page="' . $total_page . '"><a href="#">' . $total_page . '</a></li>';
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
                        $pagination .= '<li class="active"><a>' . $counter . '</a></li>';
                    } else {
                        $pagination .= '<li onclick="pagination_goto(' . $counter . ')" data-page="' . $counter . '"><a href="#">' . $counter . '</a></li>';
                    }
                }
            }
        }

        $pagination .= '<li onclick="pagination_goto(' . $next_page . ')" data-page="' . @$next_page . '"';

        if ($page >= $total_page) {
            $pagination .= 'class="disabled"';
        }
        $pagination .= ' >';

        $pagination .= '<a ';
        if ($page < $total_page) {
            $pagination .= 'href="#"';
        }
        $pagination .= '>Next</a></li>';

        if ($page < $total_page) {
            $pagination .= '<li onclick="pagination_goto(' . $total_page . ')" data-page="' . $total_page . '"><a href="#">Last &rsaquo;&rsaquo;</a></li>';
        }
        $pagination .= '</ul></nav>';



        $start_first_in_page = $start + 1;
        $end_last_in_page = $start + $perpage;

        $showing_amount_text = '<div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;">Showing ' . $start_first_in_page . ' to ' . $end_last_in_page . ' of ' . $total_record . ' entries</div>';



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
                <input value="' . $document['_id'] . '" type="checkbox" />
                <span class="label-text"></span>
                </label>
                </td>-->
                <td>' . $i . '</td>
                <td style="width: 500px;">' . $document['name'] . '</td>
                <td>' . explode_val($document['groups'], 'groups') . '</td>
                <td>' . explode_val($document['tags'], 'tags') . '</td>
                <!--<td>
                <a href="#"></a>
                </td>-->
                <td>' . check_publish($document['public']) . '</td>
                <td>' . check_last_status($document['is_modified']) . '</td>
                <td>' . change_date_utc_to_thai($document['modified']) . '</td>
                <td>' . $document['count_view'] . '</td>
                <td>
                <a href="' . route('indicators.events_detail_select', ['id' => $document['pulse_id']]) . '" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
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

    public function table_summary(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        $query_summary = "select t1.year,t1.month,group_industries_name,group_sumc from 
        (
            (
            SELECT year,month,group_concat(industries_name order by sumc DESC SEPARATOR '\n') as group_industries_name
            FROM 
            (
                SELECT year,month,industries_name, sum(attribute_count) as sumc
                FROM 
                (
                    SELECT year,month,industries_name,attribute_count 
                    FROM fx_indicator_summary_year
                    where type='attribute_type' and status=1
                    order by attribute_count DESC
                ) as a1
                group by year,month,industries_name
                order by sumc desc
            ) as tab
            group by year,month
            
            ) as t1
        inner join 
            (
            SELECT year,month,group_concat(sumc order by sumc DESC SEPARATOR '\n') as group_sumc 
            FROM 
            (
                SELECT year,month,industries_name,sum(attribute_count) as sumc
                FROM 
                (
                    SELECT year,month,industries_name,attribute_count 
                    FROM fx_indicator_summary_year
                    where type='attribute_type' and status=1
                    order by attribute_count DESC
                ) as a2
                group by year,month,industries_name
                order by sumc desc
            ) as tab
            group by year,month
            ) as t2
        ON (t1.year = t2.year and t1.month = t2.month)
        )
        order by year Desc,month Desc";
        $summary = DB::select($query_summary);
        if (!empty($summary)) {
            foreach ($summary as $records) {
                // $records->month = $arr_months[$records->month];
                $records->group_sumc = preg_replace_callback("/[0-9]+/", function ($matches) {
                    return number_format($matches[0], 0, ',', ',');
                }, $records->group_sumc);
            }
        }
        $Transaction = TransactionBatchjob::where('mode', 'indicator_summary_type')->first();

        if (empty($Transaction)) {
            $Transaction = date("Y-m-d H:i:s");
            return DataTables::of($summary)->with('dateday', $Transaction)->make(true);
        }
        return DataTables::of($summary)->with('dateday', $Transaction->transcation_date)->make(true);
    }

    public function table_summary_export(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        // $fileName = 'Indicators-Summary Type.csv';
        $data_query = DB::table('indicator_summary_year')->select('year', 'month', 'industries_name as attribute_type', DB::raw("SUM(attribute_count) as count"))
            ->where("status", "1")
            ->where("type", "attribute_type")
            ->where("year", ">=", $request->minyear)
            ->where("year", "<=", $request->maxyear)
            ->where("month", ">=", $request->minmonth)
            ->where("month", "<=", $request->maxmonth)
            ->groupBy("year", "month", "attribute_type")
            ->orderBy("year", "desc")
            ->orderBy("month", "desc")
            ->orderBy("count", "desc")
            ->get();
        // $headers = array(
        //     "Content-type"        => "text/csv",
        //     "Content-Disposition" => "attachment; filename=$fileName",
        //     "Pragma"              => "no-cache",
        //     "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //     "Expires"             => "0"
        // );
        // $columns = array('year', 'month', 'industries_name', 'sumc');
        // $callback = function () use ($data_query, $columns) {
        //     $file = fopen('php://output', 'w');
        //     fputcsv($file, $columns);

        //     foreach ($data_query as $task) {
        //         $row['year']  = $task->year;
        //         $row['month']    = $task->month;
        //         $row['industries_name']    = $task->industries_name;
        //         $row['sumc']  = $task->sumc;
        //         fputcsv($file, array($row['year'], $row['month'], $row['industries_name'], $row['sumc']));
        //     }

        //     fclose($file);
        // };
        // return  response()->stream($callback, 200, $headers);
        return response()->json($data_query->toArray());
    }

    public function load_attributes_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        // dd( $_POST['order']);
        if (TYPE_WEB == 'center') {
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];



            $start =  $row;


            $reqId = $request->pulse_id;
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $html = '';
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;

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
                'sort' => ['updated_at' => -1],
                'projection' => [
                    '_id' => 1,
                    'indicator_id' => 1,
                    'pulse_id' => 1,
                    'created' => 1,
                    'created_at' => 1,
                    'created_by' => 1,
                    'deleted_at' => 1,
                    'expiration' => 1,
                    'indicator' => 1,
                    'is_active' => 1,
                    'pulse_modified' => 1,
                    'role' => 1,
                    'source' => 1,
                    'status' => 1,
                    'transaction_date' => 1,
                    'type' => 1,
                    'updated_at' => 1,
                    'updated_by' => 1,
                    'is_count_attr' => 1,
                    'tags' => 1,
                    'attribute_score' => 1,
                    'attribute_serverity' => 1
                ],
                'typeMap' => [  // 👈 เพื่อให้ใช้งาน array_key_exists ได้ใน PHP
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array'
                ]
            ];




            if ($request->count_page == -1) {
                //$cursor_count = $col_fx_otx_events_indicator_ref->count($query);
                $cursor_count = $request->total_record;
                $count_filter = $cursor_count;
            } else {
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



            $cursor = $col_fx_otx_events_indicator_ref->find($query, $options);
            $document_all = $cursor->toArray();
            foreach ($document_all as &$item) {
                if (is_array($item) && !array_key_exists('tags', $item)) {
                    $item['tags'] = '';
                }
            }
            unset($item);


            //foreach ($document_all as &$item) {
            // $query = ['indicator_id' => $item['indicator_id']];
            //  $detail = $col_fx_otx_events_indicator_ref->findOne($query);

            ///     $item['tags'] = isset($detail['tags']) ? $detail['tags'] : '';
            //  }
            //  unset($item);

            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_indicator_detail;
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
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_load_attributes_tb, $form_body_complete, $authorization_key);

            if ($response_complete['status_code'] == "200") {
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            } else {
                return response()->json($response_complete);
            }
        }
    }


    public function load_pulse_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];
            $start =  $row;
            $reqId = $request->pulse_id;

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $html = '';
            $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_event_ref;

            $query = [
                'main_pulse_id' => $reqId,

            ];

            $options = [
                'skip' => $start, //10
                'limit' => $rowperpage //5
            ];

            if ($request->count_page == -1) {
                $cursor_count = $fx_otx_events_event_ref->count($query);
                $count_filter = $cursor_count;
            } else {
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



            $cursor = $fx_otx_events_event_ref->find($query, $options);
            $document_all = $cursor->toArray();

            // set_time_limit(500); 

            // $count_doc = count($document_all);

            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $data = array();
            $order_number = $start;

            if ($document_all) {
                foreach ($document_all as  $value) {
                    $query = [
                        'pulse_id' => $value->pulse_id
                    ];
                    $document = $col_fx_otx_events->findOne($query, $options);

                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document["name"];
                    $nestedData['groups'] = explode_val($document["groups"], 'groups');
                    $nestedData['tags'] = explode_val($document["tags"], 'tags');
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document["indicator_count"];
                    $nestedData['public'] = ($document["public"]);
                    $nestedData['is_modified'] = ($document["is_modified"]);
                    $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                    $nestedData['count_view'] = @$document["count_view"];
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
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_load_pulse_tb, $form_body_complete, $authorization_key);
            if ($response_complete['status_code'] == "200") {
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            } else {
                return response()->json($response_complete);
            }
        }
    }

    public function datatableEvent(Request $request)
    {

        $input = $request->all();
        // dd($input);

        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
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
                10 => 'creator_org',
            );
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];

            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir') == 'asc' ? 1 : -1;




            $start =  $row;


            $reqId = $request->pulse_id;
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;


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
                    'indicator_count' => 1,
                    'pulse_id' => 1,
                    'creator_org' => 1,

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

            if ($request->count_page == -1) {
                $cursor_count = $col_fx_otx_events->count($query);
                $count_filter = $cursor_count;
            } else {
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


            if ($request->keywords || $request->isDateSearch || $request->start_date || $request->end_date || $request->check_published || $request->industries || $request->groups) {

                if ($request->keywords) {
                    $query['name'] = ['$regex' => $request->keywords, '$options' => 'i'];
                    // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                }
                if ($request->industries) {
                    $query['industries'] = ['$regex' => $request->industries, '$options' => 'i'];
                }
                if ($request->groups) {
                    $query['groups'] = ['$regex' => $request->groups, '$options' => 'i'];
                }


                $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

                if ($isDateSearch) {
                    if ($request->startDate && $request->endDate) {
                        $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000), '$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                    } else if ($request->startDate) {
                        $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000)];
                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                    } else if ($request->endDate) {
                        $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                        // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                    }
                }

                if ($request->check_published) {
                    if ($request->check_published == 1) {
                        $query['public'] = 1;
                    } else if ($request->check_published == 2) {
                        $query['public'] = 0;
                    }
                }
                $cursor = $col_fx_otx_events->find($query, $options);
                $count_filter = $col_fx_otx_events->count($query);
            } else {
                $cursor = $col_fx_otx_events->find($query, $options);
            }


            $query['indicator_count'] = ['$ne' => 0];
            $cursor = $col_fx_otx_events->find($query, $options);
            $cursor = $cursor->toArray();

            // dd($cursor);
            $data = array();
            $order_number = $start;
            if (!empty($cursor)) {
                $name = [];

                foreach ($cursor as $document_2) {
                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document_2["name"];
                    $nestedData['groups'] = explode_val($document_2["groups"], 'groups');
                    $nestedData['tags'] = explode_val($document_2["tags"], 'tags');
                    $nestedData['tags_list'] = $document_2["tags"];
                    $nestedData['industries'] = explode_val($document_2["industries"]);
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document_2["indicator_count"];
                    $nestedData['public'] = ($document_2["public"]);
                    $nestedData['is_modified'] = ($document_2["is_modified"]);
                    // $nestedData['modified'] = change_date_utc_to_thai($document_2['modified']);
                    $nestedData['modified'] = change_date_thai_tummai($document_2['modified']);
                    $nestedData['count_view'] = @$document_2["count_view"];
                    $nestedData['pulse_id'] = $document_2["pulse_id"];
                    $nestedData['creator_org'] =   isset($document_2["creator_org"]) ? $document_2["creator_org"] : null;

                    //------------------------------------------------------
                    $DB_MONGO_KEY = env("DB_MONGO_DEV");
                    $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                    if (app()->environment('local')) {
                        $collection = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                        $collection_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
                    } else {
                        $collection = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                        $collection_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
                    }

                    $query_actor = [
                        'pulse_id' => $document_2['pulse_id'],
                        'mode' => 'indicator',
                        'join' => 'actor',
                        'delete_at' => null
                    ];
                    $option_actor = [];

                    $result_actor = $collection_related->find($query_actor, $option_actor);
                    $final_actor = $result_actor->toArray();
                    $count_actor = count($final_actor);

                    $nestedData['actor'] = $final_actor;
                    $nestedData['count_actor'] = $count_actor;

                    foreach (@$final_actor as $sel_data_act) {
                        $query_sel_act = [
                            'adversary_uuid' => $sel_data_act['adversary_uuid']
                        ];
                        $option_sel_act = [];
                        $result_sel_act = $collection->findOne($query_sel_act, $option_sel_act);
                        if (@$result_sel_act['logo']) {
                            $nestedData['logo'][] = $result_sel_act['logo'];
                        } else {
                            $nestedData['logo'][] = '/asset_salepage/images/AgentBasedDetection.png';
                        }
                    }

                    $query_camp = [
                        'pulse_id' => $document_2['pulse_id'],
                        'mode' => 'indicator',
                        'join' => 'campainge',
                        'delete_at' => null
                    ];
                    $option_camp = [];

                    $result_camp = $collection_related->find($query_camp, $option_camp);
                    $final_camp = $result_camp->toArray();
                    $count_camp = count($final_camp);

                    $nestedData['camp'] = $final_camp;
                    $nestedData['count_camp'] = $count_camp;
                    // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                    // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>

                    $data[] = $nestedData;
                }
                // dd($data);
            }
            $dataOut["draw"] = $draw;
            $dataOut["recordsTotal"] = $cursor_count;
            $dataOut["recordsFiltered"] = $count_filter;
            $dataOut["data"] = $data;
            $dataOut["cursor"] = $cursor;
            return response()->json($dataOut);
        } else {
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
                10 => 'creator_org',

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
            $dir = $request->input('order.0.dir') == 'asc' ? 1 : -1;



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
            $response_complete = $this->reconnnect($url_indicator_events_table, $form_body_complete, $authorization_key);
            if ($response_complete['status_code'] == 200) {
                return response()->json($response_complete['data']);
            } else {
                return response()->json($response_complete);
            }
        }
    }

    public function count_view(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $request->pulse_id), $options);
            if ($document) {
                $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                    ['_id' => $document['_id']],
                    [
                        '$set' => [
                            'count_view' => $document['count_view'] + 1
                        ]
                    ]
                );
            }

            if ($request->ajax()) {
                $data = [
                    "count" => $document['count_view'] + 1,
                ];
                return response()->json($data);
            }
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_count_view, $form_body_complete, $authorization_key);

            if ($response_complete['status_code'] == "200") {
                if ($request->ajax()) {
                    $data = [
                        "count" => $response_complete['data']['count'],
                    ];
                    return response()->json($data);
                }
            } else {
                abort(404);
            }
        }
    }

    public function new_link_tags(Request $request)
    {

        $data['id'] = $request->id;
        $data['page'] = langapp('indicators');
        return view('indicators::link_tag')->with($data);
    }


    public function table_tags(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
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
        $dir = $request->input('order.0.dir') == 'asc' ? 1 : -1;




        $start =  $row;


        $reqId = $request->pulse_id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;

        $query = array(
            'tags' => new Regex('^.*' . $request->tags . '.*$', 'i'),
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



        if ($request->count_page == -1) {
            $cursor_count = $col_fx_otx_events->count($query);
            $count_filter = $cursor_count;
        } else {
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }

        if ($request->keywords || $request->isDateSearch || $request->industries) {


            if ($request->keywords) {
                $query['name'] = ['$regex' => $request->keywords, '$options' => 'i'];
                // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
            }
            if ($request->industries) {
                $query['industries'] = ['$regex' => $request->industries, '$options' => 'i'];
            }
            $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

            if ($isDateSearch) {
                // dd($request->startDate);
                if ($request->startDate && $request->endDate) {

                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000), '$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                } else if ($request->startDate) {
                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                } else if ($request->endDate) {
                    $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }
            }
            $cursor = $col_fx_otx_events->find($query, $options);
            $count_filter = $col_fx_otx_events->count($query);
        } else {
            $cursor = $col_fx_otx_events->find($query, $options);
        }



        $cursor = $cursor->toArray();

        $data = array();
        $order_number = $start;
        if (!empty($cursor)) {
            foreach ($cursor as $document) {


                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $document["name"];
                $nestedData['groups'] = explode_val($document["groups"], 'groups');
                $nestedData['tags'] = explode_val($document["tags"], 'tags');
                $nestedData['industries'] = explode_val($document["industries"]);
                $nestedData['attr'] = '';
                $nestedData['attrCount'] = $document["indicator_count"];
                $nestedData['public'] = ($document["public"]);
                $nestedData['is_modified'] = ($document["is_modified"]);
                $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                $nestedData['count_view'] = @$document["count_view"];
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

    public function link_group($id)
    {

        $data['id'] = $id;
        $data['page'] = langapp('indicators');
        return view('indicators::link_group')->with($data);
    }

    public function new_link_group(Request $request)
    {

        $data['id'] = $request->id;
        $data['page'] = langapp('indicators');
        return view('indicators::link_group')->with($data);
    }

    public function table_groups(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
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
        $dir = $request->input('order.0.dir') == 'asc' ? 1 : -1;




        $start =  $row;


        $reqId = $request->pulse_id;
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;

        $query = array(
            'groups' => new Regex('^.*' . $request->tags . '.*$', 'i'),
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



        if ($request->count_page == -1) {
            $cursor_count = $col_fx_otx_events->count($query);
            $count_filter = $cursor_count;
        } else {
            $cursor_count = $request->count_page;
            $count_filter = $cursor_count;
        }

        if ($request->keywords || $request->isDateSearch || $request->industries) {


            if ($request->keywords) {
                $query['name'] = ['$regex' => $request->keywords, '$options' => 'i'];
                // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
            }
            if ($request->industries) {
                $query['industries'] = ['$regex' => $request->industries, '$options' => 'i'];
            }

            $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

            if ($isDateSearch) {
                // dd($request->startDate);
                if ($request->startDate && $request->endDate) {

                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000), '$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                } else if ($request->startDate) {
                    $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                } else if ($request->endDate) {
                    $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                    // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                }
            }
            $cursor = $col_fx_otx_events->find($query, $options);
            $count_filter = $col_fx_otx_events->count($query);
        } else {
            $cursor = $col_fx_otx_events->find($query, $options);
        }



        $cursor = $cursor->toArray();

        $data = array();
        $order_number = $start;
        if (!empty($cursor)) {
            foreach ($cursor as $document) {


                $order_number++;
                $nestedData['No'] = $order_number;
                $nestedData['name'] = $document["name"];
                $nestedData['groups'] = explode_val($document["groups"], 'groups');
                $nestedData['tags'] = explode_val($document["tags"], 'tags');
                $nestedData['industries'] = explode_val($document["industries"]);
                $nestedData['attr'] = '';
                $nestedData['attrCount'] = $document["indicator_count"];
                $nestedData['public'] = ($document["public"]);
                $nestedData['is_modified'] = ($document["is_modified"]);
                $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                $nestedData['count_view'] = @$document["count_view"];
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

    private function reconnnect($url, $form_body, $authorization_key)
    {
        if (TYPE_WEB !== 'center') {
            try {
                $headers = ['Authorization' => 'Bearer ' . $authorization_key];
                $res = $this->client->request('POST', $url,  [
                    'headers' => $headers,
                    'form_params' => [
                        'data' => $form_body
                    ]
                ]);
                $response = json_decode($res->getBody()->getContents(), true);
                if ($response['status_code'] == 200) {
                    $decrypt = encrypt_decrypt('decrypt', $response['data'], $authorization_key, $this->ip, $this->mac);
                    $response_data = ['message' => '', 'error' => '', 'status_code' => '200', 'data' => json_decode($decrypt, true)];
                } else {
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
        if (!$role_custom['indicators']) {
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

    public function indicator_group(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
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

    public function insert_tag(Request $request)
    {
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);

        if (app()->environment('local')) {
            $select_actors = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
            $select_campainge = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
        } else {
            $select_actors = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
            $select_campainge = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
        }
        // dd($request -> pulse_id);

        $fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
        $query = [
            'pulse_id' => $request->pulse_id,
        ];
        $options = [];
        $cursor = $fx_otx_events->find($query, $options);
        $events = $cursor->toArray();
        $data['events'] = $events;

        $query_actor = [
            'pulse_id' => $request->pulse_id,
            'mode' => 'indicator',
            'join' => 'actor',
            'delete_at' => null
        ];
        $options_actor = [];
        $connection_actor = $select_actors->find($query_actor, $options_actor);
        if ($connection_actor != null) {
            $actors = $connection_actor->toArray();
            $data['actors'] = $actors;
        } else {
            $data['actors'] = null;
        }

        // dd($data);
        $query_campainge = [
            'pulse_id' => $request->pulse_id,
            'mode' => 'indicator',
            'join' => 'campainge',
            'delete_at' => null
        ];
        $option_campainge = [];

        $connection_campainge = $select_actors->find($query_campainge, $option_campainge);
        $campainge = $connection_campainge->toArray();

        if (count($campainge) > 0) {
            foreach ($campainge as $data_campainge) {
                $data['campainge'][] = $data_campainge['adversary_uuid'];
            }
            // $data['campainge'] = $campainge;
        } else {
            $data['campainge'] = null;
        }

        $query_techniques = FXTechniques::orderBy('group', 'ASC')->get();
        $data['techniques'] = $query_techniques;

        // $query_techniques = [
        //     'adversary_uuid' => $request -> pulse_id,
        //     'mode' => 'indicator',
        //     'join' => 'techniques'
        // ];
        // $options_techniques = [];
        // $connection_techniques = $select_techniques->find($query_techniques,$options_techniques);     
        // if($connection_actor != null)
        // {
        //     $techniques = $connection_techniques->toArray();
        //     $data['techniques'] = $techniques;
        // }
        // else
        // {
        //     $data['techniques'] = null;
        // }  

        $query_master_campainge = [
            'status' => '1',
            'delete_at' => null
        ];
        $option_master_campainge = [];

        $connection_master_campainge = $select_campainge->find($query_master_campainge, $option_master_campainge);

        if ($connection_master_campainge != null) {
            $master_campainge = $connection_master_campainge->toArray();
            $data['master_campainge'] = $master_campainge;
        } else {
            $data['master_campainge'] = null;
        }

        // dd($data['campainge']);

        $data['page'] = langapp('indicators');
        return view('indicators::modal.insert_tag')->with($data);
    }


    public function save_table_tags(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
        $options = array(
            'typeMap' => array(
                'root' => 'array',
                'document' => 'array',
            ),
        );
        $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $request->pulse_id), $options);
        if ($document) {
            $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                ['_id' => $document['_id']],
                [
                    '$set' => [
                        'tags' => $request->tags_events,
                    ]
                ]
            );
        }

        //---------------------------------------------------------------------------------------------------
        if (app()->environment('local')) {
            $insert_adversaries_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;
            $collection_campaign = $clientMD->sosecure_threatintelligent_dev->fx_otx_campaign;
        } else {
            $insert_adversaries_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries_related;
            $collection_campaign = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_campaign;
        }

        $query_delete = array(
            'pulse_id' => $document['pulse_id'],
            'pulse_name' => $document['name'],
            'mode' => 'indicator',
            'join' => 'actor'
        );
        $option_delete = [];
        $result_delete = $insert_adversaries_related->find($query_delete, $option_delete);
        $final_delete = $result_delete->toArray();
        $count_actor = count($final_delete);

        if ($count_actor > 0) {
            $update_result_related = $insert_adversaries_related->updateMany(
                $query_delete,
                [
                    '$set' =>
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );
        }

        if (@$request->category_actor) {

            foreach ($request->category_actor as $data_actor) {
                $add_actor = $data_actor;
                // dd($add_actor);
                if (app()->environment('local')) {
                    $indicator_actor_related = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries;
                } else {
                    $indicator_actor_related = $clientMD->sosecure_threatintelligent_dev_test->fx_otx_adversaries;
                }
                $query_actor_related = [
                    'adversary_uuid' => $add_actor
                ];
                $option_actor_related = [];

                $query_actor = $indicator_actor_related->findOne($query_actor_related, $option_actor_related);
                // dd($query_actor['name']);
                $data_actor_related = array(
                    'adversary_uuid' => $add_actor,
                    'adversary_name' => $query_actor['name'],
                    'pulse_id' => $document['pulse_id'],
                    'pulse_name' => $document['name'],
                    'mode' => 'indicator',
                    'join' => 'actor',
                    'modified' => $date_now,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'updated_at' => $date_now,
                    'updated_by' => 'system'
                );

                $insert_adversaries_related->insertOne($data_actor_related);
            }
        }

        $query_delete_camp = array(
            'pulse_id' => $document['pulse_id'],
            'pulse_name' => $document['name'],
            'mode' => 'indicator',
            'join' => 'campainge'
        );
        $option_delete_camp = [];
        $result_delete_camp = $insert_adversaries_related->find($query_delete_camp, $option_delete_camp);
        $final_delete_camp = $result_delete_camp->toArray();
        $count_camp = count($final_delete_camp);

        if ($count_camp > 0) {
            $update_result_related = $insert_adversaries_related->updateMany(
                $query_delete_camp,
                [
                    '$set' =>
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );
        }

        if (@$request->category_campaign) {

            foreach ($request->category_campaign as $data_campaign) {
                $add_campaign = $data_campaign;

                $query_campaign_related = [
                    'campainge_uuid' => $add_campaign
                ];
                $option_campaign_related = [];

                $query_campaign = $collection_campaign->findOne($query_campaign_related, $option_campaign_related);

                $data_campaign_related = array(
                    'adversary_uuid' => $query_campaign['campainge_uuid'],
                    'adversary_name' => $query_campaign['name'],
                    'pulse_id' => $document['pulse_id'],
                    'pulse_name' => $document['name'],
                    'mode' => 'indicator',
                    'join' => 'campainge',
                    'modified' => $date_now,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'updated_at' => $date_now,
                    'updated_by' => 'system'
                );

                $insert_adversaries_related->insertOne($data_campaign_related);
            }
        }

        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('indicators.events'),
            ],
            true,
            Response::HTTP_OK
        );
    }
    public function indicator_public(Request $request)
    {

        try {
            $input = $request->all();
            // dd($input);
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $request->pulse_id), $options);
            if ($document) {
                $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                    ['_id' => $document['_id']],
                    [
                        '$set' => [
                            'public' => $request->is_public,
                        ]
                    ]
                );

                //ส่งค่าไปบันทึกที่ MISP
                if ($document['source'] == "misp") {
                    $input = $document['pulse_id'];
                    $parts = explode('.', $input);
                    $id = $parts[1]; // ได้ค่า '33421'
                    DB::connection('mysql_misp')
                        ->table('events')
                        ->where('id', $id)
                        ->update([
                            'published' => $request->is_public,
                        ]);
                }
                if ($document['source'] == "otx.alienvault") {
                    $uuid =    isset($document['mips_uuid']) ? $document['mips_uuid'] : '';
                    if ($uuid) {
                        DB::connection('mysql_misp')
                            ->table('events')
                            ->where('uuid', $uuid)
                            ->update([
                                'published' => $request->is_public,
                            ]);
                    }
                }
            }
            http: //127.0.0.1:8000/phishing_detection




            $response_data = ['message' => '', 'status_code' => '00', 'data' => ''];
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $response_data = ['message' => $e->getMessage(), 'status_code' => '01', 'data' => array()];
        }
        return response()->json($response_data);
    }
    public function indicator_update_tags(Request $request)
    {
        try {
            $input = $request->all();
            // dd($input);
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $request->pulse_id), $options);
            if ($document) {
                $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                    ['_id' => $document['_id']],
                    [
                        '$set' => [
                            'tags' => $request->tags,
                        ]
                    ]
                );
            }

            //ส่งค่าไปบันทึกที่ MISP
            if ($document['source'] == "misp") {
                $input = $document['pulse_id'];
                $parts = explode('.', $input);
                $pulseId = $parts[1]; // ได้ค่า '33421'
                $color = "#ffffff";

                //ลบก่อน
                DB::connection('mysql_misp')->table('event_tags')
                    ->where('event_id', '=', $pulseId)
                    ->delete();

                $tags_string = $request->tags;
                $array = explode(",", $tags_string);

                foreach ($array as $index => $tag_value) {
                    $tag_chk = DB::connection('mysql_misp')->table('tags')
                        ->select('id')
                        ->where('name', '=', $tag_value)
                        ->first();
                    if (!empty($tag_chk)) {


                        $event_tag_chk = '';
                        $event_tag_chk = DB::connection('mysql_misp')->table('event_tags')
                            ->select('id')
                            ->where('tag_id', '=', $tag_chk->id)
                            ->where('event_id', '=', $pulseId)
                            ->get();

                        if (count($event_tag_chk)) {
                        } else {
                            DB::connection('mysql_misp')->table('event_tags')->insert([
                                'event_id' => $pulseId,
                                'tag_id' => $tag_chk->id
                            ]);
                        }
                    } else {

                        $insertedId = DB::connection('mysql_misp')->table('tags')->insertGetId([
                            'name' => $tag_value,
                            'colour' => $color,
                            'exportable' => 1,
                            'org_id' => 0,
                            'user_id' => 0,
                            'hide_tag' => 0,
                            'numerical_value' => null,
                            'is_galaxy' => 0,
                            'is_custom_galaxy' => 0,
                            'local_only' => 0
                        ]);

                        if ($insertedId) {
                            $e_chk = DB::connection('mysql_misp')->table('event_tags')
                                ->select('id')
                                ->where('tag_id', '=', $insertedId)
                                ->where('event_id', '=', $pulseId)
                                ->get();

                            if (count($e_chk)) {
                            } else {
                                DB::connection('mysql_misp')->table('event_tags')->insert([
                                    'event_id' => $pulseId,
                                    'tag_id' => $insertedId
                                ]);
                            }
                        }
                    }
                }
            }
            if ($document['source'] == "otx.alienvault") {
                $uuid =    isset($document['mips_uuid']) ? $document['mips_uuid'] : '';
                if ($uuid) {
                    $events_data =  DB::connection('mysql_misp')
                        ->table('events')
                        ->select('id')
                        ->where('uuid', $uuid)
                        ->first();

                    if ($events_data) {


                        $pulseId = $events_data->id; // ได้ค่า '33421'
                        $color = "#ffffff";

                        //ลบก่อน
                        DB::connection('mysql_misp')->table('event_tags')
                            ->where('event_id', '=', $pulseId)
                            ->delete();

                        $tags_string = $request->tags . ',OTX';
                        $array = explode(",", $tags_string);

                        foreach ($array as $index => $tag_value) {
                            $tag_chk = DB::connection('mysql_misp')->table('tags')
                                ->select('id')
                                ->where('name', '=', $tag_value)
                                ->first();
                            if (!empty($tag_chk)) {


                                $event_tag_chk = '';
                                $event_tag_chk = DB::connection('mysql_misp')->table('event_tags')
                                    ->select('id')
                                    ->where('tag_id', '=', $tag_chk->id)
                                    ->where('event_id', '=', $pulseId)
                                    ->get();

                                if (count($event_tag_chk)) {
                                } else {
                                    DB::connection('mysql_misp')->table('event_tags')->insert([
                                        'event_id' => $pulseId,
                                        'tag_id' => $tag_chk->id
                                    ]);
                                }
                            } else {

                                $insertedId = DB::connection('mysql_misp')->table('tags')->insertGetId([
                                    'name' => $tag_value,
                                    'colour' => $color,
                                    'exportable' => 1,
                                    'org_id' => 0,
                                    'user_id' => 0,
                                    'hide_tag' => 0,
                                    'numerical_value' => null,
                                    'is_galaxy' => 0,
                                    'is_custom_galaxy' => 0,
                                    'local_only' => 0
                                ]);

                                if ($insertedId) {
                                    $e_chk = DB::connection('mysql_misp')->table('event_tags')
                                        ->select('id')
                                        ->where('tag_id', '=', $insertedId)
                                        ->where('event_id', '=', $pulseId)
                                        ->get();

                                    if (count($e_chk)) {
                                    } else {
                                        DB::connection('mysql_misp')->table('event_tags')->insert([
                                            'event_id' => $pulseId,
                                            'tag_id' => $insertedId
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }


            $response_data = ['message' => '', 'status_code' => '00', 'data' => ''];
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $response_data = ['message' => $e->getMessage(), 'status_code' => '01', 'data' => array()];
        }
        return response()->json($response_data);
    }
    public function indicator_detail_update_tags(Request $request)
    {

        try {
            $input = $request->all();
            // dd($input);
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail->findOne(array('indicator_id' => $request->indicator_id, 'pulse_id' => $request->pulse_id), $options);
            if ($document) {
                $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                    ['_id' => $document['_id']],
                    [
                        '$set' => [
                            'tags' => $request->tags,
                        ]
                    ]
                );
            }

            $col_fx_otx_indicator_detail_2 = $clientMD->sosecure_threatintelligent_dev->fx_otx_indicator_detail;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document = $col_fx_otx_indicator_detail_2->findOne(array('indicator_id' => $request->indicator_id), $options);
            if ($document) {
                $update_fx_otx_events_indicator_detail = $col_fx_otx_indicator_detail_2->updateOne(
                    ['_id' => $document['_id']],
                    [
                        '$set' => [
                            'tags' => $request->tags,
                        ]
                    ]
                );
            }


            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $document_fx_otx_events = $col_fx_otx_events->findOne(array('pulse_id' => $request->pulse_id), $options);
            //ส่งค่าไปบันทึกที่ MISP
            if ($document_fx_otx_events['source'] == "misp") {




                $input = $document_fx_otx_events['pulse_id'];
                $parts = explode('.', $input);
                $pulseId = $parts[1]; // ได้ค่า '33421'
                $color = "#ffffff";

                $attributes_data = DB::connection('mysql_misp')->table('attributes')
                    ->select('id')
                    ->where('event_id', '=',  $pulseId)
                    ->where('value1', '=',  $document['indicator_name'])
                    ->first();
                if ($attributes_data) {
                    $indicator_id = $attributes_data->id;



                    //ลบก่อน
                    DB::connection('mysql_misp')->table('attribute_tags')
                        ->where('event_id', '=', $pulseId)
                        ->where('attribute_id', '=',  $indicator_id)
                        ->delete();

                    $tags_string = $request->tags;
                    $array = explode(",", $tags_string);

                    foreach ($array as $index => $tag_value) {
                        $tag_chk = DB::connection('mysql_misp')->table('tags')
                            ->select('id')
                            ->where('name', '=', $tag_value)
                            ->first();
                        if (!empty($tag_chk)) {


                            $event_tag_chk = '';
                            $event_tag_chk = DB::connection('mysql_misp')->table('attribute_tags')
                                ->select('id')
                                ->where('tag_id', '=', $tag_chk->id)
                                ->where('event_id', '=', $pulseId)
                                ->where('attribute_id', '=',  $indicator_id)
                                ->get();

                            if (count($event_tag_chk)) {
                            } else {
                                DB::connection('mysql_misp')->table('attribute_tags')->insert([
                                    'event_id' => $pulseId,
                                    'attribute_id' => $indicator_id,
                                    'tag_id' => $tag_chk->id,
                                    'local' => 0
                                ]);
                            }
                        } else {

                            $insertedId = DB::connection('mysql_misp')->table('tags')->insertGetId([
                                'name' => $tag_value,
                                'colour' => $color,
                                'exportable' => 1,
                                'org_id' => 0,
                                'user_id' => 0,
                                'hide_tag' => 0,
                                'numerical_value' => null,
                                'is_galaxy' => 0,
                                'is_custom_galaxy' => 0,
                                'local_only' => 0
                            ]);

                            if ($insertedId) {
                                $e_chk = DB::connection('mysql_misp')->table('attribute_tags')
                                    ->select('id')
                                    ->where('tag_id', '=', $insertedId)
                                    ->where('event_id', '=', $pulseId)
                                    ->where('attribute_id', '=',  $indicator_id)
                                    ->get();

                                if (count($e_chk)) {
                                } else {
                                    DB::connection('mysql_misp')->table('attribute_tags')->insert([
                                        'event_id' => $pulseId,
                                        'attribute_id' => $indicator_id,
                                        'tag_id' => $insertedId,
                                        'local' => 0
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            if ($document_fx_otx_events['source'] == "otx.alienvault") {
                $uuid =    isset($document_fx_otx_events['mips_uuid']) ? $document_fx_otx_events['mips_uuid'] : '';
                if ($uuid) {
                    $events_data =  DB::connection('mysql_misp')
                        ->table('events')
                        ->select('id')
                        ->where('uuid', $uuid)
                        ->first();

                    if ($events_data) {


                        $pulseId = $events_data->id; // ได้ค่า '33421'
                        $color = "#ffffff";
                        $attributes_data = DB::connection('mysql_misp')->table('attributes')
                            ->select('id')
                            ->where('event_id', '=',  $pulseId)
                            ->where('value1', '=',  $document['indicator_name'])
                            ->first();
                        if ($attributes_data) {

                            $indicator_id = $attributes_data->id;

                            //ลบก่อน
                            DB::connection('mysql_misp')->table('attribute_tags')
                                ->where('event_id', '=', $pulseId)
                                ->where('attribute_id', '=',  $indicator_id)
                                ->delete();

                            $tags_string = $request->tags . ',OTX';
                            $array = explode(",", $tags_string);

                            foreach ($array as $index => $tag_value) {
                                $tag_chk = DB::connection('mysql_misp')->table('tags')
                                    ->select('id')
                                    ->where('name', '=', $tag_value)
                                    ->first();
                                if (!empty($tag_chk)) {


                                    $event_tag_chk = '';
                                    $event_tag_chk = DB::connection('mysql_misp')->table('attribute_tags')
                                        ->select('id')
                                        ->where('tag_id', '=', $tag_chk->id)
                                        ->where('event_id', '=', $pulseId)
                                        ->where('attribute_id', '=',  $indicator_id)
                                        ->get();

                                    if (count($event_tag_chk)) {
                                    } else {
                                        DB::connection('mysql_misp')->table('attribute_tags')->insert([
                                            'event_id' => $pulseId,
                                            'attribute_id' => $indicator_id,
                                            'tag_id' => $tag_chk->id,
                                            'local' => 0
                                        ]);
                                    }
                                } else {

                                    $insertedId = DB::connection('mysql_misp')->table('tags')->insertGetId([
                                        'name' => $tag_value,
                                        'colour' => $color,
                                        'exportable' => 1,
                                        'org_id' => 0,
                                        'user_id' => 0,
                                        'hide_tag' => 0,
                                        'numerical_value' => null,
                                        'is_galaxy' => 0,
                                        'is_custom_galaxy' => 0,
                                        'local_only' => 0
                                    ]);

                                    if ($insertedId) {
                                        $e_chk = DB::connection('mysql_misp')->table('attribute_tags')
                                            ->select('id')
                                            ->where('tag_id', '=', $insertedId)
                                            ->where('event_id', '=', $pulseId)
                                            ->where('attribute_id', '=',  $indicator_id)
                                            ->get();

                                        if (count($e_chk)) {
                                        } else {
                                            DB::connection('mysql_misp')->table('attribute_tags')->insert([
                                                'event_id' => $pulseId,
                                                'attribute_id' => $indicator_id,
                                                'tag_id' => $insertedId,
                                                'local' => 0
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            $response_data = ['message' => '', 'status_code' => '00', 'data' => ''];
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $response_data = ['message' => $e->getMessage(), 'status_code' => '01', 'data' => array()];
        }
        return response()->json($response_data);
    }


    public function load_adversary_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];
            $start =  $row;
            $reqId = $request->adversary_uuid;

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $html = '';
            $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_adversaries_related;

            $query = [
                'adversary_uuid' => $reqId,

            ];

            $options = [
                'skip' => $start, //10
                'limit' => $rowperpage //5
            ];

            if ($request->count_page == -1) {
                $cursor_count = $fx_otx_events_event_ref->count($query);
                $count_filter = $cursor_count;
            } else {
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



            $cursor = $fx_otx_events_event_ref->find($query, $options);
            $document_all = $cursor->toArray();

            // set_time_limit(500); 

            // $count_doc = count($document_all);

            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $data = array();
            $order_number = $start;

            if ($document_all) {
                foreach ($document_all as  $value) {
                    $query = [
                        'pulse_id' => $value->pulse_id
                    ];
                    $document = $col_fx_otx_events->findOne($query, $options);

                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document["name"];
                    $nestedData['groups'] = explode_val($document["groups"], 'groups');
                    $nestedData['tags'] = explode_val($document["tags"], 'tags');
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document["indicator_count"];
                    $nestedData['public'] = ($document["public"]);
                    $nestedData['is_modified'] = ($document["is_modified"]);

                    try {
                        // $nestedData['modified']  = change_date_utc_to_thai($document['modified']);
                        $nestedData['modified'] = $document['modified'];
                    } catch (Exception $e) {
                        $nestedData['modified'] = $document['modified'];
                    } finally {
                        //  $nestedData['modified'] =$document['modified'];
                    }


                    $nestedData['count_view'] = @$document["count_view"];
                    $nestedData['pulse_id'] = @$document["pulse_id"];

                    $data[] = $nestedData;
                }
            }

            $dataOut["draw"] = $draw;
            $dataOut["recordsTotal"] = $cursor_count;
            $dataOut["recordsFiltered"] = $count_filter;
            $dataOut["data"] = $data;
            $dataOut["cursor"] = $cursor;
            return response()->json($dataOut);
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_load_pulse_tb, $form_body_complete, $authorization_key);
            if ($response_complete['status_code'] == "200") {
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            } else {
                return response()->json($response_complete);
            }
        }
    }

    public function load_malware_tb(Request $request)
    {
        $role_custom = @check_role_custom();
        if (!$role_custom['indicators']) {
            check_permission403();
        }
        if (TYPE_WEB == 'center') {
            $draw = $_POST['draw'];
            $row = (int)$_POST['start'];
            $rowperpage = (int)$_POST['length'];
            $start =  $row;
            $reqId = $request->malware_uuid;

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            $html = '';
            $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_malware_related;

            $query = [
                'malware_uuid' => $reqId,

            ];

            $options = [
                'skip' => $start, //10
                'limit' => $rowperpage //5
            ];

            if ($request->count_page == -1) {
                $cursor_count = $fx_otx_events_event_ref->count($query);
                $count_filter = $cursor_count;
            } else {
                $cursor_count = $request->count_page;
                $count_filter = $cursor_count;
            }



            $cursor = $fx_otx_events_event_ref->find($query, $options);
            $document_all = $cursor->toArray();

            // set_time_limit(500); 

            // $count_doc = count($document_all);

            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
            $data = array();
            $order_number = $start;

            if ($document_all) {
                foreach ($document_all as  $value) {
                    $query = [
                        'pulse_id' => $value->pulse_id
                    ];
                    $document = $col_fx_otx_events->findOne($query, $options);

                    $order_number++;
                    $nestedData['No'] = $order_number;
                    $nestedData['name'] = $document["name"];
                    $nestedData['groups'] = explode_val($document["groups"], 'groups');
                    $nestedData['tags'] = explode_val($document["tags"], 'tags');
                    $nestedData['attr'] = '';
                    $nestedData['attrCount'] = $document["indicator_count"];
                    $nestedData['public'] = ($document["public"]);
                    $nestedData['is_modified'] = ($document["is_modified"]);

                    try {
                        $nestedData['modified'] = $document['modified'];
                        // $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                    } catch (Exception $e) {
                        $nestedData['modified'] = $document['modified'];
                    } finally {
                        // $nestedData['modified'] =$document['modified'];
                    }


                    $nestedData['count_view'] = @$document["count_view"];
                    $nestedData['pulse_id'] = @$document["pulse_id"];

                    $data[] = $nestedData;
                }
            }

            $dataOut["draw"] = $draw;
            $dataOut["recordsTotal"] = $cursor_count;
            $dataOut["recordsFiltered"] = $count_filter;
            $dataOut["data"] = $data;
            $dataOut["cursor"] = $cursor;
            return response()->json($dataOut);
        } else {
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
            $response_complete = $this->reconnnect($url_indicator_load_pulse_tb, $form_body_complete, $authorization_key);
            if ($response_complete['status_code'] == "200") {
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            } else {
                return response()->json($response_complete);
            }
        }
    }

    public function select_techniques(Request $request)
    {
        $input = $request->all();

        if ($request->has('q')) {
            $search = $request->q;

            $query = FXTechniques::where('name', 'like', '%' . $search . '%')
                ->select('id', 'name')
                ->get();
        }

        return response()->json($query);
    }

    public function export_events_indicators(Request $request)
    {
        // Validate request
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'type' => 'required|in:1,2',
        ]);

        $form_type = (int)$request->type;

        try {
            $tz = new \DateTimeZone('Asia/Bangkok');
            $start = new \DateTime($request->start_date . ' 00:00:00', $tz);
            $end = new \DateTime($request->end_date . ' 23:59:59', $tz);

            $from = new \MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);
            $to = new \MongoDB\BSON\UTCDateTime($end->getTimestamp() * 1000);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format.'], 422);
        }

        // return response()->json(['from' => $from, 'to' => $to]);


        $pulseRaw = $request->input('pulse_id', []);
        if (is_string($pulseRaw) && str_contains($pulseRaw, ',')) {
            $pulseIds = array_map('trim', explode(',', $pulseRaw));
        } else {
            $pulseIds = is_array($pulseRaw) ? $pulseRaw : [$pulseRaw];
        }
        $pulseIds = array_filter($pulseIds, fn($id) => is_string($id) && !empty($id));

        // Connect Mongo
        $mongoUri = config("app.DB_MONGO_DEV");
        $client = new \MongoDB\Client($mongoUri);
        $db = $client->sosecure_threatintelligent_dev;
        $eventsCollection = $db->fx_otx_events;
        $attributesCollection = $db->fx_otx_events_indicator_ref;
        $options = ['typeMap' => ['root' => 'array', 'document' => 'array']];

        // Event query
        $usePulseOnly = !empty($pulseIds) && $request->has('pulse_id_only');



        // return response()->json($pulseIds);

        if ($usePulseOnly) {
            $eventQuery = [
                'pulse_id' => ['$in' => $pulseIds],
            ];
        } else {
            $eventQuery = [
                'modified' => ['$gte' => $from, '$lte' => $to],
                'status' => 1,
                'deleted_at' => null,
            ];
            if (!empty($pulseIds)) {
                $eventQuery['pulse_id'] = ['$in' => $pulseIds];
            }
        }

        $eventName = trim($request->input('event_name'));
        if (!empty($eventName)) {
            $eventQuery['name'] = new \MongoDB\BSON\Regex($eventName, 'i');
        }

        $events = $eventsCollection->find($eventQuery, $options)->toArray();


        if (empty($events)) {
            return response()->json([
                'message' => !empty($pulseIds)
                    ? 'Not found event : ' . implode(', ', $pulseIds) . ' in date range'
                    : 'Not found event in date range',
            ], 200);
        }


        $timestamp = date("Y-m-d_H.i.s");
        $fileName = $form_type === 1
            ? "Sosecure-Threat-Insight-Events-{$timestamp}.csv"
            : "Sosecure-Threat-Insight-Events-Indicators-{$timestamp}.csv";
        $filePath = storage_path("app/exportindicator/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $file = fopen($filePath, 'w');

        if ($form_type === 1) {
            // Header for Events only
            fputcsv($file, [
                'event_id',
                'event_name',
                'public',
                'event_tags',
                'modified_datetime',
            ]);

            foreach ($events as $doc) {
                $eventModified = isset($doc['updated_at']) && $doc['updated_at'] instanceof UTCDateTime
                    ? $doc['updated_at']->toDateTime()->format('Y-m-d')
                    : '';
                fputcsv($file, [
                    $doc['pulse_id'] ?? '',
                    $doc['name'] ?? '',
                    $doc['public'] ?? '',
                    isset($doc['tags']) ? implode(',', (array)$doc['tags']) : '',
                    $eventModified,
                ]);
            }
        } elseif ($form_type === 2) {
            $eventPulseIds = array_column($events, 'pulse_id');

            $attrQuery = [
                'pulse_id' => ['$in' => $eventPulseIds],
                'created_at' => ['$gte' => $from, '$lte' => $to]
            ];
            $attributes = $attributesCollection->find($attrQuery, $options)->toArray();

            $attributeMap = [];
            foreach ($attributes as $attr) {
                $pid = $attr['pulse_id'] ?? '';
                $attributeMap[$pid][] = $attr;
            }

            // Header for Events + Attributes
            fputcsv($file, [
                'event_id',
                'event_name',
                'public',
                'event_tags',
                'modified_datetime',
                'attribute_id',
                'attribute_type',
                'attribute_name',
                'attribute_tags',
                'attribute_score',
                'attribute_serverity',
                'attribute_datetime'
            ]);

            foreach ($events as $event) {
                $pulseId = $event['pulse_id'] ?? '';
                $eventName = $event['name'] ?? '';
                $eventPublic = $event['public'] ?? '';
                $eventTags = isset($event['tags']) ? implode(',', (array)$event['tags']) : '';
                $eventModified = isset($event['updated_at']) && $event['updated_at'] instanceof UTCDateTime
                    ? $event['updated_at']->toDateTime()->format('Y-m-d')
                    : '';

                if (!empty($attributeMap[$pulseId])) {
                    foreach ($attributeMap[$pulseId] as $attr) {
                        $attrDatetime = isset($attr['created_at']) && $attr['created_at'] instanceof UTCDateTime
                            ? $attr['created_at']->toDateTime()->format('Y-m-d H:i:s')
                            : '';

                        fputcsv($file, [
                            $pulseId,
                            $eventName,
                            $eventPublic,
                            $eventTags,
                            $eventModified,
                            $attr['indicator_id'] ?? '',
                            $attr['type'] ?? '',
                            $attr['indicator'] ?? '',
                            isset($attr['tags']) ? implode(',', (array)$attr['tags']) : '',
                            $attr['attribute_score'] ?? '',
                            $attr['attribute_serverity'] ?? '',
                            $attrDatetime
                        ]);
                    }
                } else {
                    fputcsv($file, [
                        $pulseId,
                        $eventName,
                        $eventPublic,
                        $eventTags,
                        $eventModified,
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        ''
                    ]);
                }
            }
        }

        fclose($file);

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }


    public function export_event_indicators(Request $request)
    {
        // Validate: ต้องมี pulse_id
        $request->validate([
            'pulse_id' => 'required|string'
        ]);

        $pulseId = trim($request->input('pulse_id'));

        $mongoUri = config("app.DB_MONGO_DEV");
        $client = new Client($mongoUri);
        $db = $client->sosecure_threatintelligent_dev;
        $eventsCollection = $db->fx_otx_events;
        $attributesCollection = $db->fx_otx_events_indicator_ref;
        $options = ['typeMap' => ['root' => 'array', 'document' => 'array']];

        $event = $eventsCollection->findOne([
            'pulse_id' => $pulseId,
            'status' => 1,
            'deleted_at' => null
        ], $options);

        if (!$event) {
            return response()->json([
                'message' => "Not found event : {$pulseId}"
            ], 200);
        }

        $attributes = $attributesCollection->find([
            'pulse_id' => $pulseId
        ], $options)->toArray();

        // เตรียม CSV
        $timestamp = date("Y-m-d H.i.s");
        $fileName = "Sosecure-Threat-Insight-Indicators-{$timestamp}-{$pulseId}.csv";
        $filePath = storage_path("app/exportindicator/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $file = fopen($filePath, 'w');
        fputcsv($file, [
            'event_id',
            'event_name',
            'public',
            'event_tags',
            'modified_datetime',
            'attribute_id',
            'attribute_type',
            'attribute_name',
            'attribute_tags',
            'attribute_score',
            'attribute_serverity',
            'attribute_datetime'
        ]);

        $eventName = $event['name'] ?? '';
        $eventPublic = $event['public'] ?? '';
        $eventTags = isset($event['tags']) ? implode(',', (array)$event['tags']) : '';
        $eventModified = isset($event['updated_at']) && $event['updated_at'] instanceof UTCDateTime
            ? $event['updated_at']->toDateTime()->format('Y-m-d')
            : '';

        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                $attrDatetime = isset($attr['created_at']) && $attr['created_at'] instanceof UTCDateTime
                    ? $attr['created_at']->toDateTime()->format('Y-m-d H:i:s')
                    : '';

                fputcsv($file, [
                    $pulseId,
                    $eventName,
                    $eventPublic,
                    $eventTags,
                    $eventModified,
                    $attr['indicator_id'] ?? '',
                    $attr['type'] ?? '',
                    $attr['indicator'] ?? '',
                    isset($attr['tags']) ? implode(',', (array)$attr['tags']) : '',
                    $attr['attribute_score'] ?? '',
                    $attr['attribute_serverity'] ?? '',
                    $attrDatetime
                ]);
            }
        } else {
            fputcsv($file, [
                $pulseId,
                $eventName,
                $eventPublic,
                $eventTags,
                $eventModified,
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
        }

        fclose($file);
        return response()->download($filePath, $fileName);
    }


    public function importToInsight(Request $request)
    {

        // return response()->json(['message' => 'Import to Insight']);
        set_time_limit(600);
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        $file = $request->file('file');
        $import_type = $request->input('import_type');
        $path = $file->getRealPath();

        if ($import_type == '0') {
            return response()->json([
                'success' => false,
                'message' => 'Please select import type'
            ]);
        }

        if ($import_type == 'event') {
            try {
                $csv = Reader::createFromPath($path, 'r');
                $csv->setHeaderOffset(0);
                $stmt = new Statement();
                $records = $stmt->process($csv);

                $rows = array_values(iterator_to_array($records));
                $header = $csv->getHeader();
                $columnCount = count($header);

                if ($columnCount !== 5) {
                    return response()->json([
                        'success' => false,
                        'message' => "CSV column count invalid. Found $columnCount columns, expected 5.",
                        'columns' => $header
                    ], 400);
                }

                if (empty($rows)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV is empty or unreadable.'
                    ], 400);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error reading CSV: ' . $e->getMessage()
                ], 500);
            }

            $dataKey = Str::uuid()->toString();
            $now = now();
            $mongoUrl = config('app.DB_MONGO_DEV');
            $client = new Client($mongoUrl);
            $db = $client->sosecure_threatintelligent_dev;

            $tempCollection = $db->fx_events_temp;
            $dataKeyCollection = $db->fx_data_key;

            $cleanedRows = [];
            foreach ($rows as $row) {
                $row['data_key'] = $dataKey;
                $row['imported_at'] = $now->format('Y-m-d H:i:s');
                $cleanedRows[] = $row;
            }

            // ➤ Insert temp
            try {
                $tempCollection->insertMany($cleanedRows);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to insert temp event data: ' . $e->getMessage()
                ], 500);
            }

            // ➤ Insert data key meta
            try {
                $dataKeyCollection->insertOne([
                    'date' => $now->format('Y-m-d H:i:s'),
                    'data_key' => $dataKey,
                    'imported_at' => $now->format('Y-m-d H:i:s'),
                    'record_count' => count($cleanedRows),
                    'type' => 'event',
                    'file_name' => $file->getClientOriginalName(),
                    'timestamp' => $now->timestamp
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to log data_key: ' . $e->getMessage()
                ], 500);
            }

            // ➤ Sync tag to fx_otx_events + MISP
            $syncResult = $this->syncEventTagsByDataKey($dataKey, $db);

            // ➤ Update tags to MISP (event level)
            $mispService = app(MispTagService::class);
            foreach ($cleanedRows as $row) {
                if (!empty($row['event_id'])) {
                    try {
                        $mispService->update($row['event_id'], $row['event_tags'] ?? '');
                    } catch (\Throwable $e) {
                        \Log::error('[MISP EVENT] Failed to update tags', [
                            'event_id' => $row['event_id'],
                            'tags' => $row['event_tags'] ?? '',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Event tags updated.',
                'data_key' => $dataKey,
                'records_inserted' => count($cleanedRows),
                'total_rows' => count($rows),
                'success_list' => array_slice($syncResult['updated'], 0, 100000),
                'error_list' => $syncResult['errors'],
            ]);
        }


        if ($import_type == 'attribute') {
            try {
                $csv = Reader::createFromPath($path, 'r');
                $csv->setHeaderOffset(0);
                $stmt = new Statement();
                $records = $stmt->process($csv);

                $rows = array_values(iterator_to_array($records));
                $header = $csv->getHeader();
                $columnCount = count($header);

                if ($columnCount !== 12) {
                    return response()->json([
                        'success' => false,
                        'message' => "CSV column count invalid. Found $columnCount columns, expected 12.",
                        'columns' => $header
                    ], 400);
                }

                if (empty($rows)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV is empty or unreadable.'
                    ], 400);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error reading CSV: ' . $e->getMessage()
                ], 500);
            }

            $targetIndex = 8;
            $originalColumnName = $header[$targetIndex];
            $dataKey = Str::uuid()->toString();
            $mongoUrl = config('app.DB_MONGO_DEV');
            $client = new Client($mongoUrl);
            $db = $client->sosecure_threatintelligent_dev;

            $tempCollection = $db->fx_indicators_temp;
            $dataKeyCollection = $db->fx_data_key;

            $cleanedRows = [];
            foreach ($rows as $row) {
                if (isset($row[$originalColumnName])) {
                    $row['tags'] = $row[$originalColumnName];
                    unset($row[$originalColumnName]);
                }
                $row['data_key'] = $dataKey;
                $cleanedRows[] = $row;
            }

            // Insert to temp collection
            $batchSize = 1000;
            $totalInserted = 0;
            $insertedIds = [];
            $insertErrors = [];

            foreach (array_chunk($cleanedRows, $batchSize) as $chunk) {
                try {
                    $tempCollection->insertMany($chunk);
                    $totalInserted += count($chunk);
                    foreach ($chunk as $record) {
                        $insertedIds[] = $record['attribute_id'] ?? '(no attribute_id)';
                    }
                } catch (\Exception $e) {
                    $insertErrors[] = 'Insert batch failed: ' . $e->getMessage();
                }
            }

            $now = now();
            try {
                $dataKeyCollection->insertOne([
                    'date' => $now->format('Y-m-d H:i:s'),
                    'data_key' => $dataKey,
                    'imported_at' => $now->format('Y-m-d H:i:s'),
                    'record_count' => $totalInserted,
                    'type' => 'attribute',
                    'file_name' => $file->getClientOriginalName(),
                    'timestamp' => $now->timestamp
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to insert data_key log: ' . $e->getMessage()
                ], 500);
            }

            // ดึง temp และ sync ไป ref/detail
            $syncResult = $this->syncIndicatorUpdatesByDataKey($dataKey, $db);
            $mispService = new MispTagService();
            // log:info('MISP ATTRIBUTE', $cleanedRows);
            // return response()->json([
            //     'message' => 'Attribute tags updated.',
            //     'data' => $cleanedRows
            // ]);


            foreach ($cleanedRows as $row) {
                if (!empty($row['event_id']) && !empty($row['attribute_id'])) {
                    try {
                        app(MispTagService::class)->updateFromIndicator(
                            $row['event_id'],
                            $row['attribute_id'],
                            $row['tags'] ?? ''
                        );
                    } catch (\Throwable $e) {
                        Log::error('[MISP ATTRIBUTE] Update failed', [
                            'event_id' => $row['event_id'],
                            'attribute_id' => $row['attribute_id'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Import complete.',
                'data_key' => $dataKey,
                'records_inserted' => $totalInserted,
                'total_rows' => count($rows),
                'success_list' => array_slice($syncResult['updated'], 0, 100000),
                'error_list' => $syncResult['errors']
            ]);
        }
    }

    private function syncIndicatorUpdatesByDataKey(string $dataKey, $db): array
    {
        $tempCollection = $db->fx_indicators_temp;
        $refCollection = $db->fx_otx_events_indicator_ref;
        $detailCollection = $db->fx_otx_indicator_detail;

        $cursor = $tempCollection->find(
            ['data_key' => $dataKey],
            [
                'projection' => [
                    '_id' => 0,
                    'attribute_id' => 1,
                    'attribute_name' => 1,
                    'tags' => 1,
                    'attribute_score' => 1,
                    'attribute_serverity' => 1
                ],
                'batchSize' => 1000
            ]
        );

        $batchSize = 1000;
        $refOps = [];
        $detailOps = [];
        $count = 0;

        $updatedIds = [];
        $errors = [];

        foreach ($cursor as $doc) {
            $id = $doc['attribute_id'] ?? null;
            if (empty($id)) continue;

            $updateFields = [
                'tags' => $doc['tags'] ?? null,
                'attribute_score' => $doc['attribute_score'] ?? null,
                'attribute_serverity' => $doc['attribute_serverity'] ?? null
            ];

            $refOps[] = [
                'updateOne' => [
                    ['indicator_id' => $id],
                    ['$set' => $updateFields],
                    ['upsert' => false]
                ]
            ];

            $detailOps[] = [
                'updateOne' => [
                    ['indicator_id' => $id],
                    ['$set' => $updateFields],
                    ['upsert' => false]
                ]
            ];

            $updatedIds[] = [
                'id' => $id,
                'tag' => $doc['tags'] ?? '-',
                'name' => $doc['attribute_name'] ?? '-'
            ];

            $count++;

            if ($count >= $batchSize) {
                try {
                    $refResult = $refCollection->bulkWrite($refOps);
                    $detailResult = $detailCollection->bulkWrite($detailOps);

                    // ตรวจสอบการ match
                    $matched = $refResult->getMatchedCount();
                    if ($matched < count($refOps)) {
                        $errors[] = [
                            'id' => '(bulk batch)',
                            'name' => '-',
                            'reason' => "Warning: Only $matched of " . count($refOps) . " ref documents matched."
                        ];
                    }

                    $matchedDetail = $detailResult->getMatchedCount();
                    if ($matchedDetail < count($detailOps)) {
                        $errors[] = [
                            'id' => '(bulk batch)',
                            'name' => '-',
                            'reason' => "Warning: Only $matchedDetail of " . count($detailOps) . " detail documents matched."
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => '(bulk batch)',
                        'name' => '-',
                        'reason' => 'Bulk update error: ' . $e->getMessage()
                    ];
                }

                $refOps = [];
                $detailOps = [];
                $count = 0;
            }
        }

        // leftover ops
        if (!empty($refOps)) {
            try {
                $refResult = $refCollection->bulkWrite($refOps);
                $detailResult = $detailCollection->bulkWrite($detailOps);

                $matched = $refResult->getMatchedCount();
                if ($matched < count($refOps)) {
                    $errors[] = [
                        'id' => '(Not detected)',
                        'name' => 'Not detected',
                        'success' => $matched,
                        'reason' => "Warning: Only $matched of " . count($refOps) . " Idicator ref documents matched."
                    ];
                }

                $matchedDetail = $detailResult->getMatchedCount();
                if ($matchedDetail < count($detailOps)) {
                    $errors[] = [
                        'id' => '(Not detected)',
                        'name' => 'Not detected',
                        'success' => $matched,
                        'reason' => "Warning: Only $matchedDetail of " . count($detailOps) . " Idicator detail documents matched."
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'id' => '(final batch)',
                    'name' => '-',
                    'reason' => 'Final bulk update error: ' . $e->getMessage()
                ];
            }
        }
        return [
            'updated' => $updatedIds,
            'errors' => $errors
        ];
    }

    private function syncEventTagsByDataKey(string $dataKey, $db)
    {
        $tempCollection = $db->fx_events_temp;
        $eventCollection = $db->fx_otx_events;

        $cursor = $tempCollection->find(
            ['data_key' => $dataKey],
            [
                'projection' => [
                    '_id' => 0,
                    'event_id' => 1,
                    'event_tags' => 1,
                    'event_name' => 1
                ],
                'batchSize' => 1000
            ]
        );

        $success = [];
        $errors = [];

        foreach ($cursor as $index => $doc) {
            $pulseId = $doc['event_id'] ?? null;
            $tags = $doc['event_tags'] ?? null;
            $name = $doc['event_name'] ?? '-';
            $rowNumber = $index + 1;

            if (!$pulseId) {
                $errors[] = [
                    'id' => "(row {$rowNumber})",
                    'name' => $name,
                    'reason' => 'missing event_id'
                ];
                continue;
            }

            try {
                $result = $eventCollection->updateOne(
                    ['pulse_id' => $pulseId],
                    ['$set' => ['tags' => $tags]],
                    ['upsert' => false]
                );

                if ($result->getModifiedCount() > 0) {
                    $success[] = [
                        'id' => $pulseId,
                        'name' => $name
                    ];
                } else {
                    $errors[] = [
                        'id' => $pulseId,
                        'name' => $name,
                        'reason' => 'no update (not found or unchanged)'
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'id' => $pulseId,
                    'name' => $name,
                    'reason' => 'update failed: ' . $e->getMessage()
                ];
            }
        }

        return [
            'updated' => $success,
            'errors' => $errors
        ];
    }
}
