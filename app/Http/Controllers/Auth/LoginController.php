<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Modules\Users\Entities\User;
use Modules\Users\Entities\model_has_roles;

use App\Menu;
use App\Menu_sub;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public $maxAttempts  = 5;
    public $decayMinutes = 3;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['installed', 'guest'])->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }


        $role_status = 0;
        $User = User::where('email',$request->email)->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->first();
        $model_has_roles = model_has_roles::where('role_id',@$User->get_model_has_roles->role_id)->first();
        if($model_has_roles) {
            if($model_has_roles->role_id == 1 || $model_has_roles->role_id == 2) {
                $role_status = 1;
            } else {
                $role_status = 0;
            }
        }


        // if ((Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active' => 1, 'site_role_id' => 1]) ) || ($this->oldLogin($request))) {
        if ( ($this->oldLogin($request))) {//custom login
            // The user is active, not suspended, and exists.

            $menu = Menu::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
            // session_start();
            $_SESSION["menu"] = $menu;
            // session('menu', $menu);
            // dd($menu);
            // dd(55);
            if(Session::has('check_goto_menu')){
                Session::forget('check_goto_menu');
                Session::put('check_goto_menu', @check_goto_menu($menu));
            } else {
                if(@check_goto_menu($menu)) {
                    Session::put('check_goto_menu', @check_goto_menu($menu));
                }
            }

            
            return $this->sendLoginResponse($request);

        }


        
        // if ($this->attemptLogin($request) || $this->oldLogin($request)) {
        //     // $user = $this->guard('api')->user();
        //     // $user->generateToken();

        //     $menu = Menu::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
        //     // session_start();
        //     $_SESSION["menu"] = $menu;
        //     // session('menu', $menu);
           
        //     return $this->sendLoginResponse($request);
        // }



        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function validateLogin(Request $request)
    {
        $rules                    = [];
        $rules[$this->username()] = 'required|string';
        $rules['password']        = 'required|string';
        if (get_option('use_recaptcha') === 'TRUE') {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }
        $this->validate($request, $rules);
    }

    /**
     * Attempt to log the user into the application using old credentials
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function oldLogin($request)
    {
        $role_status = 0;
        $User = User::where('email',$request->email)->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->where('verify',1)->first();
        // $User = User::where('email', $request->email)->first();
        $model_has_roles = model_has_roles::where('role_id',@$User->get_model_has_roles->role_id)->first();
        if($model_has_roles) {
            if($model_has_roles->role_id == 1 || $model_has_roles->role_id == 2) {
                $role_status = 1;
            } else if($model_has_roles->role_id == 4 || $model_has_roles->role_id == 5 || $model_has_roles->role_id == 6) {
                $role_status = 0;
            } else {
                $role_status = 1;
            }
        }

        $user = User::where('email', $request->email)->first();
        if ($user != null && @$role_status == 1) {
            $passwordHasher = new PasswordHash(8, true);
            $passwordMatch  = $passwordHasher->CheckPassword($request->password, $user->password);
            if ($passwordMatch) {
                return Auth::login($user, true);
            }
        }
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/');
    }

    /*
    |--------------------------------------------------------------------------
    | Api เข้าสู่ระบบ
    |--------------------------------------------------------------------------
     */

    public function test_api(Request $request)
    {
        return 123;
    }
    public function login_api(Request $request)
    {
        // $user = User::select('id')->where('username', $request->username)->first();

        $user = User::select('id','username','email','password')->where('email', $request->email)->first();
        if ($user != null) {
            $passwordHasher = new PasswordHash(8, true);
            $passwordMatch  = $passwordHasher->CheckPassword($request->password, $user->password);
            if ($passwordMatch) {
                $token = $this->jwt($user);
                $user["api_token"] = $token;
    
                return $this->responseRequestSuccess($user);
            } else{
                return $this->responseRequestError("Username or password is incorrect");
            }
        } else {
            return $this->responseRequestError("Username or password is incorrect");
        }


        // if (!empty($user) && Hash::check($request->password, $user->password)) {
        //     $token = $this->jwt($user);
        //     $user["api_token"] = $token;

        //     return $this->responseRequestSuccess($user);
        // } else {
        //     return $this->responseRequestError("Username or password is incorrect");
        // }
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

    /*
    |--------------------------------------------------------------------------
    | response เมื่อข้อมูลส่งถูกต้อง
    |--------------------------------------------------------------------------
     */
    protected function responseRequestSuccess($ret)
    {
        return response()->json(['status' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /*
    |--------------------------------------------------------------------------
    | response เมื่อข้อมูลมีการผิดพลาด
    |--------------------------------------------------------------------------
     */
    protected function responseRequestError($message = 'Bad request', $statusCode = 200)
    {
        return response()->json(['status' => 'error', 'error' => $message], $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
