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

    public function show_detail_indicators($otxid, $otxtype, $otxindicator)
    {
        $data['page'] = langapp('indicators');
        $data['otxid'] = $otxid;
        $data['otxtype'] = $otxtype;
        $data['otxindicator'] = $otxindicator;
        return view('indicators::detail_indicators')->with($data);
    }


    public function load_general(Request $request)
    {
        // $OTX_KEY = env("OTX_KEY","");
        // $client = new \GuzzleHttp\Client();
        // $bodyData   = $client->request( 
        //     'GET',
        //     'https://otx.alienvault.com/api/v1/indicators/'.$otxtype.'/'.$otxindicator.'/general',
        //     [
        //         'headers' => [
        //             'Accept'       => 'application/json',
        //             'Content-type' => 'application/json',
        //             'X-OTX-API-KEY' => $OTX_KEY,
        //         ]
        //     ]
        // )->getBody();
        // $DataotxIndicator = json_decode($bodyData,true);
        $testto = '<ul class="list-indicators">
        <li>
            <div class="related-pulses">
                <div class="related-img">
                    <img src="{{asset(\'images/avatar_1.png\')}}" alt="">
                </div>
                <div class="related-content">
                    <div class="related-title">
                        <a href="pulsedetail">
                            <h1 class="related-title">
                                The Gafgyt variant vbot seen in its 31 campaigns
                            </h1>
                        </a>
                        <div class="active-indicator">
                            <div class="dot green"></div>
                            <div> URL Indicator Active </div>
                        </div>
                    </div>
                    <div class="details-wrapper">
                        <ul class="detail-show">
                            <li>
                                <span class="modified"> Modified </span>
                                <span class="pulse-ago"> 3 HOURS AGO </span>
                                by <a href="" class="pulse-author">MTSC</a>
                            </li>
                            <li>
                                <span class="stat-label"> Public </span>
                            </li>
                            <li>
                                <a href="https://www.us-cert.gov/tlp" target="_new">TLP</a>:
                                <span><i class="fas fa-circle white"></i> White </span>
                            </li>
                        </ul>
                        <div class="pulse-indicator-counts">
                            <span class="nowrap ellipsis">
                                <span class="insered">
                                    <strong>FileHash-MD5:</strong>
                                    <span class="br-last">48</span>
                                </span>
                                <span class="insered">
                                    <strong>FileHash-SHA1:</strong>
                                    <span class="br-last">3</span>
                                </span>
                                <span class="insered">
                                    <strong>FileHash-SHA256:</strong>
                                    <span class="br-last">17</span>
                                </span>
                                <span class="insered">
                                    <strong>URL:</strong>
                                    <span>94</span>
                                </span>
                            </span>
                        </div>
                        <div class="indicator-description">
                            <span class="nowrap ellipsis">
                                Gafgyt botnets have a long history of infecting Linux devices to
                                launch DDoS attacks. While dozens of variants have been detected,
                                new variants are constantly emerging with changes in terms of
                                register message, exploits, and attacking methods. On the other
                                hand, their new botnets are usually short lived, with most of the
                                C2s watched keeping active for only a few days.
                            </span>
                        </div>
                        <div class="by-items">
                            <a href="#"><span>linux</span></a>,
                            <a href="#"><span>iot</span></a>,
                            <a href="#"><span>malware</span></a>
                        </div>
                    </div>
                </div>
                <div class="related-subscribers">
                    <span class="star-count">116,017</span>
                    <span class="subscribers">
                        <i></i>&nbsp;SUBSCRIBERS
                    </span>
                </div>
            </div>
        </li>


        <li>
            <div class="related-pulses">
                <div class="related-img">
                    <img src="{{asset(\'images/avatar_1.png\')}}" alt="">
                </div>
                <div class="related-content">
                    <div class="related-title">
                        <a href="pulsedetail">
                            <h1 class="related-title">
                                The Gafgyt variant vbot seen in its 31 campaigns
                            </h1>
                        </a>
                        <div class="active-indicator">
                            <div class="dot green"></div>
                            <div> URL Indicator Active </div>
                        </div>
                    </div>
                    <div class="details-wrapper">
                        <ul class="detail-show">
                            <li>
                                <span class="modified"> Modified </span>
                                <span class="pulse-ago"> 3 HOURS AGO </span>
                                by <a href="" class="pulse-author">MTSC</a>
                            </li>
                            <li>
                                <span class="stat-label"> Public </span>
                            </li>
                            <li>
                                <a href="https://www.us-cert.gov/tlp" target="_new">TLP</a>:
                                <span><i class="fas fa-circle white"></i> White </span>
                            </li>
                        </ul>
                        <div class="pulse-indicator-counts">
                            <span class="nowrap ellipsis">
                                <span class="insered">
                                    <strong>FileHash-MD5:</strong>
                                    <span class="br-last">48</span>
                                </span>
                                <span class="insered">
                                    <strong>FileHash-SHA1:</strong>
                                    <span class="br-last">3</span>
                                </span>
                                <span class="insered">
                                    <strong>FileHash-SHA256:</strong>
                                    <span class="br-last">17</span>
                                </span>
                                <span class="insered">
                                    <strong>URL:</strong>
                                    <span>94</span>
                                </span>
                            </span>
                        </div>
                        <div class="indicator-description">
                            <span class="nowrap ellipsis">
                                Gafgyt botnets have a long history of infecting Linux devices to
                                launch DDoS attacks. While dozens of variants have been detected,
                                new variants are constantly emerging with changes in terms of
                                register message, exploits, and attacking methods. On the other
                                hand, their new botnets are usually short lived, with most of the
                                C2s watched keeping active for only a few days.
                            </span>
                        </div>
                        <div class="by-items">
                            <a href="#"><span>linux</span></a>,
                            <a href="#"><span>iot</span></a>,
                            <a href="#"><span>malware</span></a>
                        </div>
                    </div>
                </div>
                <div class="related-subscribers">
                    <span class="star-count">116,017</span>
                    <span class="subscribers">
                        <i></i>&nbsp;SUBSCRIBERS
                    </span>
                </div>
            </div>
        </li>
    </ul>' ;

        if ($request->ajax()) {
            $data = [
                "html" => $testto,
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

        // dd($news);

        if ($request->keywords) {

            $data = OtxIndicatiorData::where("status", '=', 1)->where('indicatior', 'LIKE', '%' . $request->keywords . '%')->paginate(10);
            $count = $data->count();
        } else {
            $count = OtxIndicatiorData::where("status", '=', 1)->count();
            $data = OtxIndicatiorData::where("status", '=', 1)->paginate(10);
        }

        foreach ($data as $data) {
            $html .= ' <ul class="list-indicators">
                        <li>
                            <a href="' . route('indicators.detail_indicators', ['id' => $data->id, 'type' => $data->type, 'indicatior' => $data->indicatior]) . '">
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