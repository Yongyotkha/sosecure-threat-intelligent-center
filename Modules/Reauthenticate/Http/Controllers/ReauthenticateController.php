<?php

namespace Modules\Reauthenticate\Http\Controllers;

use Modules\Reauthenticate\Http\Requests\VerifyUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Auth;
use App\Roles;
use App\transaction_client_role_permissions;
use App\transaction_client_users;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Modules\Users\Entities\role_menu_permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Users\Entities\model_has_roles;

class ReauthenticateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        dd(1234);
        return view('reauthenticate::index');
    }

    public function verify_site_user($token)
    {
        $User = User::where('site_add_user_token',$token)->first();

        if($User) {
            $model_has_roles = model_has_roles::where('model_id',$User->id)->first();
            if(@$model_has_roles) {
                if(@$model_has_roles->role_id == 4 || @$model_has_roles->role_id == 5 || @$model_has_roles->role_id == 6) {
                    $granted_access = 'Welcome Site:';
                    $granted_access_val = '';
                } else if($model_has_roles->role_id == 1) {
                    $granted_access = 'Granted access Site:';
                    $granted_access_val = 'All site';
                } else {
                    $granted_access = 'Granted access Site:';
                    $granted_access_val = '';
                }
            }
            $verify = $User->verify;
            $last_change_pass = $User->last_change_pass;
            if($verify == 0 && $last_change_pass == null) {
                return view('reauthenticate::index',compact('User','granted_access','granted_access_val'));
            } else {
                return redirect('/');
            }
        } else {
            return redirect('/');  
        }
 
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('reauthenticate::create');
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
        return view('reauthenticate::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('reauthenticate::edit');
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

    public function verify_success(Request $request)
    {
        $token = $request->token;
        $user = User::where('site_add_user_token',$token)->first();

        $model_has_roles = model_has_roles::where('model_id',$user->id)->first();
        if($model_has_roles) {
            if($model_has_roles->role_id == 4 || $model_has_roles->role_id == 5 || $model_has_roles->role_id == 6) {
                $url_redirect = route('reauth.verify_success');
                $UserSite = UserSite::select('site_id')->where('user_id',$user->id)->first();
                $site_ip = @$UserSite->get_site->ip_key;
            } else {
                $url_redirect = route('index');
            }
        }
        $data['site_ip'] = $site_ip;
        // dd($site_ip);

        return view('reauthenticate::verify_success')->with($data);
    }

    public function verify_update_pass(VerifyUserRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        // $user = $this->user->findOrFail($id);
        // $user = $this->user->where('code',$id)->first();
        $user = User::where('code',$id)->first();

        $model_has_roles = model_has_roles::where('model_id',$user->id)->first();
        if($model_has_roles) {
            if($model_has_roles->role_id == 4 || $model_has_roles->role_id == 5 || $model_has_roles->role_id == 6) {
                $url_redirect = route('reauth.verify_success').'?token='.$user->site_add_user_token;
                $UserSite = UserSite::select('site_id')->where('user_id',$user->id)->first();
            } else {
                $url_redirect = url('/');
            }
        }

        // $role = Roles::where('id',$user->site_role_id)->first();
        // if($role) {
        //     if($role != 1 && $role != 4 && $role != 5 && $role != 6) {
        //         $role = 2;
        //     }
        // }

        // dd($user);
        // exit();
        // $user->update($request->all());
        // $user->name = trim($request->name);

        $user->update(array(
            // 'name' =>  $request->name,
            // 'email' => $request->email,
            'password' => $request->password
        ));


        // $user->password = Hash::make($request->password);
        $user->verify = 1;
        $user->email_verified_at = Carbon::now();
        $user->last_change_pass = Carbon::now();
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

        // if($role) {
        //     $user->syncRoles($role->name);
        //     $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $user->site_id)->where('transaction_id', $user->id)->first();
        //     if($transaction_client_role_permissions){
        //         $transaction_client_role_permissions -> transaction_mode = 'update';
        //         $transaction_client_role_permissions -> transaction_data_status = 1;
        //         $transaction_client_role_permissions -> status = 1;
        //         $transaction_client_role_permissions -> save();
        //     }else{
        //         $transaction_client_role_permissions = new transaction_client_role_permissions();
        //         $transaction_client_role_permissions -> site_id = $user->site_id;
        //         $transaction_client_role_permissions -> transaction_id = $user->id;
        //         $transaction_client_role_permissions -> transaction_mode = 'update';
        //         $transaction_client_role_permissions -> transaction_data_status = 1;
        //         $transaction_client_role_permissions -> status = 1;
        //         $transaction_client_role_permissions -> save();
        //     }
        // }

        // $site_code = $this->siteSettings->find_code($user->site_id);

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $user);
        // }
        return ajaxResponse(
            [
                'id'       => $user->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => @$url_redirect,
            ],
            true,
            Response::HTTP_OK
        );
    }
}
