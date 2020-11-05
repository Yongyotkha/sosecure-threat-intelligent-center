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

use Modules\SiteSettings\Jobs\BulkDeleteDomainSettings;

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
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings, Domain $domain)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
        $this->domain = $domain;
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
        $data['page'] = 'Domain Settings';
        return view('sitesettings::domain')->with($data);
    }


    public function domain_detail($tab = 'overview')
    {
        $allowed      = ['overview', 'datatype', 'settings', 'logs'];
        $tab          = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab']  = $tab;
        return view('sitesettings::domain_detail')->with($data);
    }
    

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        $code = $request->code;
        return view('sitesettings::modal.create_domain',compact('code'));
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
        $segments = request()->segments();
        $last_segments  = end($segments);
        // $segment3 =  request()->segment(3);
        // dd($segment3);
        $code = $request->code;

        $SiteSettings = SiteSettings::where('code',$code)->first();

        $Domain = $this->domain;
        $Domain->code = generator_uuid();
        $Domain->name = $request->name;
        $Domain->domain = $request->domain;
        // $Domain->created_by = @Auth::user()->id;
        $Domain->status = $request->status ? 1 : 0;
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
        $domain->domain = $request->domain;
        // $domain->open_scan = $request->open_scan;
        // $domain->scan_interval = $request->scan_interval;
        $domain->status = $request->status ? 1 : 0;
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



    public function bulkDelete()
    {
        if ($this->request->has('checked')) {
            BulkDeleteDomainSettings::dispatch($this->request->checked, Auth::id());
            $data['message']  = langapp('deleted_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    }

    public function test() {
        $model = $this->domain->query();
        // dd($model);
        // return $modal;
       dd(DataTables::eloquent($model)->make(true));
    }

    public function tableData()
    {
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        $model = $this->domain->query();
        // var_dump($model);
        // exit();
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
                    return '<label><input type="checkbox" name="checked" value="' . $domain->id . '"><span class="label-text"></span></label>';
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
                    return $domain->domain;
                }
            )
            ->editColumn(
                'status',
                function ($domain) {
                    if($domain->status == '1') {
                        $checked_val = 'checked';
                    } else {
                        $checked_val = '';
                    }
                    $html = '';
                    
                    // $html = '';
                    $html .= '<label class="switch">
                                <input type="checkbox" id="domain_active_'.$domain->code.'" onchange="change_domain_active(\''. $domain->code .'\')" '.$checked_val.' name="active" value="1">
                                <span></span>
                              </label>';

                    return $html;
                }
            )
            ->editColumn(
                'action',
                function ($domain) {
                    $html = '';
                    $html .= "<!--<a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                    @icon('solid/shield-alt')
                                </a>-->
                                
                                <a href='". route('domainsettings.edit', ['id' => $domain->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>
                                <a href='". route('domainsettings.delete', ['id' => $domain->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                </a>";
                    return $html;
                }
            )
            ->rawColumns(['no', 'chk', 'name', 'domain', 'status', 'action'])
            ->make(true);
    }


    public function edit(Domain $id)
    {
        $data['domain'] = $id;
        // dd($id);
        return view('sitesettings::modal.update_domain')->with($data);
    }

    public function delete(Domain $id)//del_domain
    {
        $data['domain'] = $id;
        return view('sitesettings::modal.delete_domain')->with($data);
    }
    
}
