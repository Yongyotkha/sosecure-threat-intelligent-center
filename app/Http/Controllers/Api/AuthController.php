<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiController;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use App\Menu;
use App\Menu_sub;
use App\Menu_permission_site;
use App\Menu_sub_permission_site;
use Auth;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Modules\Users\Entities\model_has_roles;

class AuthController extends ApiController
{
    public function login(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationLogin($header, $request->mode, $request->code);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationLogin($header, $request->mode, $request->code);
        }
        
        $value = $request -> data;
        // dd($value);
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        // dd($data);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            // 

            $user_check = User::where('email', $data_key['email'])->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->where('verify',1)->first();
            $UserSite = UserSite::where('user_id',@$user_check->id)->where('active',1)->get()->pluck('site_id')->toArray();

            $role_status = 0;
            $user = User::where('email', $data_key['email'])->where('site_id', $site['data']['id'])->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->where('verify',1);
            $user = $user->where(function($q) {
                $q->whereNull('password_time_expire');
                $q->orWhereDate('password_time_expire', '<=', date('Y-m-d H:i:s'));
            });
            $user = $user->whereHas('get_user_site_many', function($q) use ($UserSite) {
                $q->whereIn('site_id', $UserSite);
            });

            $user = $user->first();
            $model_has_roles = model_has_roles::where('role_id',@$user->get_model_has_roles->role_id)->first();
            if($model_has_roles) {
                if($model_has_roles->role_id == 1 || $model_has_roles->role_id == 2) {
                    $role_status = 0;
                } else if($model_has_roles->role_id == 4 || $model_has_roles->role_id == 5 || $model_has_roles->role_id == 6) {
                    $role_status = 1;
                } else {
                    $role_status = 0;
                }
            }


            if ($user != null && @$role_status == 1) {
                $passwordHasher = new PasswordHash(8, true);
                $passwordMatch  = $passwordHasher->CheckPassword($data_key['password'], $user->password);
                if ($passwordMatch) {
                    $token = $this->jwt($user);
                    $user -> access_token = $token;
                    $user -> save();


                    $menu = Menu::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
                    $menu_sub = Menu_sub::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
                    $Menu_permission_site = Menu_permission_site::select('menu_id')->where('site_id',$user->site_id)->where('deleted_at',null)->get()->toArray();
                    // $Menu_permission_site = Menu_permission_site::all();
                    $Menu_sub_permission_site = Menu_sub_permission_site::select('menu_sub_id')->where('site_id',$user->site_id)->where('deleted_at',null)->get()->toArray();

                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $user , 'menu' => $menu , 'menu_sub' => $menu_sub , 'menu_sub_permission_site' => $Menu_sub_permission_site , 'menu_permission_site' => $Menu_permission_site]);
                    // return response()->json(['message' => 'Successful', 
                    //                             'error' => '', 
                    //                             'status_code' => '200', 
                    //                             'data' => $user,
                    //                             'menu' => $menu,
                    //                             'menu_sub' => $menu_sub,
                    //                             'menu_permission_site' => $Menu_permission_site,
                    //                             'menu_sub_permission_site' => $Menu_sub_permission_site]);
                } else{
                    return response()->json(['error' => 'Username or password is incorrect', 'status_code' => '400']);
                }
            } else {
                return response()->json(['error' => 'Username or password is incorrect', 'status_code' => '400']);
            }
        }
    } 

    /*
    |--------------------------------------------------------------------------
    | ตัวเข้ารหัส JWT
    |--------------------------------------------------------------------------
     */
    protected function jwt($user)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + env('JWT_EXPIRE_HOUR') * 60 * 60, // Expiration time
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }
}
