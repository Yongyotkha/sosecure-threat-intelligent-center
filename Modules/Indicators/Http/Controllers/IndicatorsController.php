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

    public function show_detail_indicators()
    {
        $data['page'] = langapp('indicators');
        return view('indicators::detail_indicators')->with($data);
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

        // $date_start = $request->date_start;

        // $date_start_explode = explode(" ",$date_start);
        // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
        // // dd($date_start_time);
        // $date_start_time_time = date("H:i", strtotime($date_start_time));
        // // dd($date_start_time_time);

        // date("H:i", strtotime("04:25 PM"))
        $html = '';

        // $news_all = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();

        // if($request -> title || $request -> cate || $request -> related_news || $request -> lang_th || $request -> lang_en || $request -> date_start || $request -> date_end){

        //     $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
        //     if($request -> title){
        //         $news = $news -> where('title_th', 'LIKE' ,'%'.$request -> title.'%');
        //     }
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
        $count = OtxIndicatiorData::where("status", '=', 1)->count();
        $data = OtxIndicatiorData::where("status", '=', 1)->paginate(10);
        foreach ($data as $data) {
            $html .= ' <ul class="list-indicators">
                        <li>
                            <a href="">
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