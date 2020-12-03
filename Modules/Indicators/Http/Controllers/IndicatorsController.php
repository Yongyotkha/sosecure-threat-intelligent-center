<?php

namespace Modules\Indicators\Http\Controllers;

use App\Entities\OtxIndicatiorData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\indicators\Entities\OTXtypeData;


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
    public function index()
    {
        $data['page'] = langapp('indicators');
        $data['otx_type'] = OTXtypeData::where("status", '=', 1)->get();
        return view('indicators::index')->with($data);
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
        $OTX_KEY = env("OTX_KEY","");
        $client = new \GuzzleHttp\Client();
        $reqType = $request->type;
        if (stripos( $request->type, "file") !== false) {
            $reqType = 'file';
        }else if($reqType == "CVE"){
            $reqType = "cve";
        }else if($reqType == "URL"){
            $reqType = 'url';
        }else if($reqType == "NIDS"){
            $reqType = 'nids';
        }else if($reqType == "YARA"){
            $reqType = 'yara';
        }else if($reqType == "BitcoinAddress"){
            $reqType = 'bitcoin-address';
        }else if($reqType == "SSLCertFingerprint"){
            $reqType = 'ssl-cert-fingerprint';
        }
        $reqIndicator = $request->indicator;
        $bodyData   = $client->request( 
            'GET',
            'https://otx.alienvault.com/otxapi/indicator/'.$reqType.'/general'.'/'.$reqIndicator,
            [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-type' => 'application/json',
                    'X-OTX-API-KEY' => $OTX_KEY,
                ]
            ]
        )->getBody();
          $DataotxIndicator = json_decode($bodyData,true);


        $pulseInfo = $DataotxIndicator["pulse_info"];
        $validationInfo = $DataotxIndicator["validation"];

        //pulse info HTML
        $html = '';
        $html .= '<ul class="list-indicators">';
        if(!empty($pulseInfo["pulses"])){
            foreach ($pulseInfo["pulses"] as $value) {
                $html .= '
                <li>
                    <div class="related-pulses">
                        <div class="related-img">
                            <img src="' . $value["author"]["avatar_url"] . '" alt="">
                        </div>
                        <div class="related-content">
                            <div class="related-title">
                                <a href="pulsedetail">
                                    <h1 class="related-title">
                                        '. $value["name"] .'
                                    </h1>
                                </a>
                                <div class="active-indicator">
                                    <div class="'.($value["related_indicator_is_active"]==1?"dot green":"dot grey"). '"></div>
                                    <div>
                                        '.$request->type. ' Indicator ' .($value["related_indicator_is_active"]==1?"Active":"Inactive").'
                                    </div>
                                </div>
                            </div>
                            <div class="details-wrapper">
                                <ul class="detail-show">
                                    <li>
                                        <span class="'.($value["is_modified"]==false?"created":"modified").'"> 
                                        '.($value["is_modified"]==false?"Created":"Modified").' 
                                        </span>
                                        <span class="pulse-ago"> 
                                            '.$value["modified_text"].'
                                        </span>
                                        by <a href="https://otx.alienvault.com/user/'.$value["author"]["username"].'/pulses" class="pulse-author">
                                        '.$value["author"]["username"].'
                                        </a>
                                    </li>
                                    <li>
                                        <span class="stat-label"> Public </span>
                                    </li>
                                    <li>
                                        <a href="https://www.us-cert.gov/tlp" target="_new">TLP</a>:
                                      <span>
                                        <i class="fas fa-circle '.$value["TLP"].'">
                                        </i> 
                                        '.ucwords($value["TLP"]).'
                                      </span>
                                    </li>
                                </ul>
                                <div class="pulse-indicator-counts">
                                    <span class="nowrap ellipsis">';
                    if(!empty($value["indicator_type_counts"])){
                        foreach ($value["indicator_type_counts"]  as $key => $typeCount) {
                            $html .= '<span class="insered">
                            <strong>'.$key.':</strong>
                                <span class="br-last">'.$typeCount.'</span>
                            </span>';
                        }
                    }
                $html .= '          </span>
                                </div>
                                <div class="indicator-description">
                                    <span class="nowrap ellipsis">
                                    '.(isset($value["description"])?$value["description"]:"").'
                                    </span>
                                </div>
                                <div class="by-items">';
                                
                                if(!empty($value["tags"])){
                                    $html_sub = '';
                                    foreach ($value["tags"]  as $tag) {
                                        $html_sub .= ',<a href="https://otx.alienvault.com/browse/pulses?q=tag:'.$tag.' "><span>'.$tag.'</span></a>';
                                    }
                                    $html .= substr($html_sub,1);
                                }

                $html .=        '</div>
                            </div>
                        </div>
                        <div class="related-subscribers">
                            <span class="star-count">'.$value["subscriber_count"].'</span>
                            <span class="subscribers">
                                <i></i>&nbsp;SUBSCRIBERS
                            </span>
                        </div>
                    </div>
                </li>';
            }
        }else{
            $html .= '<li>
            <div class="related-pulses">
                no Data
            </div>
            </li>';
        }
        $html .= '</ul>';

        $html2 = '';
        if(!empty($validationInfo)){
            foreach ($validationInfo as $value) {
                $html2 .= '
                <div class="row m-b-xs">
                    <div class="col-md-6">
                        '.$value["name"].'
                    </div>
                    <div class="col-md-6">
                        '.$value["message"].'
                    </div>
                </div>';
            }
        }else{
            $html2 .= '';
        }


        if ($request->ajax()) {
            $data = [
                "html" =>  $html,
                "html2" =>  $html2,
                "sections" =>  $DataotxIndicator["sections"],
                "testdata" =>  $DataotxIndicator,
                "count" => 0
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
    //     return view('indicators::index', compact('data'));
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
    {


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
        if ($request->keywords || $request->type || $request->startDate || $request->endDate) {
            if ($request->type && $request->keywords) {
                $count = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->where('type', '=', $request->type)->count();
                $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->where('type', '=', $request->type)->paginate(10);
            } else if ($request->keywords) {
                $count = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->count();
                $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->paginate(10);
            } else if ($request->type) {
                $count = OtxIndicatiorData::where("status", '=', 1)->where('type', '=', $request->type)->count();
                $data = OtxIndicatiorData::where("status", '=', 1)->where('type', '=', $request->type)->paginate(10);
            }
        } else {

            $data = OtxIndicatiorData::where("status", '=', 1)->paginate(10);
            $count = OtxIndicatiorData::where("status", '=', 1)->count();
        }

        foreach ($data as $data) {
            //FileHash-PEHASH ไม่มีตัวอย่าง
             //Osquery
             //Ja3

            //ถ้าเป็น NIDS เอา Title มาแทน indicatior
           
            if($data->type == "CIDR"||$data->type == "FilePath"||$data->type == "FileHash-IMPHASH"||$data->type == "Mutex"||$data->type == "URI"){
                $linkIndicator =  '<a>';
            }else{
                $linkIndicator =  '<a href="'.route('indicators.detail_indicators').'?id='.$data->id.'&&type='.$data->type.'&&indicator='.$data->indicatior.'">';
            }
            $html .= ' <ul class="list-indicators">
                        <li>
                            '.$linkIndicator.'
                                <h1 class="primary-text">' . $data->indicatior . '</h1>
                           
                                <span class="secondary-text">Type : ' . $data->type . '</span>
                            </a>
                        </li></ul>';

            
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $count
            ];
            return response()->json($data);
        }
    }
}