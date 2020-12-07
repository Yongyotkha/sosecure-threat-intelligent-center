<?php

namespace Modules\SiteSettings\Http\Controllers;

use App\DataLeakSocialRef;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Yajra\DataTables\Facades\DataTables;

class DataLeakController extends Controller
{
        /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    
    public function keyword($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Keyword Setting';
       return view('sitesettings::keyword_setting')->with($data);
    }

    public function datafeed()
    {
       $data['page'] = 'Data Feed(Social)';
       return view('sitesettings::datafeed')->with($data);
    }

    public function socialdatas($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Social Datas';
       return view('sitesettings::social-datas')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::create');
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
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('sitesettings::edit');
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

    public function socialdatas_datatables(Request $request){
        $site =  $this->siteSettings->get_data($request->site_code);
        $model = DataLeakSocialRef::where('site_id', $site->id)->where('deleted_at', null)->orderBy('id', 'desc');
        if($request -> search){
            if($request -> search){
                $search = $request -> search;
                $model = $model -> whereHas('get_data_leak_feed', function($query) use ($search){
                    $query -> where('feedcontent', 'LIKE' , '%'.$search.'%');
                    $query -> orwhere('tag', 'LIKE' , '%'.$search.'%');
                });
            }
            $model = $model -> get();
        }else{
            $model = $model -> get();
        }
        return DataTables::of($model)
        ->editColumn(
            'chk',
            function (DataLeakSocialRef $model) {
                return '<label><input type="checkbox" name="checked" value="' . $model->id . '"><span class="label-text"></span></label>';
            }
        )
        ->editColumn(
            'source',
            function (DataLeakSocialRef $model) {
                return $model -> get_data_leak_feed -> tag;
            }
        )
        ->editColumn(
            'keyword',
            function (DataLeakSocialRef $model) {
                return $model -> keyword;
            }
        )
        ->editColumn(
            'content',
            function (DataLeakSocialRef $model) {
                return $model -> get_data_leak_feed -> feedcontent;
            }
        )
        ->editColumn(
            'data_feed',
            function (DataLeakSocialRef $model) {
                return $model -> get_data_leak_feed -> feedtimestamp;
            }
        )
        ->editColumn(
            'view_count',
            function (DataLeakSocialRef $model) {
                return $model -> view;
            }
        )
        ->editColumn(
            'status',
            function (DataLeakSocialRef $model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="status_'.$model->code.'" onchange="change_status(\''. $model->code .'\')" '.$checked_val.' name="status" value="1">
                            <span></span>
                            </label>';

                return $html;
            }
        )
        ->editColumn(
            'action',
            function (DataLeakSocialRef $model) {
                return "<a href='". route('socialdatas.delete', ['code' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a>";
            }
        )
        ->rawColumns(['chk','source','keyword','content','data_feed','view_count','status','action'])
        ->make(true);
    }

    public function change_status(Request $request){
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $DataLeakSocialRef->status = $request->status;
        $DataLeakSocialRef->save();

        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_socialdatas(Request $request){
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $data['DataLeakSocialRef'] = $DataLeakSocialRef;
        return view('sitesettings::modal.delete_socialdatas')->with($data);
    }

    public function delete_socialdata(Request $request){
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $DataLeakSocialRef->deleted_at = Carbon::now();
        $DataLeakSocialRef->save();

        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
