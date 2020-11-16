<?php

namespace Modules\sitesettings\Http\Controllers;

use Auth;
use DataTables;
use Modules\Users\Entities\User;
use Modules\SiteSettings\Http\Requests\UserRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;

class UsersSettingsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    protected $user;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings, User $user)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
        $this->user = $user;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function users_settings($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Users Settings';
        return view('sitesettings::users')->with($data);
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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


    public function create(Request $request)
    {
        $code = $request->code;
        return view('sitesettings::modal.create_user',compact('code'));
    }

    public function save(UserRequest $request)//UserRequest
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

        // $User = $this->domain;
        $User = new User;
        $User->code = generator_uuid();
        $User->username = $request->username;
        $User->email = $request->username;
        $User->name = $request->name;
        $User->password = $request->password;
        // $User->created_by = @Auth::user()->id;
        $User->active = $request->active ? 1 : 0;
        $User->site_id = $SiteSettings->id;
        $User->save();

        // foreach($request->category AS $cate) {
        //     $SiteCategory = new SiteCategory;
        //     $SiteCategory->site_id = $User->id;
        //     $SiteCategory->category_id = $cate;
        //     $SiteCategory->save();
        // }

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $User);
        // }

   
        return ajaxResponse(
            [
                'id'       => $User->id,
                'message'  => langapp('saved_successfully'),
                'redirect' =>route('userssettings.index', ['id' => $SiteSettings->code]),
            ],
            true,
            Response::HTTP_CREATED
        );
    }


    public function update(UserRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        // $user = $this->user->findOrFail($id);
        // $user = $this->user->where('code',$id)->first();
        $user = User::where('code',$id)->first();
        // dd($user);
        // exit();
        // $user->update($request->all());
        $user->name = trim($request->name);
        $user->username = trim($request->username);
        // $user->open_scan = $request->open_scan;
        // $user->scan_interval = $request->scan_interval;
        $user->active = $request->active ? 1 : 0;
        $user->save();

        $site_code = $this->siteSettings->find_code($user->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $user);
        // }
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('userssettings.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_process($id = null)
    {
        // $data['user'] = User::where('code', $id)->first();
        // $model = $this->user->find($id);
        $model = $this->user->where('code',$id)->first();
        // dd($model);
        $model->delete();

        $site_code = $this->siteSettings->find_code($model->site_id);

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('userssettings.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }


    public function change_status(Request $request)
    {
        $user = User::where('code', $request->code)->first();
        $user->active = $request->active;
        $user->save();

        $site_code = $this->siteSettings->find_code($user->site_id);

        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('userssettings.index',['id' => $site_code->code]),
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
        $current_uri = request()->segments();
        dd($current_uri[2]);
        // $model = $this->user->query();
        // dd($model);
        // return $modal;
    //    dd(DataTables::eloquent($model)->make(true));
    }

    public function tableData()
    {
        $current_uri = request()->segments();
        // $site_code = $current_uri[2];
        $site_code = $this->request->site_code;
        // $site_code = 'a7b6ff37-30ec-4494-9527-93b0ccc51d56';
        $site_id_find = $this->siteSettings->find_id($site_code);
        $site_id = $site_id_find->id;

        // $site_code = $this->request->site_code;
        // $site_id = $this->request->site_id;
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        $model = $this->user->query();
        $test = 1;
        $model->when(
            $test == 1,
            function ($q) use ($site_id) {
                return $q->where('site_id','=', $site_id);
            }
        );
        // $model = $this->user->where('site_id','43')->query();
        // $model = User::all()->toArray();
        // var_dump($model);
        // exit();
        return DataTables::eloquent($model)
            ->editColumn(
                'no',
                function ($user) {
                    return $user->id;
                }
            )
            ->editColumn(
                'chk',
                function ($user) {
                    return '<label><input type="checkbox" name="checked" value="' . $user->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'name',
                function ($user) {
                    return $user->name;
                }
            )
            ->editColumn(
                'email',
                function ($user) {
                    return $user->email;
                }
            )
            ->editColumn(
                'confirm',
                function ($user) {
                    $html = '';
                    return $html;
                }
            )
            ->editColumn(
                'role',
                function ($user) {
                    return $user->user;
                }
            )
            ->editColumn(
                'status',
                function ($user) {
                    if($user->active == '1') {
                        $checked_val = 'checked';
                    } else {
                        $checked_val = '';
                    }
                    $html = '';
                    
                    // $html = '';
                    $html .= '<label class="switch">
                                <input type="checkbox" id="user_active_'.$user->code.'" onchange="change_user_active(\''. $user->code .'\')" '.$checked_val.' name="active" value="1">
                                <span></span>
                              </label>';

                    return $html;
                }
            )
            ->editColumn(
                'lastupdate',
                function ($user) {
                    $html = '';
                    return $html;
                }
            )
            ->editColumn(
                'action',
                function ($user) {
                    $html = '';
                    $html .= "<!--<a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                    @icon('solid/shield-alt')
                                </a>-->
                                
                                <a href='". route('user.edit', ['id' => $user->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>
                                <a href='". route('user.delete2', ['id' => $user->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                </a>";
                    return $html;
                }
            )
            ->rawColumns(['no', 'chk', 'name', 'email', 'role', 'status', 'action'])
            ->make(true);
    }


    public function edit(Request $request, $id)
    {
        $data['user'] = User::where('code', $id)->first();
        // dd($id);
        return view('sitesettings::modal.update_user')->with($data);
    }

    public function delete(Request $request, $id)//del_domain
    {
        $data['user'] = User::where('code', $id)->first();
        // $data['user'] = $id;
        return view('sitesettings::modal.delete_user')->with($data);
    }
}
