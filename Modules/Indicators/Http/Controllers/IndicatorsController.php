<?php

namespace Modules\Indicators\Http\Controllers;
use Yajra\DataTables\DataTables;
use App\Entities\OtxIndicatiorData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\indicators\Entities\OTXtypeData;
use MongoDB\Client;
use MongoDB\Client as MongoClient;

class IndicatorsController extends Controller
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

    public function events()
    
    {
        
        $data['page'] = langapp('indicators');
        return view('indicators::events')->with($data);
    }

    public function events_detail()
    {
        $data['page'] = langapp('indicators');
        return view('indicators::events_detail')->with($data);
    }

    public function attributes()
    {
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
        $data['otx_type'] = OTXtypeData::where("status", '=', 1)->get();
        
        return view('indicators::attributes')->with($data);
    }

    public function show_detail_indicators(Request $request)
    {
        $data['page'] = langapp('indicators');
        $data['otxid'] = $request->id;
        $data['otxtype'] = $request->type;
        $data['otxindicator'] = $request->indicator;
        return view('indicators::detail_indicators')->with($data);
    }

    public function load_general(Request $request)
    {
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

        $bodyData = $client->request(
            'GET',
            'https://otx.alienvault.com/otxapi/indicator/' . $reqType . '/general' . '/' . $reqIndicator,
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                    'X-OTX-API-KEY' => $OTX_KEY,
                ],
            ]
        )->getBody();
        $DataotxIndicator = json_decode($bodyData, true);

        //pulse info HTML
        $html = '';
        $html .= '<ul class="list-indicators">';
        $isPulse_info = 0;
        if (!empty($DataotxIndicator["pulse_info"])) {
            $isPulse_info = 1;
            $pulseInfo = $DataotxIndicator["pulse_info"];
            foreach ($pulseInfo["pulses"] as $value) {
                $html .= '
                <li>
                    <div class="related-pulses">
                        <div class="related-img">
                            <img src="' . (isset($value["author"]["avatar_url"]) ? $value["author"]["avatar_url"] : "") . '" alt="">
                        </div>
                        <div class="related-content">
                            <div class="related-title">
                                <a href="pulsedetail">
                                    <h1 class="related-title">
                                        ' . (isset($value["name"]) ? $value["name"] : "") . '
                                    </h1>
                                </a>
                                <div class="active-indicator">
                                    <div class="' . ($value["related_indicator_is_active"] == 1 ? "dot green" : "dot grey") . '"></div>
                                    <div>
                                        ' . $request->type . ' Indicator ' . ($value["related_indicator_is_active"] == 1 ? "Active" : "Inactive") . '
                                    </div>
                                </div>
                            </div>
                            <div class="details-wrapper">
                                <ul class="detail-show">
                                    <li>
                                        <span class="' . ($value["is_modified"] == false ? "created" : "modified") . '">
                                        ' . ($value["is_modified"] == false ? "Created" : "Modified") . '
                                        </span>
                                        <span class="pulse-ago">
                                            ' . (isset($value["modified_text"]) ? $value["modified_text"] : "") . '
                                        </span>
                                        by <a href="https://otx.alienvault.com/user/' . (isset($value["author"]["username"]) ? $value["author"]["username"] : "") . '/pulses" class="pulse-author">
                                        ' . (isset($value["author"]["username"]) ? $value["author"]["username"] : "") . '
                                        </a>
                                    </li>
                                    <li>
                                        <span class="stat-label"> Public </span>
                                    </li>
                                    <li>
                                        <a href="https://www.us-cert.gov/tlp" target="_new">TLP</a>:
                                      <span>
                                        <i class="fas fa-circle ' . $value["TLP"] . '">
                                        </i>
                                        ' . ucwords($value["TLP"]) . '
                                      </span>
                                    </li>
                                </ul>
                                <div class="pulse-indicator-counts">
                                    <span class="nowrap ellipsis">';
                if (!empty($value["indicator_type_counts"])) {
                    foreach ($value["indicator_type_counts"] as $key => $typeCount) {
                        $html .= '<span class="insered">
                            <strong>' . $key . ':</strong>
                                <span class="br-last">' . $typeCount . '</span>
                            </span>';
                    }
                }
                $html .= '          </span>
                                </div>
                                <div class="indicator-description">
                                    <span class="nowrap ellipsis">
                                    ' . (isset($value["description"]) ? $value["description"] : "") . '
                                    </span>
                                </div>
                                <div class="by-items">';

                if (!empty($value["tags"])) {
                    $html_sub = '';
                    foreach ($value["tags"] as $tag) {
                        $html_sub .= ',<a href="https://otx.alienvault.com/browse/pulses?q=tag:' . $tag . ' "><span>' . $tag . '</span></a>';
                    }
                    $html .= substr($html_sub, 1);
                }

                $html .= '</div>
                            </div>
                        </div>
                        <div class="related-subscribers">
                            <span class="star-count">' . $value["subscriber_count"] . '</span>
                            <span class="subscribers">
                                <i></i>&nbsp;SUBSCRIBERS
                            </span>
                        </div>
                    </div>
                </li>';
            }
        } else {
            $html .= '<li>
            <div class="related-pulses">
                no Data
            </div>
            </li>';
        }
        $html .= '</ul>';

        $html2 = '';
        $isValidation = 0;
        if (!empty($DataotxIndicator["validation"])) {
            $isValidation = 1;
            $validationInfo = $DataotxIndicator["validation"];
            foreach ($validationInfo as $value) {
                $html2 .= '
                <div class="row m-b-xs">
                    <div class="col-md-6">
                        ' . (isset($value["name"]) ? $value["name"] : "") . '
                    </div>
                    <div class="col-md-6">
                        ' . (isset($value["message"]) ? $value["message"] : "") . '
                    </div>
                </div>';
            }
        } else {
            $html2 .= '';
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "html2" => $html2,
                "generalData" => $DataotxIndicator,
                "sections" => $DataotxIndicator["sections"],
                "isValidation" => $isValidation,
                "isPulse_info" => $isPulse_info,
            ];
            return response()->json($data);
        }

    }

    public function load_url_list(Request $request)
    {
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
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new MongoClient("mongodb://10.104.0.7:27017");
        $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicators_data;

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
        $_search = array('status' => 1,
            'deleted_at' => null);
        $_sort = [];
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
                
                if ($request->keywords) {
                    $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                } 

                if ($request->type) {
                    $_search =  array_merge($_search, array('type' => ['$in'=>$request->type]));
                }

                if ($request->startDate&&$request->endDate) {
                    $_search =  array_merge($_search, array('type' => ['$in'=>$request->type]));
                }
                
                if ($request->type && $request->keywords && $request->startDate) {
                    $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereIn('type', $request->type)->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                } else if ($request->type && $request->keywords) {

                    $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereIn('type', $request->type);
                } else if ($request->startDate && $request->keywords) {

                    $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                } else if ($request->startDate && $request->type) {

                    $data = OtxIndicatiorData::where("status", '=', 1)->whereIn('type', $request->type)->whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                } else if ($request->keywords) {

                    $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%');
                } else if ($request->type) {

                    $data = OtxIndicatiorData::where("status", '=', 1)->whereIn('type', $request->type);
                } else if ($request->startDate) {

                    $data = OtxIndicatiorData::whereBetween('updated_at', array($date_start_datetime_format, $date_end_datetime_format));
                }


                if ($request->target == 'Recently Modified') {
                    $data = $data->orderBy('updated_at', 'desc');
                } else if ($request->target == 'Least Recently Modified') {
                    $data = $data->orderBy('updated_at', 'asc');
                } else if ($request->target == 'Name Descending') {
                    $data = $data->orderBy('indicatior', 'desc');
                } else if ($request->target == 'Name Ascending') {
                    $data = $data->orderBy('indicatior', 'asc');
                }

                $count = $data->count();
                $data = $data->paginate(PAGINATE_NUM);
            }
        } else {
            
            if ($request->target == 'Recently Modified') {
                $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'desc');
            } else if ($request->target == 'Least Recently Modified') {
                $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('updated_at', 'asc');
            } else if ($request->target == 'Name Descending') {
                $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'desc');
            } else if ($request->target == 'Name Ascending') {
                $data = OtxIndicatiorData::where("status", '=', 1)->orderBy('indicatior', 'asc');
            } else {
                $data = OtxIndicatiorData::where("status", '=', 1);
            }

            

            $data = $data->paginate(PAGINATE_NUM);
            $count = OtxIndicatiorData::where("status", '=', 1)->count();
        }
        $cursor = $col_fx_transaction_otx_indicators_data->find(
           
        $_search,
            [
                'limit' => PAGINATE_NUM,
                'skip' => ($request->page-1)*PAGINATE_NUM,
                'sort' => $_sort,
            ]
        );
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
        if(!empty($documentAll))
        foreach ($documentAll as $data) {

            //ถ้าเป็น NIDS เอา Title มาแทน indicatior
            if ($data->type == "CIDR"  || $data->type == "FileHash-IMPHASH"|| $data->type == "FileHash-PEHASH" || $data->type == "FilePath" || $data->type == "Mutex" || $data->type == "URI"|| $data->type == "JA3"|| $data->type == "osquery") {
                $linkIndicator =  '<a>';
            } else {
                $linkIndicator =  '<a href="' . route('indicators.detail_indicators') . '?id=' . $data->indicator_id . '&&type=' . $data->type . '&&indicator=' . $data->indicator . '">';
            }
            $html .= ' <ul class="list-indicators">
                        <li>
                            ' . $linkIndicator . '
                                <h1 class="primary-text">' . $data->indicator . '</h1>
                                
                                
                               
                                <span class="secondary-text">Type : ' . $data->type . '</span>
                            </a>
                        </li></ul>';
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
        
        // $DB_MONGO_KEY = env("DB_MONGO_DEV", "mongodb://10.104.0.7:27017");
        
        // $client = new \MongoDB\Client($DB_MONGO_KEY);
        $mongo_url = 'mongodb://10.104.0.7:27017';
        $client = new \MongoDB\Client($mongo_url);
        $db_name = 'sosecure_threatintelligent';
        $db = $client->$db_name;
        $collection = $db->fx_otx_events;
        $where = array(
            'status' => 1,
        );
        // $cursor = $collection->find($where,['projection'=>['_id'=>0]]);
        $cursor = $collection->find($where);
        $model= $cursor->toArray();
        // $model = $model[0];
        // unset($model['_id']);
        if($model) {
            // foreach($model as $key => $model_val) {
            //     dd($model_val->name);
            //     dd($model_val->name);
            // }
        }

        // $collection = collect(['name', 'public']);
        // $collection = collect([
        //     ['product' => 'Desk', 'price' => 200],
        //     ['product' => 'Chair', 'price' => 100],
        // ]);

        // $collection->paginate(15);
        // dd($collection);
        $test = mysqli_num_rows($model);
        dd($test);

        // $combined = $collection->combine(['George', 1]);
        // $combined->all();
        // dd($combined);
        //  dd($model[0]['TLP']);
        // $model = collect($model);
        //dd($model[0]->TLP);

        return DataTables::of($collection->toJson())
        ->editColumn('chk', function ( $collection) {
            return '<label><input type="checkbox"  name="events_id" class="events_id" value=""><span class="label-text"></span></label>';
        })
        ->addColumn('no', function ( $collection) {
            return '-';
        })
        ->addColumn('even_name', function ( $collection) {
            
            return $collection->product;
        })

        ->addColumn('group', function ( $collection) {
        
            return "-";
        })
        ->addColumn('tags', function ( $collection) {
            return '-';
        })
        ->addColumn('attr', function ( $collection) {
            return '-';
        })
        ->addColumn('published', function ( $collection) {
            return '-';
        })
        ->addColumn('last_status', function ( $collection) {
            return '-';
        })
        ->addColumn('date_time', function ( $collection) {
            return '-';
        })
        ->addColumn('view', function ( $collection) {
            return '-';
        })
        ->addColumn('action', function ( $collection) {
            return '-';
        })


        ->rawColumns(['chk', 'no', 'even_name', 'group', 'tags', 'attr', 'published', 'last_status', 'date_time', 'view', 'action'])
        ->toJson();
     
    }

}