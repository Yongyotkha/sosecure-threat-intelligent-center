<?php

namespace Modules\Users\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Users\Entities\User;
use Modules\Users\Http\Requests\UserRequest;
use Modules\Users\Transformers\UserResource;
use Modules\Users\Transformers\UsersResource;
use Modules\Users\Entities\UserSite;
use Modules\Users\Entities\model_has_roles;

use Auth;
use App\Roles;
use App\transaction_client_model_has_roles;
use App\transaction_client_profiles;
use App\transaction_client_role_permissions;
use App\transaction_client_user_site;
use App\transaction_client_users;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Emails\SiteCreateUserMail;

class UsersApiController extends Controller
{
    /**
     * Request instance
     *
     * @var Request
     */
    public $request;
    /**
     * User Model
     *
     * @var User
     */
    public $user;
    protected $summary;

    public function __construct(Request $request)
    {
        $this->middleware('localize');
        $this->request = $request;
        $this->user    = new User;
        $this->summary = [];
    }

    public function index()
    {
        $users = new UsersResource(
            $this->user->with(['profile.business:id,name'])
                ->orderByDesc('id')
                ->paginate(50)
        );
        return response($users, Response::HTTP_OK);
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($id = null)
    {
        $user = $this->user->findOrFail($id);
        return response(new UserResource($user), Response::HTTP_OK);
    }

    public function save(UserRequest $request)
    {
        // $this->checkPassword($request);
        // dd($request->site);

        // $User = new User;
        // $User->code = generator_uuid();
        // $User->username = $request->email;
        // $User->email = $request->email;
        // $User->name = $request->name;
        // $User->site_role_id = $request->role_id;
        // // $User->created_by = @Auth::user()->id;
        // $User->active = $request->active ? 1 : 0;
        // // $User->site_id = $SiteSettings->id;
        // $User->site_add_user_token = generator_uuid();
        // $User->save();

        $email = $request->email;
        if($email) {
            $User_check_email = User::where('email',$email)->where('deleted_at',null)->get()->count();
            if($User_check_email > 0) {
                return response()->json(['message' => 'this email address already exist', 'errors' => ['missing' => ["this email address already exist "]]], 500);
            }
        }



        $userColumns = ['email', 'name'];
        // $userColumns = ['username', 'email', 'password', 'name', 'locale'];
        $user        = $this->user->create($request->only($userColumns));
        $user->code = generator_uuid();
        $user->username = $request->email;
        $user->site_role_id = $request->role_id;
        $user->active = $request->active ? 1 : 0;
        $user->site_add_user_token = generator_uuid();
        $user->save();
        $user->profile->update($request->except(['email', 'roles', 'name', 'site', 'active']));


        if($request->role_id) {
            $model_has_roles = model_has_roles::where('model_id',$user->id)->first();
            if($model_has_roles) {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }
                $model_has_roles->role_id = $request->role_id;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                // $model_has_roles->model_id = $User->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();
            } else {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }
                
                $model_has_roles = new model_has_roles;
                $model_has_roles->role_id = $request->role_id;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                $model_has_roles->model_id = $user->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();
            } 
        }


        $site_value = [];
            if(!empty($request->site_multi)) {
                $site_value = $request->site_multi;
            } 
            if($request->site) {
                $site_value[] = $request->site;
            }
        if($request->role_id == 4 || $request->role_id == 5 || $request->role_id == 6) {
            
            if(!empty($site_value)) {
                foreach($site_value as $site_val) {
                    $UserSite = new UserSite;
                    $UserSite->user_id = $user->id;
                    $UserSite->site_id = $site_val;
                    $UserSite->created_by = @Auth::user()->id;
                    $UserSite->save();

                    $transaction_client_users = new transaction_client_users();
                    $transaction_client_users -> site_id = $site_val;
                    $transaction_client_users -> transaction_id = $user->id;
                    $transaction_client_users -> transaction_mode = 'insert';
                    $transaction_client_users -> transaction_data_status = 1;
                    $transaction_client_users -> status = 1;
                    $transaction_client_users -> save();

                    $transaction_client_user_site = new transaction_client_user_site();
                    $transaction_client_user_site -> site_id = $site_val;
                    $transaction_client_user_site -> transaction_id = $UserSite->id;
                    $transaction_client_user_site -> transaction_mode = 'insert';
                    $transaction_client_user_site -> transaction_data_status = 1;
                    $transaction_client_user_site -> status = 1;
                    $transaction_client_user_site -> save();

                    $transaction_client_profiles = new transaction_client_profiles();
                    $transaction_client_profiles -> site_id = $site_val;
                    $transaction_client_profiles -> transaction_id = $user->id;
                    $transaction_client_profiles -> transaction_mode = 'insert';
                    $transaction_client_profiles -> transaction_data_status = 1;
                    $transaction_client_profiles -> status = 1;
                    $transaction_client_profiles -> save();

                    $transaction_client_model_has_roles = new transaction_client_model_has_roles();
                    $transaction_client_model_has_roles -> site_id = $site_val;
                    $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
                    $transaction_client_model_has_roles -> transaction_mode = 'insert';
                    $transaction_client_model_has_roles -> transaction_data_status = 1;
                    $transaction_client_model_has_roles -> status = 1;
                    $transaction_client_model_has_roles -> save();

                }
            }
        } else {
            if(!empty($site_value)) {
                foreach($site_value as $site_val) {
                    $UserSite = new UserSite;
                    $UserSite->user_id = $user->id;
                    $UserSite->site_id = $site_val;
                    $UserSite->created_by = @Auth::user()->id;
                    $UserSite->save();
                }
            }
        }


        $this->summary = [
            'site_add_user_token'   => $user->site_add_user_token,
            'User'   => $user,
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
        \Mail::to($user->email)->send(new SiteCreateUserMail($this->summary));



        // $user->profile->update($request->except(['username', 'password', 'email', 'roles', 'department', 'name', 'locale']));

        // $user->syncRoles($request->roles);

        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('saved_successfully'),
                'redirect' => route('users.index'),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    public function update(UserRequest $request, $id = null)
    {
        $this->checkPassword($request);
        $user = $this->user->findOrFail($id);
        $user->update($request->all());
        $user->profile->update($request->all());
        $user->syncRoles($request->roles);
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('users.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function ban($id = null)
    {
        $user = $this->user->findOrFail($id);
        $user->update(['banned' => $user->banned ? 0 : 1, 'ban_reason' => $this->request->ban_reason]);
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('users.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete($id = null)
    {
        $model = $this->user->find($id);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('users.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    private function checkPassword($request)
    {
        if (config('system.secure_password')) {
            return $request->validate(['password' => 'sometimes|pwned']);
        }
        return;
    }
}
