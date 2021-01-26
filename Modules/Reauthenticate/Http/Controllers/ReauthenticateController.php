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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ReauthenticateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('reauthenticate::index');
    }

    public function verify_site_user($token)
    {
        $User = User::where('site_add_user_token',$token)->first();
        $verify = $User->verify;
        $last_change_pass = $User->last_change_pass;
        if($verify == 0 && $last_change_pass == null) {
            return view('reauthenticate::index',compact('User'));
        } else {
            return redirect()->route('index');
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
        return view('reauthenticate::verify_success');
    }

    public function verify_update_pass(VerifyUserRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        // $user = $this->user->findOrFail($id);
        // $user = $this->user->where('code',$id)->first();
        $user = User::where('code',$id)->first();


        $role = Roles::where('id',$user->site_role_id)->first();
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

        if($role) {
            $user->syncRoles($role->name);
            $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $user->site_id)->where('transaction_id', $user->id)->first();
            if($transaction_client_role_permissions){
                $transaction_client_role_permissions -> transaction_mode = 'update';
                $transaction_client_role_permissions -> transaction_data_status = 1;
                $transaction_client_role_permissions -> status = 1;
                $transaction_client_role_permissions -> save();
            }else{
                $transaction_client_role_permissions = new transaction_client_role_permissions();
                $transaction_client_role_permissions -> site_id = $user->site_id;
                $transaction_client_role_permissions -> transaction_id = $user->id;
                $transaction_client_role_permissions -> transaction_mode = 'update';
                $transaction_client_role_permissions -> transaction_data_status = 1;
                $transaction_client_role_permissions -> status = 1;
                $transaction_client_role_permissions -> save();
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
                'redirect' => route('reauth.verify_success'),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
