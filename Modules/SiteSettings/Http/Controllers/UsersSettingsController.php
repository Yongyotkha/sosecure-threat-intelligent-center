<?php

namespace Modules\sitesettings\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Auth;
use DataTables;
use App\Roles;
use App\transaction_client_model_has_roles;
use App\transaction_client_profiles;
use App\transaction_client_role_permissions;
use App\transaction_client_user_site;
use App\transaction_client_users;
use Modules\Users\Entities\Profile;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Modules\Users\Entities\model_has_roles;
use Modules\SiteSettings\Http\Requests\UserRequest;

use Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Emails\SiteCreateUserMail;

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
    protected $summary;

    public function __construct(Request $request, SiteSettings $siteSettings, User $user)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
        $this->user = $user;
        $this->summary = [];
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function users_settings($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Users';
        $data['code'] = $id;
        

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
        $SiteSettings = SiteSettings::where('code',$request->code)->first();
        if($SiteSettings) {
            if($SiteSettings->role_allow_admin == 'Y') {
                $Roles = Roles::whereIn('id',['4','5'])->get();
            } else {
                $Roles = Roles::whereIn('id',['5'])->get();
            }
        }

        $data['Roles'] = $Roles;
        $data['code'] = $request->code;

        return view('sitesettings::modal.create_user')->with($data);
    }

    public function save(UserRequest $request)//UserRequest
    {
        // dd($request);
        // $this->authorize('create', Domain::class);
        // $Domain = $this->Domain->create($request->all());
        // $segments = request()->segments();
        // $last_segments  = end($segments);
        // $segment3 =  request()->segment(3);
        // dd($segment3);
        $code = $request->code;

        $SiteSettings = SiteSettings::where('code',$code)->first();

        $User_check_limit = User::where('site_role_id','!=',99)->where('site_role_id','!=',6)->where('deleted_at',null);
   
        $User_check_limit->whereHas('get_user_site_many', function($q) use ($SiteSettings) {
            $q->where('site_id', $SiteSettings->id);
        });
        $User_check_limit = $User_check_limit->get()->count();
        // dd($User_check_limit);
        // dd($User_check_limit->get()->count());

        $user_allow = $SiteSettings->user_allow;
        $user_limit_amount = $SiteSettings->user_limit_amount;

        if($SiteSettings) {

            if($user_allow == 'Y') {//allow
                if($User_check_limit >= $user_limit_amount) {//limit
                    return response()->json(['message' => 'Failure, user exceeded limit!', 'errors' => ['missing' => ["Failure, user exceeded limit! "]]], 500);
                } else {//limit pass

                    $email = $request->email;
                    if($email) {
                        $User_check_email = User::where('email',$email)->where('deleted_at',null)->get()->count();
                        if($User_check_email > 0) {
                            return response()->json(['message' => 'this email address already exist', 'errors' => ['missing' => ["this email address already exist "]]], 500);
                        }
                    }


                    $role_id = $request->role_id;

            
            
            
                    // return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
            
                    // $User = $this->domain;
            
                    $password = $request->password;
                    // dd($password);
                    // exit();
                    $User = new User;
                    $User->code = generator_uuid();
                    $User->username = $request->email;
                    $User->email = $request->email;
                    $User->name = $request->name;
                    $User->site_role_id = $request->role_id;
                    // $User->created_by = @Auth::user()->id;
                    $User->active = $request->active ? 1 : 0;
                    $User->site_id = $SiteSettings->id;
                    $User->site_add_user_token = generator_uuid();
                    $User->save();

                    $transaction_client_users = new transaction_client_users();
                    $transaction_client_users -> site_id = $SiteSettings->id;
                    $transaction_client_users -> transaction_id = $User->id;
                    $transaction_client_users -> transaction_mode = 'insert';
                    $transaction_client_users -> transaction_data_status = 1;
                    $transaction_client_users -> status = 1;
                    $transaction_client_users -> save();

                    $UserSite = new UserSite;
                    $UserSite->user_id = $User->id;
                    $UserSite->site_id = $SiteSettings->id;
                    $UserSite->created_by = @Auth::user()->id;
                    $UserSite->save();

                    $transaction_client_user_site = new transaction_client_user_site();
                    $transaction_client_user_site -> site_id = $SiteSettings->id;
                    $transaction_client_user_site -> transaction_id = $UserSite->id;
                    $transaction_client_user_site -> transaction_mode = 'insert';
                    $transaction_client_user_site -> transaction_data_status = 1;
                    $transaction_client_user_site -> status = 1;
                    $transaction_client_user_site -> save();

                    $transaction_client_profiles = new transaction_client_profiles();
                    $transaction_client_profiles -> site_id = $SiteSettings->id;
                    $transaction_client_profiles -> transaction_id = $User->id;
                    $transaction_client_profiles -> transaction_mode = 'insert';
                    $transaction_client_profiles -> transaction_data_status = 1;
                    $transaction_client_profiles -> status = 1;
                    $transaction_client_profiles -> save();
            
                    if($role_id) {
                        $model_has_roles = model_has_roles::where('model_id',$User->id)->first();
                        if($model_has_roles) {
                            $model_has_roles->role_id = $role_id;
                            $model_has_roles->model_type = 'Modules\Users\Entities\User';
                            // $model_has_roles->model_id = $User->id;
                            $model_has_roles->save();
                        } else {
                            $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                            if($model_has_roles_q) {
                                $id_last = $model_has_roles_q->id+1;
                            } else {
                                $id_last = 1;
                            }
                            
                            $model_has_roles = new model_has_roles;
                            $model_has_roles->role_id = $role_id;
                            $model_has_roles->model_type = 'Modules\Users\Entities\User';
                            $model_has_roles->model_id = $User->id;
                            $model_has_roles->id = $id_last;
                            $model_has_roles->save();
                        }

                        // $site_role_id = null;
                        // if($role_id == 1 || $role_id == 4) {
                        //     $role = 'admin';
                        //     // $site_role_id = 99;
                        // } else {
                        //     $role = 'admin';//client_site
                        //     // $site_role_id = $role_id;
                        // } 

                        // $User->syncRoles($role);

                        $transaction_client_model_has_roles = new transaction_client_model_has_roles();
                        $transaction_client_model_has_roles -> site_id = $SiteSettings->id;
                        $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
                        $transaction_client_model_has_roles -> transaction_mode = 'insert';
                        $transaction_client_model_has_roles -> transaction_data_status = 1;
                        $transaction_client_model_has_roles -> status = 1;
                        $transaction_client_model_has_roles -> save();
                    }
            
            
                        $this->summary = [
                            'site_add_user_token'   => $User->site_add_user_token,
                            'User'   => $User,
                            // 'invoiced_amount'    => formatCurrency(get_option('default_currency'), $this->invoicedToday()),
                            // 'estimates_accepted' => formatCurrency(get_option('default_currency'), $this->estimatesToday()),
                            // 'hours_worked'       => $this->workedToday(),
                            // 'deals_won'          => Deal::whereDate('won_time', today()->toDateTimeString())->count(),
                            // 'leads_converted'    => Lead::whereDate('converted_at', today()->toDateTimeString())->count(),
                            // 'expenses_total'     => formatCurrency(get_option('default_currency'), $this->expensesToday()),
                            // 'closed_tickets'     => Ticket::whereDate('closed_at', today()->toDateTimeString())->count(),
                            // 'completed_tasks'    => Task::completed()->whereDate('updated_at', today()->toDateTimeString())->count(),
                        ];
                        // \Mail::to(User::role('admin')->get())->send(new DailyDigestMail($this->summary));
                        \Mail::to($User->email)->send(new SiteCreateUserMail($this->summary));
                        // Mail::to($MAIL_TO_sent)->cc($MAIL_RECEIVE_ORDER_TO_ORG_cc_arr)->send(new Send_data_mailto_org_ins($OrderProductCar,$NO_ID,$LISNO,$vw_sys_product_cars,$OrderProductCarInsure,$sys_file,'sent_mailto_org_controller'));
                    
                    // Xrun::dispatch()->onQueue('low')->delay(now()->addMinutes(10));
            
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

            } else {//not allow
                return response()->json(['message' => 'Failed, adding users is not allowed.!', 'errors' => ['missing' => ["Failed, adding users is not allowed.! "]]], 500);
            }

        }

 
    
    }


    public function update(Request $request, $id = null)
    {

        $password = $request->password;
        $password_re = $request->password_re;
        $role_id = $request->role_id;
        $site_role_id = null;
        $site_code = $request->site_code;
        // dd($site_code);

        


        // if($role_id == 1 || $role_id == 4) {
        //     $role = 'admin';
        //     // $site_role_id = 99;
        // } else {
        //     $role = 'admin';//client_site
        //     // $site_role_id = $role_id;
        // } 
        // dd($password);
        // exit();

        $pass = '';
        if($password) {
            if($password_re) {
                if($password == $password_re) {
                    $pass = $password;
                } else {
                    return response()->json(['message' => 'Please make sure your passwords match', 'errors' => ['missing' => ["Please make sure your passwords match"]]], 500);
                }

            }

        }

        // dd($request);
        // exit();
        // $user = $this->user->findOrFail($id);
        // $user = $this->user->where('code',$id)->first();
        $user = User::where('code',$id)->first();
        // dd($user);
        // exit();
        // $user->update($request->all());

        $UserSite = UserSite::where('user_id',$user->id)->get()->pluck('site_id')->toArray();
        // dd($UserSite);


        if($pass) {
            $userColumns = ['username', 'password', 'name', 'active'];
        } else {
            $userColumns = ['username', 'name', 'active'];
        }

        // $userColumns = ['username', 'password', 'name', 'active'];
        $user->update($request->only($userColumns));
        // $user->code = generator_uuid();
        // $user->save();


        // $user->name = trim($request->name);
        // $user->username = trim($request->username);
        if($pass) {
            // $user->password = Hash::make($request->password);
            // $user->password = bcrypt($request->password);
        }
        // $user->open_scan = $request->open_scan;
        // $user->scan_interval = $request->scan_interval;
        $user->active = $request->active ? 1 : 0;
        $user->site_role_id = $role_id;
        $user->save();



        // $user->profile->update($request->all());
        if($role_id) {
            $model_has_roles = model_has_roles::where('model_id',$user->id)->first();
            if($model_has_roles) {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }
                $model_has_roles->role_id = $role_id;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                // $model_has_roles->model_id = $User->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();

                $has_roles_transaction_mode = 'update';
                    // dd($model_has_roles);
            } else {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }
                
                $model_has_roles = new model_has_roles;
                $model_has_roles->role_id = $role_id;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                $model_has_roles->model_id = $user->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();

                $has_roles_transaction_mode = 'insert';
            }

            // $user->syncRoles($role);

            
            // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $user->site_id)->where('transaction_id', $user->id)->first();
            // if($transaction_client_role_permissions){
            //     $transaction_client_role_permissions -> transaction_mode = 'update';
            //     $transaction_client_role_permissions -> transaction_data_status = 1;
            //     $transaction_client_role_permissions -> status = 1;
            //     $transaction_client_role_permissions -> save();
            // }else{
            //     $transaction_client_role_permissions = new transaction_client_role_permissions();
            //     $transaction_client_role_permissions -> site_id = $user->site_id;
            //     $transaction_client_role_permissions -> transaction_id = $user->id;
            //     $transaction_client_role_permissions -> transaction_mode = 'update';
            //     $transaction_client_role_permissions -> transaction_data_status = 1;
            //     $transaction_client_role_permissions -> status = 1;
            //     $transaction_client_role_permissions -> save();
            // }
            
        }




        if($UserSite) {
            foreach($UserSite as $UserSite_val) {
                $transaction_client_users = transaction_client_users::where('site_id', $UserSite_val)->where('transaction_id', $user->id)->first();
                if($transaction_client_users){
                    $transaction_client_users -> transaction_mode = 'update';
                    $transaction_client_users -> transaction_data_status = 1;
                    $transaction_client_users -> status = 1;
                    $transaction_client_users -> save();
                }else{
                    $transaction_client_users = new transaction_client_users();
                    $transaction_client_users -> site_id = $UserSite_val;
                    $transaction_client_users -> transaction_id = $user->id;
                    $transaction_client_users -> transaction_mode = 'update';
                    $transaction_client_users -> transaction_data_status = 1;
                    $transaction_client_users -> status = 1;
                    $transaction_client_users -> save();
                }

                if($role_id) {
                    $transaction_client_model_has_roles = new transaction_client_model_has_roles();
                    $transaction_client_model_has_roles -> site_id = $UserSite_val;
                    $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
                    $transaction_client_model_has_roles -> transaction_mode = $has_roles_transaction_mode;
                    $transaction_client_model_has_roles -> transaction_data_status = 1;
                    $transaction_client_model_has_roles -> status = 1;
                    $transaction_client_model_has_roles -> save();
                }

            }
        }


        



        

        // $site_code = $this->siteSettings->find_code($user->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $user);
        // }
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('userssettings.index',['id' => $site_code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function process_gen_pass(Request $request)
    {
        // dd($request);
        // exit();
        // $user = $this->user->findOrFail($id);
        $user_code = $request->user_code;
        $user = User::where('code',$user_code)->first();
        // dd($user);
        // exit();
        $site_code = $request->site_code;
        // dd($site_code);
        // if($pass) {
        //     $userColumns = ['username', 'password', 'name', 'active'];
        // } else {
        //     $userColumns = ['username', 'name', 'active'];
        // }

        // $userColumns = ['username', 'password', 'name', 'active'];
        $user->update(array(
            // 'name' =>  $request->name,
            // 'email' => $request->email,
            'password' => $request->pass
            ));


        // $user->password = Hash::make(Str::uuid());
        $user->password_time_expire = Carbon::now()->addMinutes(10);
        $user->active = 1;
        $user->save();
        // $user->syncRoles('admin');

        $user_find = User::where('code',$user->code)->first();

        // $site_code = $this->siteSettings->find_code($user->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $user);
        // }
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('userssettings.index',['id' => $site_code]),
                'pass' => $request->pass,
                'time_pass_expire' => $user_find->password_time_expire,
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_process(Request $request,$id = null)
    {
        // $data['user'] = User::where('code', $id)->first();
        // $model = $this->user->find($id);
        $model = $this->user->where('code',$id)->first();
        $site_code = $request->site_code;
        $SiteSettings = SiteSettings::where('code',$site_code)->first();
        // dd($site_code);

        $UserSite = UserSite::where('user_id',$model->id)->get()->count();
        // dd($UserSite);

        if($UserSite > 1) {
            $UserSite_get = UserSite::where('user_id',$model->id)->where('site_id',$SiteSettings->id)->first();
            $UserSite = UserSite::where('user_id',$model->id)->where('site_id',$SiteSettings->id)->delete();

            $transaction_client_user_site = transaction_client_user_site::where('site_id', $SiteSettings->id)->where('transaction_id', $UserSite_get->id)->first();
            if($transaction_client_user_site){
                $transaction_client_user_site -> transaction_mode = 'delete';
                $transaction_client_user_site -> transaction_data_status = 1;
                $transaction_client_user_site -> status = 1;
                $transaction_client_user_site -> save();
            }else{
                $transaction_client_user_site = new transaction_client_user_site();
                $transaction_client_user_site -> site_id = $SiteSettings->id;
                $transaction_client_user_site -> transaction_id = $UserSite_get->id;
                $transaction_client_user_site -> transaction_mode = 'delete';
                $transaction_client_user_site -> transaction_data_status = 1;
                $transaction_client_user_site -> status = 1;
                $transaction_client_user_site -> save();
            }

        } else {

            $transaction_client_users = transaction_client_users::where('site_id', $SiteSettings->id)->where('transaction_id', $model->id)->first();
            if($transaction_client_users){
                $transaction_client_users -> transaction_mode = 'delete';
                $transaction_client_users -> transaction_data_status = 1;
                $transaction_client_users -> status = 1;
                $transaction_client_users -> save();
            }else{
                $transaction_client_users = new transaction_client_users();
                $transaction_client_users -> site_id = $SiteSettings->id;
                $transaction_client_users -> transaction_id = $model->id;
                $transaction_client_users -> transaction_mode = 'delete';
                $transaction_client_users -> transaction_data_status = 1;
                $transaction_client_users -> status = 1;
                $transaction_client_users -> save();
            }

            $UserSite_get = UserSite::where('user_id',$model->id)->where('site_id',$SiteSettings->id)->first();
            if($UserSite_get) {
                $UserSite = UserSite::where('user_id',$model->id)->delete();
            
                $transaction_client_user_site = transaction_client_user_site::where('site_id', $SiteSettings->id)->where('transaction_id', $UserSite_get->id)->first();
                if($transaction_client_user_site){
                    $transaction_client_user_site -> transaction_mode = 'delete';
                    $transaction_client_user_site -> transaction_data_status = 1;
                    $transaction_client_user_site -> status = 1;
                    $transaction_client_user_site -> save();
                }else{
                    $transaction_client_user_site = new transaction_client_user_site();
                    $transaction_client_user_site -> site_id = $SiteSettings->id;
                    $transaction_client_user_site -> transaction_id = $UserSite_get->id;
                    $transaction_client_user_site -> transaction_mode = 'delete';
                    $transaction_client_user_site -> transaction_data_status = 1;
                    $transaction_client_user_site -> status = 1;
                    $transaction_client_user_site -> save();
                }
            }


            $Profile = Profile::where('user_id',$model->id)->first();
            if($Profile) {
                $transaction_client_profiles = transaction_client_profiles::where('site_id', $SiteSettings->id)->where('transaction_id', $Profile->id)->first();
                if($transaction_client_profiles){
                    $transaction_client_profiles -> transaction_mode = 'delete';
                    $transaction_client_profiles -> transaction_data_status = 1;
                    $transaction_client_profiles -> status = 1;
                    $transaction_client_profiles -> save();
            
                }else{
                    $transaction_client_profiles = new transaction_client_profiles();
                    $transaction_client_profiles -> site_id = $SiteSettings->id;
                    $transaction_client_profiles -> transaction_id = $Profile->id;
                    $transaction_client_profiles -> transaction_mode = 'delete';
                    $transaction_client_profiles -> transaction_data_status = 1;
                    $transaction_client_profiles -> status = 1;
                    $transaction_client_profiles -> save();
                }
    
                $Profile->delete();
            }


            $model_has_roles = model_has_roles::where('model_id',$model->id)->first();
            // dd($model_has_roles);
            if($model_has_roles) {
                $transaction_client_model_has_roles = transaction_client_model_has_roles::where('site_id', $SiteSettings->id)->where('transaction_id', $model_has_roles->id)->first();
                if($transaction_client_model_has_roles){
                    $transaction_client_model_has_roles -> transaction_mode = 'delete';
                    $transaction_client_model_has_roles -> transaction_data_status = 1;
                    $transaction_client_model_has_roles -> status = 1;
                    $transaction_client_model_has_roles -> save();
                }else{
                    $transaction_client_model_has_roles = new transaction_client_model_has_roles();
                    $transaction_client_model_has_roles -> site_id = $SiteSettings->id;
                    $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
                    $transaction_client_model_has_roles -> transaction_mode = 'delete';
                    $transaction_client_model_has_roles -> transaction_data_status = 1;
                    $transaction_client_model_has_roles -> status = 1;
                    $transaction_client_model_has_roles -> save();
                }
                $model_has_roles->delete();
            }

            
            $model->delete();
        }



        

        // $site_code = $this->siteSettings->find_code($model->site_id);

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('userssettings.index',['id' => @$site_code]),
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

        $transaction_client_users = transaction_client_users::where('site_id', $user->site_id)->where('transaction_id', $user->id)->first();
        if($transaction_client_users){
            $transaction_client_users -> transaction_mode = 'update';
            $transaction_client_users -> transaction_data_status = 1;
            $transaction_client_users -> status = 1;
            $transaction_client_users -> save();
        }else{
            $transaction_client_users = new transaction_client_users();
            $transaction_client_users -> site_id = $user->site_id;
            $transaction_client_users -> transaction_id = $user->id;
            $transaction_client_users -> transaction_mode = 'update';
            $transaction_client_users -> transaction_data_status = 1;
            $transaction_client_users -> status = 1;
            $transaction_client_users -> save();
        }

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
        $model = User::where('deleted_at', null)->where('active',1)->where('site_id', $site_id)->orderBy('id','ASC');

        $model->whereHas('get_model_has_roles',function($q) {
            $q->whereIn('role_id',[4,5,6]);
        });
      

        $model->get();
        
        // $model = $this->user->query();
        // $test = 1;
        // $model->when(
        //     $test == 1,
        //     function ($q) use ($site_id) {
        //         return $q->where('site_id','=', $site_id);
        //     }
        // );

        // $model = $this->user->where('site_id','43')->query();
        // $model = User::all()->toArray();
        // var_dump($model);
        // exit();
        return DataTables::of($model)
            ->editColumn(
                'no',
                function ($user) {
                    return $user->id;
                }
            )
            ->editColumn(
                'chk',
                function ($user) {
                    if($user->site_role_id == 99) {
                        $disabled = 'disabled';
                    } else {
                        $disabled = '';
                    } 
                    return '<label><input class="user_id" type="checkbox" '.$disabled.' name="checked" value="' . $user->id . '"><span class="label-text"></span></label>';
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
                    $html = "";
                    if($user->verify == 1) {
                        // $html = '<i class="fas fa-check"></i>';
                        $html = '<i class="fas fa-check"></i>';
                    } else {
                        $html = '<i class="fas fa-times"></i>';
                    }
                    return $html;
                }
            )
            ->editColumn(
                'role',
                function ($user) {
                    $site_role_id = $user->site_role_id;
                    if($site_role_id) {
                        if($site_role_id == '99') {
                            $site_role_id_val = 1;
                        } else {
                            $site_role_id_val = $site_role_id;
                        }
                    }

                    $Roles = Roles::where('id', $site_role_id_val)->first();
                    $role_name = $Roles->name;

                    
                    return $role_name;
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

                    if($user->site_role_id == 99) {
                        $disabled = 'disabled';
                        $bg = 'background-color: #54a56291 !important;';
                    } else {
                        $disabled = '';
                        $bg = '';
                    } 
                    $html = '';
                    
                    // $html = '';
                    $html .= '<label class="switch">
                                <input type="checkbox" id="user_active_'.$user->code.'" onchange="change_user_active(\''. $user->code .'\')" '.$checked_val.' name="active" value="1" '.$disabled.'>
                                <span style="'.$bg.'"></span>
                              </label>';

                    return $html;
                }
            )
            ->editColumn(
                'lastupdate',
                function ($user) {
                    $html = '';
                    $html .= $user->updated_at;
                    return $html;
                }
            )
            ->editColumn(
                'action',
                function ($user) use ($site_code) {
                    $html = '';
                    if($user->site_role_id == 99) {
                        $html .= "<a href='". route('user.edit_gen_pass', ['id' => $user->code]) ."?s=".$site_code."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                        <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                        <span>Password</span>
                        </a>";
                    } else {
                        $html .= "<!--<a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                @icon('solid/shield-alt')
                                </a>-->
                                
                                <a href='". route('user.edit', ['id' => $user->code]) ."?s=".$site_code."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>
                                <a href='". route('user.delete2', ['id' => $user->code]) ."?s=".$site_code."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                </a>";
                    }

                    return $html;
                }
            )
            ->rawColumns(['no', 'chk', 'name', 'email', 'confirm', 'role', 'status', 'lastupdate', 'action'])
            ->make(true);
    }


    public function edit(Request $request, $id)
    {
        $site_code = $request->s;//site_code
        // dd($site_code);
        $User = User::where('code', $id)->first();

        $model_has_roles = model_has_roles::where('model_id',$User->id)->first();
        $role_id = $model_has_roles->role_id;

        $Roles = Roles::whereIn('id',[4,5,6])->get();
        $data['site_code'] = $site_code;
        $data['Roles'] = $Roles;
        $data['roles_select'] = $role_id;
        // dd($site_role_id_val);
        $data['user'] = User::where('code', $id)->first();
        // dd($id);
        return view('sitesettings::modal.update_user')->with($data);
    }

    public function edit_gen_pass(Request $request, $id)
    {
        $site_code = $request->s;//site_code
        $data['site_code'] = $site_code;
        $data['user'] = User::where('code', $id)->first();
        // dd($id);
        return view('sitesettings::modal.edit_gen_pass')->with($data);
    }

    public function delete(Request $request, $id)//del_domain
    {
        $site_code = $request->s;//site_code
        $data['site_code'] = $site_code;
        $data['user'] = User::where('code', $id)->first();
        // $data['user'] = $id;
        return view('sitesettings::modal.delete_user')->with($data);
    }

    public function delete_process_change(Request $request)
    {
        
        if($request->id_change){
            foreach($request->id_change as $id_change){
                // $model = $this->user->where('id',$id_change)->first();
    
                
                //     $transaction_client_users = new transaction_client_users();
                //     $transaction_client_users -> site_id = $model->site_id;
                //     $transaction_client_users -> transaction_id = $model->id;
                //     $transaction_client_users -> transaction_mode = 'delete';
                //     $transaction_client_users -> transaction_data_status = 1;
                //     $transaction_client_users -> status = 1;
                //     $transaction_client_users -> save();
                
                
                //     $transaction_client_user_site = new transaction_client_user_site();
                //     $transaction_client_user_site -> site_id = $model->site_id;
                //     $transaction_client_user_site -> transaction_id = $user_site->id;
                //     $transaction_client_user_site -> transaction_mode = 'delete';
                //     $transaction_client_user_site -> transaction_data_status = 1;
                //     $transaction_client_user_site -> status = 1;
                //     $transaction_client_user_site -> save();
                
                
                //     $transaction_client_profiles = new transaction_client_profiles();
                //     $transaction_client_profiles -> site_id = $model->site_id;
                //     $transaction_client_profiles -> transaction_id = $model->id;
                //     $transaction_client_profiles -> transaction_mode = 'delete';
                //     $transaction_client_profiles -> transaction_data_status = 1;
                //     $transaction_client_profiles -> status = 1;
                //     $transaction_client_profiles -> save();

                //     $transaction_client_model_has_roles = new transaction_client_model_has_roles();
                //     $transaction_client_model_has_roles -> site_id = $model->site_id;
                //     $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
                //     $transaction_client_model_has_roles -> transaction_mode = 'delete';
                //     $transaction_client_model_has_roles -> transaction_data_status = 1;
                //     $transaction_client_model_has_roles -> status = 1;
                //     $transaction_client_model_has_roles -> save();
                

                // $model->delete();
                
            }
        }
        


        

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('userssettings.index',['id' => $request->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }
}



