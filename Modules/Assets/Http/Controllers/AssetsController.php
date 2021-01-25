<?php

namespace Modules\Assets\Http\Controllers;

use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;

use App\Credentials;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Assets\Entities\CPEData;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Assets\Entities\OSType;


class AssetsController extends Controller
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

        if(Auth::check()) {

            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(Auth::user()->hasRole('admin')) {//if admin
                // dd(777);
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

            } else { //if notAdmin
                // dd(888);
                if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                    if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                        // dd(99);

                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
             

                    } else {//not support and admin
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }
                }
            }
        }
        
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('assets');
        return view('assets::index')->with($data);
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('assets::create');
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
        return view('assets::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('assets::edit');
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

    public function assets_add_cpe()
    {

        $data['cpe'] = CPEData::where('status', 1)->get();
        $data['Credentials'] = Credentials::where('status', 1)->get();
        $data['SiteSettings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();
        $data['os'] = OSType::get();

        return view('assets::modal.add_cpe')->with($data);
    }

    public function web_server_add_user(Request $request)
    {

        $data_search = Credentials::where("name", $request->name)->first();
        if (!$data_search) {
            $data = new Credentials;
            $data->code = generator_uuid();
            $data->site_id = $request->site;
            $data->name = $request->name;
            $data->user = $request->user;
            $data->password = $request->password;
            $data->status = 1;
            $data->save();
            $message = langapp('changes_saved_successful');
            return ajaxResponse(
                [
                    'message' => $message,
                    'id' => $data->id,
                    'name' => $data->name,

                ],
                true,
                Response::HTTP_OK
            );
        } else {

            $message = '';
            return ajaxResponse(
                [
                    'message' => $message,
                ],
                true,
                Response::HTTP_OK
            );
        }

    }

}
