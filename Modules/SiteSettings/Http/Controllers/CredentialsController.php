<?php

namespace Modules\SiteSettings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Modules\SiteSettings\Entities\SiteSettings;
use App\Credentials;


class CredentialsController extends Controller
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
       $data['page'] = 'Credentials';
       return view('sitesettings::credentials')->with($data);
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

    public function create_credentials(Request $request)
    {
        $data_search = Credentials::where("name", $request->name)->first();


        if(!$data_search){
            $data = new Credentials;
            $data->code = generator_uuid();
            $data->site_id = $request->site;
            $data->name = $request->name;
            $data->user = $request->user;
            $data->password = $request->password;
            $data->status = $request->check;
            $data->save();
            $message = langapp('changes_saved_successful');
        }else{
            $message = '';
        }
        

        return ajaxResponse(
            [
                'message' => $message,
                'redirect' => route('credentials.index', ['code' => $request->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function table_credentials(Request $request)
    {
        $model = Credentials::where('site_id',$request->site)->with('get_compromised_server');
        $model->get();



        return DataTables::of($model)->toJson();
    }

    public function credentials_change_status(Request $request)
    {
        // dd($request->active);


        $DataLeakFeed = Credentials::where('id', $request->id)->first();
        $DataLeakFeed->status = $request->active;
        $DataLeakFeed->save();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                // 'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function credentials_delete(Request $request)
    {

        // dd($request->id);
        if($request->id_change){
            
            foreach($request->id_change as $id_change ){

                $data = Credentials::where("id", $id_change )->delete();

            }
        }else{

            Credentials::where("id", $request->id)->delete();

        }



        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
  
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function credentials_edit_modal($code)
    {
        $data['Credentials']=Credentials::where("code", $code)->first();
        $data['code']=$code;
        return view('sitesettings::modal.update_credentials')->with($data);
    }

    public function credentials_edit(Request $request)
    {
        
        $data = Credentials::where("code", $request->code)->first();
        $data->name = $request->name;
        $data->user = $request->user;
        $data->password = $request->password;
        if($request->check){
            $data->status = $request->check;
        }
        $data->save();

        $code_site = SiteSettings::where("id", '=', $data->site_id)->first();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('credentials.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );

    }
    
}
