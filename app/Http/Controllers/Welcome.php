<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;

class Welcome extends Controller
{
    public function index()
    {
        return redirect('dashboardnew');
    }

    // public function test()
    // {
    //     return '1234';
    // }

    public function test(Request $request)
    {
        $user = $this->jwt(12345);
        return $user;
        // return $this->responseRequestSuccess($user);   
    }

    protected function jwt($user)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + env('JWT_EXPIRE_HOUR') * 60 * 60, // Expiration time
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    protected function responseRequestSuccess($ret)
    {
        return response()->json(['status' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    public function emailtest()
    {
        return view('emails.template_email');
    }
    public function search(Request $request)
    {
        print_r($request->input());
    }
}