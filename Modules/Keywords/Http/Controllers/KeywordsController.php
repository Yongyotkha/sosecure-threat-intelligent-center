<?php

namespace Modules\Keywords\Http\Controllers;

use Modules\SiteSettings\Entities\Site_keywords;
use Modules\SiteSettings\Entities\site_keywords_main;
use Modules\SiteSettings\Entities\SiteSettings;
use DataTables;
use Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use MongoDB\Client as MongoClient;
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
                    return $q->where('site_id','=', $site_id)->orderBy('type', 'desc')->get();
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
                    return '<label><input type="checkbox" class="keyword_id" name="keyword_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'name',
                function ($model) {
                    return $model->name;
                }
            )
            ->editColumn(
                'type',
                function ($model) {
                    return $model->type;
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
                            <a href='". route('KeywordsController.delete', ['id' => $model->id]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
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
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_social_Keyword = $clientMD->social->Keyword;

        $Site_keywords = Site_keywords::where("id",$id)->first();

        if($Site_keywords->type == 'social'){
            $isDeleteKeyword = true;
            $all_site_Keywords = Site_keywords::where("name",$Site_keywords->name)->where("id",'!=',$Site_keywords->id)->where("deleted_at",null)->get()->toArray();
            if(count($all_site_Keywords)>0){
                foreach ($all_site_Keywords as $key => $value) {
                    $findSite = SiteSettings::where('id',$value["site_id"])->where("active",1)->where("deleted_at",null)->first();
                    if(!empty($findSite)){
                        $isDeleteKeyword = false;
                    }
                }
            }
            if($isDeleteKeyword){
                $deleteResult = $col_social_Keyword->deleteOne(['_id' => $Site_keywords->name,'created_by'=>'mtsc']);
            }
        }
        
        
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

    
    public function delete_checked(Request $request)
    {

        //  dd($request->id);

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_social_Keyword = $clientMD->social->Keyword;
        
        foreach($request->id as $keyword_id){
            $Site_keywords  = Site_keywords::where('id', $keyword_id)->first();
            
            if($Site_keywords->type == 'social'){
                $isDeleteKeyword = true;
                $all_site_Keywords = Site_keywords::where("name",$Site_keywords->name)->where("id",'!=',$Site_keywords->id)->where("deleted_at",null)->get()->toArray();
                if(count($all_site_Keywords)>0){
                    foreach ($all_site_Keywords as $key => $value) {
                        $findSite = SiteSettings::where('id',$value["site_id"])->where("active",1)->where("deleted_at",null)->first();
                        if(!empty($findSite)){
                            $isDeleteKeyword = false;
                        }
                    }
                }
                if($isDeleteKeyword){
                    $deleteResult = $col_social_Keyword->deleteOne(['_id' => $Site_keywords->name,'created_by'=>'mtsc']);
                }
            }
            

            $data = Site_keywords::where("id",$keyword_id);
            $data->delete();

        }

        $SiteSettings = SiteSettings::where('id',$Site_keywords->site_id)->first();

        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('keyword.index',['id' => @$SiteSettings->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function save_add_keyword_main(Request $request)
    {
        $message = '';
        $status = 0;

        $code_site = $request->code_site;
        $keyword_name = $request->keyword_name;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        $site_keywords_main = site_keywords_main::where('site_id', $site->id)->where('name',$keyword_name)->where('status',1)->whereNull('deleted_at')->first();
        if(!$site_keywords_main) {
            $site_keywords_main_insert = new site_keywords_main;
            $site_keywords_main_insert->code = generator_uuid();
            $site_keywords_main_insert->site_id = $site->id;
            $site_keywords_main_insert->name = $keyword_name;
            $site_keywords_main_insert->status = 1;
            $site_keywords_main_insert->created_by = Auth::user()->id;
            $site_keywords_main_insert->save();

            $message = langapp('changes_saved_successful');
            $status = 1;
        } else {
            toastr()->warning('!Error Duplicate Keyword.', langapp('response_status'));
            // return response()->json(['message' => 'Error Duplicate Keyword', 'errors' => ['missing' => ['Please new Keyword name. ']]], 500);
            $message = '!Error Duplicate Keyword.';
            $status = 0;
        }

        // dd($message);

        return ajaxResponse(
            [
                'id'       => @$site_keywords_main_insert->id,
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function get_keyword_main(Request $request)
    {
        $message = '';
        $status = 0;

        $code_site = $request->code_site;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        $site_keywords_main = site_keywords_main::where('site_id', $site->id)->where('status',1)->whereNull('deleted_at')->get();
        if(!$site_keywords_main) {

            $message = langapp('changes_saved_successful');
            $status = 1;
        } else {
            // toastr()->warning('!Error Duplicate Keyword.', langapp('response_status'));
            // return response()->json(['message' => 'Error Duplicate Keyword', 'errors' => ['missing' => ['Please new Keyword name. ']]], 500);
            $message = '';
            $status = 1;
        }

        // dd($message);

        return ajaxResponse(
            [
                'data'       => @$site_keywords_main,
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function get_keyword_sub(Request $request)
    {
        $message = '';
        $status = 0;

        $type = $request->type;
        $code_site = $request->code_site;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        $Site_keywords = Site_keywords::where('site_id', $site->id)->where('status',1)->whereNull('deleted_at')->where('type',$type)->get();
        if(!$Site_keywords) {

            $message = langapp('changes_saved_successful');
            $status = 1;
        } else {
            // toastr()->warning('!Error Duplicate Keyword.', langapp('response_status'));
            // return response()->json(['message' => 'Error Duplicate Keyword', 'errors' => ['missing' => ['Please new Keyword name. ']]], 500);
            $message = '';
            $status = 1;
        }

        // dd($message);

        return ajaxResponse(
            [
                'data'       => @$Site_keywords,
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function edit_keyword_process(Request $request)
    {
        $message = '';
        $status = 0;

        $keywords_main_id_edit = $request->keywords_main_id_edit;
        $keyword_input = $request->keyword_input;
        $code_site = $request->code_site;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        if(($site) && ($keywords_main_id_edit) && ($keyword_input)) {
            $site_keywords_main = site_keywords_main::where('id',$keywords_main_id_edit)->where('site_id',$site->id)->first();
            $Site_keywords = Site_keywords::where('keywords_main_id',$keywords_main_id_edit)->where('site_id',$site->id)->get();
            // $check = site_keywords_main::where('id',$keywords_main_id_edit)->where('site_id',$site->id)->first();
            
            if($site_keywords_main) {
                $site_keywords_main->name = $keyword_input;
                $site_keywords_main->save();
                if($site_keywords_main) {
                    foreach($Site_keywords as $val) {
                        $val->name = $keyword_input;
                        $val->save();
                    }
                }
                $message = langapp('changes_saved_successful');
                $status = 1;
            } else {
                $message = '';
                $status = 1;
            }
        }


        // dd($message);

        return ajaxResponse(
            [
                'data'       => '',
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function del_keyword_process(Request $request)
    {
        $message = '';
        $status = 0;

        $keywords_main_id_del = $request->keywords_main_id_del;
        $keywords_sub_id_del = $request->keywords_sub_id_del;
        $keywords_type_del = $request->keywords_type_del;
        $code_site = $request->code_site;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        if(($keywords_type_del) && ($keywords_main_id_del || $keywords_sub_id_del)) {
            if($keywords_type_del == 'main') {
                $site_keywords_main = site_keywords_main::where('id',$keywords_main_id_del)->where('site_id',$site->id)->delete();
                $keywords_sub_id_del = Site_keywords::where('keywords_main_id',$keywords_main_id_del)->where('site_id',$site->id)->delete();
                $check = site_keywords_main::where('id',$keywords_main_id_del)->where('site_id',$site->id)->first();
                if(!$check) {
                    $message = langapp('changes_saved_successful');
                    $status = 1;
                } else {
                    $message = '';
                    $status = 1;
                }
            } else if ($keywords_type_del == 'sub') {
                $keywords_sub_id_del = Site_keywords::where('id',$keywords_sub_id_del)->where('site_id',$site->id)->delete();
                $check = Site_keywords::where('id',$keywords_sub_id_del)->where('site_id',$site->id)->first();
                if(!$check) {
                    $message = langapp('changes_saved_successful');
                    $status = 1;
                } else {
                    $message = '';
                    $status = 1;
                }
            }
        }


        // dd($message);

        return ajaxResponse(
            [
                'data'       => '',
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function check_insert_keyword_process(Request $request)
    {
        $message = '';
        $status = 0;

        $from_id = $request->from_id;
        $to_id = $request->to_id;
        $attributes_id = $request->attributes_id;
        $code_site = $request->code_site;
        $site = SiteSettings::select('id')->where('code', $code_site)->first();

        if(($from_id) && ($to_id || $attributes_id)) {

            if($from_id == 'keyword_main') {
                $site_keywords_main = site_keywords_main::where('id',$attributes_id)->first();
                if($to_id == 'social_main') {
                    $Site_keywords_check = Site_keywords::where('keywords_main_id',$attributes_id)->where('site_id',$site->id)->where('type','social')->first();
                    if($Site_keywords_check) {

                    } else {
                        $Site_keywords_insert = new Site_keywords;
                        $Site_keywords_insert->code = generator_uuid();
                        $Site_keywords_insert->keywords_main_id = $attributes_id;
                        $Site_keywords_insert->site_id = $site->id;
                        $Site_keywords_insert->name = $site_keywords_main->name;
                        $Site_keywords_insert->type = 'social';
                        $Site_keywords_insert->status = 1;
                        $Site_keywords_insert->created_by = Auth::user()->id;
                        $Site_keywords_insert->save();
                    }
                } else if ($to_id == 'darkweb_main') {
                    $Site_keywords_check = Site_keywords::where('keywords_main_id',$attributes_id)->where('site_id',$site->id)->where('type','darkweb')->first();
                    if($Site_keywords_check) {

                    } else {
                        $Site_keywords_insert = new Site_keywords;
                        $Site_keywords_insert->code = generator_uuid();
                        $Site_keywords_insert->keywords_main_id = $attributes_id;
                        $Site_keywords_insert->site_id = $site->id;
                        $Site_keywords_insert->name = $site_keywords_main->name;
                        $Site_keywords_insert->type = 'darkweb';
                        $Site_keywords_insert->status = 1;
                        $Site_keywords_insert->created_by = Auth::user()->id;
                        $Site_keywords_insert->save();
                    }
                }
            } else if ($from_id == 'social_main') {
                $Site_keywords = Site_keywords::where('id',$attributes_id)->first();
                $keywords_main_id = $Site_keywords->keywords_main_id;
                $site_keywords_main = site_keywords_main::where('id',$keywords_main_id)->first();
                if($Site_keywords) {
                    $Site_keywords->delete();

                    $Site_keywords_insert = new Site_keywords;
                    $Site_keywords_insert->code = generator_uuid();
                    $Site_keywords_insert->keywords_main_id = $keywords_main_id;
                    $Site_keywords_insert->site_id = $site->id;
                    $Site_keywords_insert->name = $site_keywords_main->name;
                    $Site_keywords_insert->type = 'darkweb';
                    $Site_keywords_insert->status = 1;
                    $Site_keywords_insert->created_by = Auth::user()->id;
                    $Site_keywords_insert->save();
                } else {

                }

            } else if ($from_id == 'darkweb_main') {
                $Site_keywords = Site_keywords::where('id',$attributes_id)->first();
                $keywords_main_id = $Site_keywords->keywords_main_id;
                $site_keywords_main = site_keywords_main::where('id',$keywords_main_id)->first();
                if($Site_keywords) {
                    $Site_keywords->delete();

                    $Site_keywords_insert = new Site_keywords;
                    $Site_keywords_insert->code = generator_uuid();
                    $Site_keywords_insert->keywords_main_id = $keywords_main_id;
                    $Site_keywords_insert->site_id = $site->id;
                    $Site_keywords_insert->name = $site_keywords_main->name;
                    $Site_keywords_insert->type = 'darkweb';
                    $Site_keywords_insert->status = 1;
                    $Site_keywords_insert->created_by = Auth::user()->id;
                    $Site_keywords_insert->save();
                } else {

                }
            }

            $Site_keywords = new Site_keywords;
            $check = site_keywords_main::where('id',$keywords_main_id_del)->where('site_id',$site->id)->first();
            if(!$check) {
                $message = langapp('changes_saved_successful');
                $status = 1;
            } else {
                $message = '';
                $status = 1;
            }
            
        }


        // dd($message);

        return ajaxResponse(
            [
                'data'       => '',
                'message'  => $message,
                'status'   => $status,
                'redirect' => route('keyword.index',['id' => $code_site]),
            ],
            true,
            Response::HTTP_OK
        );
    }


}
