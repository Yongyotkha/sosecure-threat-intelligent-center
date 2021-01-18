<?php

namespace Modules\SiteSettings\Http\Controllers;

use Modules\Keywords\Http\Requests\KeywordsRequest;
use Auth;
use Modules\SiteSettings\Entities\Site_keywords;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;

class KeywordSettingController extends Controller
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
    public function index($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'KeywordSettings';
       return view('sitesettings::keyword_setting')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        $code = $request->code;
        return view('sitesettings::modal.create_keyword',compact('code'));
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
    public function update(KeywordsRequest $request, $id = null)
    {
        
        // dd($request);
        // exit();
        // $domain = $this->domain->findOrFail($id);
        $Site_keywords = Site_keywords::findOrFail($id);
        // $domain->update($request->all());
        $Site_keywords->name = $request->name;
        $Site_keywords->type = $request->type;
        $Site_keywords->status = $request->status ? 1 : 0;
        $Site_keywords->save();

        $site_code = $this->siteSettings->find_code($Site_keywords->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $domain);
        // }
        return ajaxResponse(
            [
                'id'       => $Site_keywords->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('keyword.index',['id' => $site_code->code]),
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

    public function save(KeywordsRequest $request)//DomainRequest
    {

     
        
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
        //  dd($segment3);
        $code = $request->code;

        $SiteSettings = SiteSettings::where('code',$code)->first();


        foreach ($request->type as $key) {
            $Site_keywords = new Site_keywords;
            $Site_keywords->code = generator_uuid();
            $Site_keywords->site_id = $SiteSettings->id;
            $Site_keywords->name = $request->name;
            $Site_keywords->type = $key;
            $Site_keywords->status = $request->status ? 1 : 0;
            $Site_keywords->created_by = @Auth::user()->id;
            $Site_keywords->save();
        }
        


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
                'id'       => $Site_keywords->id,
                'message'  => langapp('saved_successfully'),
                'redirect' =>route('keyword.index', ['id' => $SiteSettings->code]),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    public function change_status(Request $request)
    {
        $Site_keywords = Site_keywords::where('code', $request->code)->first();
        $Site_keywords->status = $request->active;
        $Site_keywords->save();

        $site_code = $this->siteSettings->find_code($Site_keywords->site_id);

        return ajaxResponse(
            [
                'id'       => $Site_keywords->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('keyword.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
