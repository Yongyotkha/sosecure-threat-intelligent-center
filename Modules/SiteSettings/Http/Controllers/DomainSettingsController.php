<?php

namespace Modules\sitesettings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Http\Requests\DomainRequest;

class DomainSettingsController extends Controller
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
}
