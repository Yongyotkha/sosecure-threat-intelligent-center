<?php

namespace Modules\Keywords\Http\Controllers;

use Modules\SiteSettings\Entities\Site_keywords;
use Modules\SiteSettings\Entities\SiteSettings;
use DataTables;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class KeywordsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $Site_keywords;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, Site_keywords $Site_keywords)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->Site_keywords = $Site_keywords;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $data['page'] = langapp('keywords');
       return view('keywords::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('keywords::create');
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
        return view('keywords::show');
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


    public function tableData(Request $request)
    {
        $site_id = '';
        $site_code = $this->request->site_code;
        // $site_code = 'a7b6ff37-30ec-4494-9527-93b0ccc51d56';
        $site_id_find = SiteSettings::where("code",$site_code)->first();
        if($site_code) {
            $site_id = $site_id_find->id;
        }
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        
        // var_dump($model);
        // exit();
        // $model = $this->domain->query();
        $model = Site_keywords::query();
        // $model = TransactionTimeStampScans::query();
        $test = 1;
        if($site_id) {
            $model->when(
                $test == 1,
                function ($q) use ($site_id) {
                    return $q->where('site_id','=', $site_id);
                }
            );
        }



        return DataTables::eloquent($model)
            ->editColumn(
                'no',
                function ($model) {
                    return $model->id;
                }
            )
            ->editColumn(
                'chk',
                function ($model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'name',
                function ($model) {
                    return $model->name;
                }
            )
            ->editColumn(
                'last_update',
                function ($model) {
                    if($model->updated_at) {
                        $last_update = $model->updated_at;
                    } else {
                        $last_update = $model->created_at;
                    }
                    return $last_update;
                }
            )
            ->editColumn(
                'status',
                function ($model) {
                    if($model->status == '1') {
                        $checked_val = 'checked';
                    } else {
                        $checked_val = '';
                    }
                    $html = '';
                    
                    // $html = '';
                    $html .= '<label class="switch">
                                <input type="checkbox" id="keyword_active_'.$model->code.'" onchange="change_keyword_active(\''. $model->code .'\')" '.$checked_val.' name="active" value="1">
                                <span></span>
                              </label>';

                    return $html;
                }
            )

            ->addColumn('action', function ($model) {
                $html = '';
                            $html .= "<a href='". route('KeywordsController.edit', ['id' => $model->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                            </a>
                            <a href='". route('KeywordsController.delete', ['id' => $model->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                            </a></div>";
                return $html;
            })
            ->rawColumns(['chk','name','last_update','status','action'])
            ->make(true);
    }

    public function edit(Site_keywords $id)
    {
        $data['Site_keywords'] = $id;
        // dd($id);
        return view('sitesettings::modal.update_keyword')->with($data);
    }

    public function delete(Site_keywords $id)//del_domain
    {
        $data['Site_keywords'] = $id;
        return view('keywords::modal.delete_keyword')->with($data);
    }

    public function delete_process($id = null)
    {
        $Site_keywords = Site_keywords::where("id",$id)->first();
        $model = Site_keywords::where("id",$id);
        // dd($model);
        $model->delete();

        // $site_code = $this->siteSettings->find_code($model->$site_id);
        $SiteSettings = SiteSettings::where('id',$Site_keywords->site_id)->first();

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('keyword.index',['id' => @$SiteSettings->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }


}
