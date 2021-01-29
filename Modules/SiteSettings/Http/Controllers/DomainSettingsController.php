<?php

namespace Modules\sitesettings\Http\Controllers;

use Modules\CategorySettings\Entities\CategorySettings;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Http\Requests\DomainRequest;
use App\TransactionTimeStampScans;

// use Modules\SiteSettings\Jobs\BulkDeleteDomainSettings;

class DomainSettingsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    protected $domain;
    protected $TransactionTimeStampScans;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings, Domain $domain, TransactionTimeStampScans $TransactionTimeStampScans)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
        $this->domain = $domain;
        $this->TransactionTimeStampScans = $TransactionTimeStampScans;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function domain_setting($id)
    {
        // dd(1);
        $get_data = $this->siteSettings->get_data($id);
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Domain';
        return view('sitesettings::domain')->with($data);
    }


    public function domain_detail($tab = 'overview')
    {
        $allowed      = ['overview', 'datatype', 'settings', 'logs'];
        $tab          = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain';
        $data['tab']  = $tab;
        return view('sitesettings::domain_detail')->with($data);
    }
    

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        $SiteSettings = SiteSettings::where('code',$request->code)->first();
        $data['Domain'] = Domain::where('site_id',$SiteSettings->id)->where('domain_default',1)->first();
    
        // dd($data['Domain1'] );
        $data['code']  = $request->code;
        return view('sitesettings::modal.create_domain')->with($data);
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


    public function save(DomainRequest $request)//DomainRequest
    {
        // dd($request);
        // $this->authorize('create', Domain::class);
        // $Domain = $this->Domain->create($request->all());
        $SiteSettings = SiteSettings::where('code',$request->code)->where("active",1)->where("deleted_at",null)->first();
        // if($SiteSettings->domain_allow != 'Y') {
        //     return response()->json(['status' => 'warning', 'message' => 'Add domain not allow.', 'errors' => 'test'], 400);
        //     // return response()->json(['error' => 'Unauthorized', 'code_status' => '401']);
        //     // return response()->json([
        //     //             'error' => 'Token not provided.',
        //     //             'message' => 'Token not provided.',
        //     //             'status_code' => '02',//error
        //     //         ], 401);
        //     exit();
        // }
        $segments = request()->segments();
        $last_segments  = end($segments);
        // $segment3 =  request()->segment(3);
        // dd($segment3);

        $domain_check_limit = Domain::where('site_id',$SiteSettings->id)->where('status',1)->where('deleted_at',null)->get()->count();

        $domain_allow = $SiteSettings->domain_allow;
        $domain_limit_amount = $SiteSettings->domain_limit;

        if($SiteSettings) {

            if($domain_allow == 'Y') {//allow
                if($domain_check_limit >= $domain_limit_amount) {//limit
                    return response()->json(['message' => 'Failure, domain exceeded limit!', 'errors' => ['missing' => ["Failure, domain exceeded limit! "]]], 500);
                } else {//limit pass

                    $domain = $request->domain;
                    if($domain) {
                        $Domain_check_domain = Domain::where('domain',$domain)->where('deleted_at', null)->get()->count();
                        if($Domain_check_domain > 0) {
                            return response()->json(['message' => 'this domain already exist', 'errors' => ['missing' => ["this domain already exist "]]], 500);
                        }
                    }



                    $code = $request->code;

                    $SiteSettings = SiteSettings::where('code',$code)->first();
            

                    if($request->default){
                        Domain::where('site_id',$SiteSettings->id)->update(['domain_default' => 0]);
                    }

                    $Domain = $this->domain;
                    $Domain->code = generator_uuid();
                    $Domain->name = $request->name;
                    $Domain->domain = $this->remove_http($request->domain);
                    // $Domain->created_by = @Auth::user()->id;
                    $Domain->status = $request->status ? 1 : 0;
                    $Domain->domain_default = $request->default ? 1 : 0;
                    $Domain->site_id = $SiteSettings->id;
                    $Domain->save();
            
                    // foreach($request->category AS $cate) {
                    //     $SiteCategory = new SiteCategory;
                    //     $SiteCategory->site_id = $Domain->id;
                    //     $SiteCategory->category_id = $cate;
                    //     $SiteCategory->save();
                    // }
            
                    // if ($request->hasFile('logo')) {
                    //     $this->uploadLogo($request, $Domain);
                    // }
            
                    if($request->formsubmit == 'formSavingAndRun'){
                        $TransactionTimeStampScans = $this->TransactionTimeStampScans;
                        $TransactionTimeStampScans->code = generator_uuid(); 
                        $TransactionTimeStampScans->created_by = @Auth::user()->id;
                        $TransactionTimeStampScans->site_id = $SiteSettings->id;
                        $TransactionTimeStampScans->domain_id = $Domain->id;
                        $TransactionTimeStampScans->status = 1;
                        $TransactionTimeStampScans->progress = 0;
                        $TransactionTimeStampScans->save();
                    }
            
                    return ajaxResponse(
                        [
                            'id'       => $Domain->id,
                            'message'  => langapp('saved_successfully'),
                            'redirect' =>route('domain.index', ['id' => $SiteSettings->code]),
                        ],
                        true,
                        Response::HTTP_CREATED
                    );

                }

            } else {//not allow
                return response()->json(['message' => 'Failed, adding domain is not allowed.!', 'errors' => ['missing' => ["Failed, adding domain is not allowed.! "]]], 500);
            }
    
        }
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
    // public function edit($id)
    // {
    //     return view('sitesettings::edit');
    // }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(DomainRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        $domain = $this->domain->findOrFail($id);
        // $domain->update($request->all());
        $domain->name = $request->name;
        $domain->domain = $this->remove_http($request->domain);
        // $domain->open_scan = $request->open_scan;
        // $domain->scan_interval = $request->scan_interval;
        $domain->status = $request->status ? 1 : 0;
        if($request->default){
            Domain::where('site_id',$domain->site_id)->update(['domain_default' => 0]);
        }
        $domain->domain_default = $request->default ? 1 : 0;
        $domain->save();

        $site_code = $this->siteSettings->find_code($domain->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $domain);
        // }
        return ajaxResponse(
            [
                'id'       => $domain->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('domain.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    private function remove_http($url) {
        $disallowed = array('http://', 'https://', 'http://www.', 'https://www.', 'www.');
        foreach($disallowed as $d) {
           if(strpos($url, $d) === 0) {
              return str_replace($d, '', $url);
           }
        }
        return $url;
     }

    public function delete_process($id = null)
    {
        $model = $this->domain->find($id);
        // dd($model);
        $model->delete();

        $site_code = $this->siteSettings->find_code($model->site_id);

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('domain.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
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

    // public function change_status(Request $request)
    // {
    //     // dd($request);
    //     // exit();
    //     $data['id'] = $this->request->id;
    //     $domain = $this->domain->findOrFail($data['id']);
    //     // $domain->update($request->all());
    //     // $domain->name = $request->name;
    //     $domain->status = $domain->status == 1 ? 0 : 1;
    //     $domain->save();

    //     $site_code = $this->siteSettings->find_code($dmain->site_id);

    //     // if ($request->hasFile('logo')) {
    //     //     $this->uploadLogo($request, $client);
    //     // }
    //     return ajaxResponse(
    //         [
    //             'id'       => $domain->id,
    //             'message'  => langapp('changes_saved_successful'),
    //             'redirect' => route('domain.index',['id' => $site_code->code]),
    //         ],
    //         true,
    //         Response::HTTP_OK
    //     );
    // }

    public function change_status(Request $request)
    {
        $Domain = Domain::where('code', $request->code)->first();
        $Domain->status = $request->active;
        $Domain->save();

        $site_code = $this->siteSettings->find_code($Domain->site_id);

        return ajaxResponse(
            [
                'id'       => $Domain->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('domain.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }



    // public function bulkDelete()
    // {
    //     if ($this->request->has('checked')) {
    //         BulkDeleteDomainSettings::dispatch($this->request->checked, Auth::id());
    //         $data['message']  = langapp('deleted_successfully');
    //         $data['redirect'] = url()->previous();
    //         return ajaxResponse($data);
    //     }
    //     return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    // }

    public function test() {
        $model = $this->domain->query();
        // dd($model);
        // return $modal;
       dd(DataTables::eloquent($model)->make(true));
    }

    public function del_domain_select(Request $request){
        $site_code = 0;
        foreach($request -> id as $key => $id){
            $model = Domain::find($id);
            $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $model->site_id)
            ->where('status', 1)
            ->where('domain_id', $id)
            ->first();
            if($key == 0){
                $site_code = $this->siteSettings->find_code($model->site_id);
            }
            if($TransactionTimeStampScans){
                if($TransactionTimeStampScans -> progress == 3){
                    $model->delete();
                    $TransactionTimeStampScans -> status = 0;
                    $TransactionTimeStampScans -> save();
                } 
            }else{
                $model->delete();
            }
        }
        

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('domain.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
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
        $model = Domain::query();
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
                function ($domain) {
                    return $domain->id;
                }
            )
            ->editColumn(
                'chk',
                function ($domain) {
                    return '<label><input type="checkbox" name="checked" class="domain_id" value="' . $domain->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'name',
                function ($domain) {
                    return $domain->name;
                }
            )
            ->editColumn(
                'domain',
                function ($domain) {
                    return '<label>'.$domain->domain.'</label>';
                }
            )
            ->addColumn('elements', function ($domain) {
                $html = '';
                $html .= @$domain->get_transaction_time_stamp_scans->elements;
                // $html = get_name_scan_status($domain -> progress , 'badg');
                return $html;
            })
            ->addColumn('progress', function ($domain) {
                $html = get_name_scan_status(@$domain->get_transaction_time_stamp_scans->progress , 'badg');
                return $html;
            })
            ->editColumn(
                'domain_default',
                function ($domain) {
                    if($domain->domain_default == '1') {
                        $html = '<div><i class="fas fa-check"></i></div>';
                    } else {
                        $html = '';
                    }


                    return $html;
                }
            )

            ->addColumn('action', function ($domain) {
                $html = '';
                            if(@$domain -> get_transaction_time_stamp_scans -> progress !== 3){
                                $html .= "<a href='#' class='disabled btn btn-". get_option('theme_color') ." btn-c-xs'>
                                    <span>
                                    <i class='far fa-eye'></i>
                                    </span>
                                </a>";
                            }else{
                                $html .= "<a href='". route('scans.index', ['tab' => 'overview', 'site_code' => @$domain->get_transaction_time_stamp_scans->code]) ."' class='btn btn-". get_option('theme_color') ." btn-c-xs'>
                                    <span>
                                    <i class='far fa-eye'></i>
                                    </span>
                                </a>";
                            }
                            if(@$domain -> get_transaction_time_stamp_scans -> progress !== 1 && @$domain -> get_transaction_time_stamp_scans -> progress !== 2){
                                $html .= "<a href='". route('domainsettings.redo', ['id' => $domain->code]) ."' class='btn btn-". get_option('theme_color') ." btn-c-xs' data-toggle='ajaxModal'>
                                    <span>
                                    <i class='fas fa-redo'></i>
                                    </span>
                                </a>";
                            }else{
                                $html .= "<a href='#' class='disabled btn btn-". get_option('theme_color') ." btn-c-xs'>
                                    <span>
                                    <i class='fas fa-redo'></i>
                                    </span> 
                                </a>";
                            }
                            if($domain -> get_transaction_time_stamp_scans){
                                if($domain -> get_transaction_time_stamp_scans -> progress !== 3){
                                    $html .= "<a href='". route('domainsettings.edit', ['id' => $domain->id]) ."' class='disabled btn btn-". get_option('theme_color') ." btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                    </a>
                                    <a href='". route('domainsettings.delete', ['id' => $domain->id]) ."' class='disabled btn btn-danger btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                    </a>";
                                }else{
                                    $html .= "<a href='". route('domainsettings.edit', ['id' => $domain->id]) ."' class='btn btn-". get_option('theme_color') ." btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                    </a>
                                    <a href='". route('domainsettings.delete', ['id' => $domain->id]) ."' class='btn btn-danger btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                    </a>";
                                }
                            }else{
                                $html .= "<a href='". route('domainsettings.edit', ['id' => $domain->id]) ."' class='btn btn-". get_option('theme_color') ." btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                    </a>
                                    <a href='". route('domainsettings.delete', ['id' => $domain->id]) ."' class='btn btn-danger btn-c-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                    </a>";
                            }
                            
                           
                            $html .= "</div>";
                return $html;
            })
            ->rawColumns(['chk','name','domain','domain_default','progress','elements','action'])
            ->make(true);
    }


    public function edit(Domain $id)
    {
        $data['domain'] = $id;
        $domain = Domain::where('id',$id->id)->first();
        $data['Domain_count'] = Domain::where('site_id',$domain->site_id)->count();

        // dd($id);
        return view('sitesettings::modal.update_domain')->with($data);
    }

    public function redo($id)
    {
        $data['domain'] = $this->domain->get_data($id);
        return view('sitesettings::modal.redo_domain_transaction')->with($data);
    }

    public function delete(Domain $id)//del_domain
    {
        $data['domain'] = $id;
        return view('sitesettings::modal.delete_domain')->with($data);
    }

    public function redo_process($id){
        $get_data = $this->domain->get_data($id);
        $site_code = $this->siteSettings->find_code($get_data->site_id);
        $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $get_data->site_id)->where('domain_id', $get_data->id)->first();
        if($TransactionTimeStampScans){
            $TransactionTimeStampScans -> progress = 0;
            $TransactionTimeStampScans -> save();
        }else{
            $TransactionTimeStampScans = new TransactionTimeStampScans(); 
            $TransactionTimeStampScans->code = generator_uuid(); 
            $TransactionTimeStampScans->created_by = @Auth::user()->id;
            $TransactionTimeStampScans->site_id = $get_data->site_id;
            $TransactionTimeStampScans->domain_id = $get_data->id;
            $TransactionTimeStampScans->status = 1;
            $TransactionTimeStampScans->progress = 0;
            $TransactionTimeStampScans->save();
        }
       
        return ajaxResponse(
            [
                'message'  => "Successfully",
                'redirect' => route('domain.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }
    
}
