<?php

namespace Modules\Assets\Http\Controllers;

use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
        return view('assets::modal.add_cpe');
    }

}
