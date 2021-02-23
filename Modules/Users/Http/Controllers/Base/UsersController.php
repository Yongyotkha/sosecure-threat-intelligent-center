<?php

namespace Modules\Users\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Modules\Users\Exports\UsersExport;
use Modules\Users\Jobs\BulkDeleteUsers;
use Modules\Users\Jobs\GDPRExportData;
use Modules\Users\Entities\model_has_roles;
use App\Entities\Roles;

abstract class UsersController extends Controller
{
    /**
     * Request instance
     *
     * @var Request
     */
    protected $request;
    /**
     * User Model
     *
     * @var User
     */
    protected $user;
    /**
     * Create a new controller instance.
     */
    public function __construct(Request $request, User $user)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function test()
    {
        dd(123);
        return view('users::index')->with($data);
    }
    public function index()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $SiteSettings = '';
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];

        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }

        $data['SiteSettings'] = $SiteSettings;
        $data['filter'] = $this->request->filter;
        $data['page'] = $this->getPage();

        return view('users::index')->with($data);
    }

    public function create()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $SiteSettings = '';
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];

        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }

        $data['SiteSettings'] = @$SiteSettings;

        return view('users::modal.create')->with($data);
    }

    public function edit_new_modal(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $SiteSettings = '';
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @UserSite::select('site_id')->where('user_id', $user->id)->get();

        $data['SiteSettings'] = @$SiteSettings;
        $data['site_id_arr'] = array_column(@$site_id_arr->toArray(), 'site_id');
        $data['user'] = $user;
        $data['role_id'] = @model_has_roles::where('model_id',$user->id)->first()->role_id;
        return view('users::modal.update_user_new')->with($data);
    }

    public function delete_new_modal(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $data['user'] = $user;
        return view('users::modal.delete_user_new')->with($data);
    }

    public function edit(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $data['user'] = $user;
        return view('users::modal.update')->with($data);
    }

    public function suspend(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        if (can('users_delete')) {
            $data['user'] = $user;
            return view('users::modal.suspend')->with($data);
        }
    }

    public function impersonate($id)
    {
        $user = User::findOrFail($id);
        if (!$user->hasRole('admin')) {
            Auth::user()->setImpersonating($user->id);
        } else {
            toastr()->warning('Impersonate disabled for this user', langapp('response_status'));
        }

        return redirect()->back();
    }

    public function stopImpersonate()
    {
        Auth::user()->stopImpersonating();
        toastr()->success('Welcome Back', langapp('response_status'));

        return redirect()->back();
    }

    public function regenerateKey()
    {
        Auth::user()->update(['calendar_token' => str_random(60)]);
        toastr()->success('Calendar Token regenerated', langapp('response_status'));

        return redirect()->back();
    }

    public function gdprExport()
    {
        GDPRExportData::dispatch(Auth::user());
        toastr()->info('We will send you an email when your data is available', langapp('response_status'));

        return redirect(url()->previous());
    }
    /**
     * Export users as CSV
     */
    public function export()
    {
        if (isAdmin()) {
            return (new UsersExport)->download('users_' . now()->toIso8601String() . '.csv');
        }
        abort(404);
    }

    public function permissions(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $data['user'] = $user;

        return view('users::modal.permissions')->with($data);
    }
    /**
     * Change user permissions
     *
     * @param  Request $request
     * @param  User    $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePermission(Request $request, User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $request->validate(['user_id' => 'required']);
        $permissions = [];
        if ($request->has('perm')) {
            foreach ($request->perm as $key => $value) {
                $permissions[] = $key;
            }
            $user->syncPermissions($permissions);
        }
        $data['message'] = langapp('changes_saved_successful');
        $data['redirect'] = url()->previous();

        return ajaxResponse($data);
    }

    public function delete(User $user)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $data['user'] = $user;
        return view('users::modal.delete')->with($data);
    }

    public function bulkDelete()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        if ($this->request->has('checked')) {
            BulkDeleteUsers::dispatch($this->request->checked, Auth::id());
            $data['message'] = langapp('deleted_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No users selected', 'errors' => ['missing' => ["Please select atleast 1 user"]]], 500);
    }

    public function pin($entity = null, $module = null)
    {
        classByName($module)->findOrFail($entity)->addSidebar();

        toastr()->info(langapp('action_completed'), langapp('response_status'));

        \Cache::forget('quick-access-' . Auth::id());

        return redirect(url()->previous());
    }

    public function holiday($status = null)
    {
        if ($status == 'enable') {
            Auth::user()->update(['on_holiday' => 1]);
            toastr()->warning(langapp('holiday_mode_enabled'), langapp('response_status'));
        } else {
            Auth::user()->update(['on_holiday' => 0]);
            toastr()->info(langapp('holiday_mode_disabled'), langapp('response_status'));
        }
        return redirect(url()->previous());
    }

    /**
     * Show user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function view(User $user, $tab = 'overview')
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        $allowed = ['deals', 'files', 'overview', 'projects', 'tickets', 'timesheet'];
        $tab = in_array($tab, $allowed) ? $tab : 'overview';
        $data['user'] = $user;
        $data['page'] = $this->getPage();
        $data['tab'] = $tab;

        return view('users::view')->with($data);
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableData(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);

        // if($request->site){

        //             $model->whereHas('gey_UserSite', function ($query,$request) {
        //                 $query->where('site_id', $request->site);
        //             })
        //     );
        //     $model = $model->query();

        // }
        $Roles = Roles::get()->keyBy('id')->toArray();
        if(!empty(get_role_custom()))
            if(get_role_custom()['superadmin'] == 1){
                $model = User::select('users.code','users.id','email','users.created_at','name','site_role_id','model_has_roles.role_id AS model_role_id')->where('active', '1')->whereNull('deleted_at')->with('profile')->with('get_UserSite');
            }else if(get_role_custom()['site_admin'] == 1){
                
                $model = User::select('users.id','email','users.created_at','name','site_role_id','model_has_roles.role_id AS model_role_id')->where('active', '1')->whereNull('deleted_at')->with('profile')->with('get_UserSite');
                $model2 = UserSite::select('site_id')->where('user_id',@Auth::user()->id)->get()->toArray();
                
                $model = $model->whereHas('get_UserSite', function ($query) use ($model2) {
                    $query->whereIn('site_id',@$model2);
                });

            }else{

                $model = User::select('code','id','email','created_at','name','site_role_id')->where('active', '1')->where('id', @Auth::user()->id)->whereNull('deleted_at')->with('profile')->with('get_UserSite');
            }
        

        if ($request->role) {
            $model = $model->where('site_role_id', $request->role);

        }

        if ($request->site) {

            $model = $model->whereHas('get_UserSite', function ($query) use($request) {
                $query->where('site_id', $request->site);
            });
        }
        // $model = $model->first();
        // dd([0]->get_site->name);
        $model = $model->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id');
        $model->get();
        
        return DataTables::of($model)
            ->editColumn(
                'no',
                function ($model) {
                    return $model->id;
                }
            )
            ->editColumn(
                'name',
                function ($model) {
                    // return '<a href="' . route('users.view', $model->id) . '"><span class="thumb-xs avatar lobilist-check"><img src="' . @$model->profile->photo . '" class="img-circle"></span> ' . str_limit($model->name, 15) . '</a>';
                    return '<span class="thumb-xs avatar lobilist-check"><img src="' . @$model->profile->photo . '" class="img-circle"></span> ' . str_limit($model->name, 15);
                }
            )
            ->editColumn(
                'chk',
                function ($model) {
                    if (@$model->id == 1) {
                        return '<label><input type="checkbox" disabled  name="checked[]" class="user_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                    } else {
                        return '<label><input type="checkbox"   name="checked[]" class="user_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                    }

                }
            )
            ->editColumn(
                'job_title',
                function ($model) {
                    $str = $model->on_holiday ? '<i class="fas fa-plane-departure text-danger"></i> ' : '';
                    return $str .= str_limit(@$model->profile->job_title, 15);
                }
            )
            ->editColumn(
                'site_name',
                function ($model) {
                    $html = '';
                    if($model->get_UserSite){

                        foreach ($model->get_UserSite as $key) {
                            if($key->get_site){
                                $html .= $key->get_site->name.',';
                            }
                            
                            // $site = $key->get_site->name;
                            // $html.= '"'.$site.'"';
                            # code...
                        }
                    }
                    $html=rtrim($html,",");
                    
                    return $html;
                }
            )
            ->editColumn(
                'mobile',
                function ($model) {
                    return @$model->profile->mobile;
                }
            )
            ->editColumn(
                'city',
                function ($model) {
                    return @$model->profile->city;
                }
            )
            ->editColumn(
                'created_at',
                function ($model) {
                    return dateFormatted($model->created_at);
                }
            )->editColumn('action', function ( $model) {

                $html='';
                if(@$model->get_model_has_roles->role_id == 6){
                    $html .= "<a href='". route('user.edit_gen_pass', ['id' => $model->code])."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                    <span>Password</span>
                    </a>";
                }else{
                    if($model->id == 1){

                    } else {
                        $html .= '<span><a href="' . route("users.edit_new_modal", ["user" => $model->id]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                        <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                        </a></span>';
                        $html .= '<span><a href="'.route("users.delete_new_modal", ["user" => $model->id]).'" class="btn btn-xs btn-danger" data-toggle="ajaxModal"><i class="fas fa-trash"></i></a></span>';
                    }
                   
                }            
                return $html;
                
            })->addColumn('rolename', function ($model) use ($Roles){
                //humanize(--str--)
                return @$Roles[@$model->model_role_id]["name"];
            })
            ->rawColumns(['name', 'chk', 'job_title', 'role', 'user','action'])
            ->make(true);

            //<a href="'.route("assets.assets_delete_cpe", ["cpecode" => $CPE_Datavalue->code,"menu" => $menu]).'" class="btn btn-xs btn-danger" style="display:inline; font-size: 11px;" data-toggle="ajaxModal"><i class="fas fa-trash"></i></a>
            // <a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
            // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
            // </a

    }

    protected function applyFilter()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }
        if ($this->request->filled('filter')) {
            return $this->user->role($this->request->filter);
        }
        return $this->user->query();
    }

    private function getPage()
    {
        return langapp('users');
    }

    public function del_user(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['manage_users']) {
            check_permission403();
        }

        foreach ($request->id as $id_chang) {

            $user = User::where("id", '=', $id_chang)->first();


            
            if (@$user->get_model_has_roles->role_id != 1 && @$user->get_model_has_roles->role_id != 6) {
                $data = User::where("id", $id_chang)->delete();
                $message = langapp('changes_saved_successful');
            } else {
                $message = '';
            }

        }

        return ajaxResponse(
            [
                'message' => $message,
                'redirect' => route('users.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
