<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Classes\Fn_api;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['login']]);
        $this->guard = "api";
    }

    public function login()
    {
        $Fn_api = new Fn_api();
        $login_api = $Fn_api->login_api();
        return $login_api;
    //   return view('pages.home', compact('prices'));
    }

    public function show_data(Request $request)
    {
            $Fn_api = new Fn_api();
            $show_data_api = $Fn_api->show_data_api($request);
            return $show_data_api;
    }

    public function logout()
    {
        $Fn_api = new Fn_api();
        $logout_api = $Fn_api->logout_api();
        return $logout_api;
    //   return view('pages.home', compact('prices'));
    }

    public function refresh()
    {
        $Fn_api = new Fn_api();
        $refresh_api = $Fn_api->refresh_api();
        return $refresh_api;
    //   return view('pages.home', compact('prices'));
    }
    public function payload()
    {
        return auth()->payload();
    }
}

