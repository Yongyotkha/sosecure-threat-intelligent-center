<?php

namespace Modules\WebDefacement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\SiteSettings\Entities\SiteSettings;

class WebDefacementController extends Controller
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
        $data['page'] = langapp('webdefacement');
        $data['SiteSettings'] = SiteSettings::where("active",1)->where("deleted_at",null)->get();

        return view('webdefacement::index')->with($data);
    }

    public function detail($code)
    {

        // dd($code);
        $data['page'] = langapp('webdefacement');
        $data['code'] = @$code;
        $data['webdefacement'] = WebdefacmentSetting::where("code",$code)->first();
        $data['webdefacment_data_original']=@$data['webdefacement']->get_webdefacment_data_original[0];
        $data['webdefacment_data_check']=@$data['webdefacement']->get_webdefacment_data_check[0];
        $data['webdefacment_data_log']=@$data['webdefacement']->get_webdefacment_data_log[0];

        //   dd( $data['webdefacment_data_check']);
        



        
        return view('webdefacement::detail')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('webdefacement::create');
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
        return view('webdefacement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('webdefacement::edit');
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

    public function load_card(Request $request)
    {
        $html = '';
     

        $modal = WebdefacmentSetting::where("active", '=', 1)->where("deleted_at",null);

        if ($request->search_ == 1) {
    
            if ($request->site != "") {
                $modal = $modal->where('site_id', '=', $request->site);
                
            }

            if ($request->keywords) {
                $modal = $modal->where('name', 'LIKE', '%' . $request->keywords . '%')
                ->orWhere('url', 'LIKE', '%' . $request->keywords . '%');
            }

            if ($request->datatype) {
                // dd($request->datatype);
                
                $modal = $modal->whereIn('status_val', $request->datatype);
                // dd($modal);
            }
            
            
        }

        if($request->level){
            if($request->level =='critical'){
                $modal = $modal->where('status_val', 'critical');
            }
            else if($request->level =='high'){
                $modal = $modal->where('status_val', 'high');
            }
            else if($request->level =='medium'){
                $modal = $modal->where('status_val', 'medium');
            }
            else if($request->level =='normal'){
                $modal = $modal->where('status_val', 'normal');
            }
            else if($request->level =='none'){
                $modal = $modal->where('status_val', 'none');
            }
            // else{
            //     $modal = $modal;
            // }
        
    }
            
        $modal = $modal->get();


        foreach ($modal as $key) {
            $html .= 
            '<div class="item-wdfm wdfm-inner">
                <div class="wdfm-card">
                    <div class="wdfm-header">
                        <div class="wdfm-img">
                            <a href="'.route('webdefacement.detail',['code'=>$key->code]).'">
                                <img src="'.asset($key->image_last).'" alt="">
                            </a>
                        </div>
                    </div>
                    <div class="wdfm-body">
                        <div class="wdfm-btn">
                            <a href="'.route('webdefacement.detail',['code'=>$key->code]).'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        <h4>'.$key->name.'</h4>
                        <p class="mdfm-text-muted">'.$key->url.'</p>
                    </div>
                    <div class="wdfm-footer">
                        <div class="wdfm-ft-left flex">
                            <div>Site : '.$key->get_site->name.'</div>
                            <div class="status-flex mr-2">Status : &nbsp; '.get_webdefacment_status($key->status_val,'color').'</div>
                        </div>
                        <div class="wdfm-ft-right flex">
                            <div>Last online: '.$key->last_online.'</div>
                            <div>Last Check: '.$key->last_check.'</div>
                        </div>
                    </div>
                </div>
            </div>';                    
        }

        if ($request->ajax()) {
            $data = [
                "html" => $html,
            ];
            return response()->json($data);
        }
       
    }
}
