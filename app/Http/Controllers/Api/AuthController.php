<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiController;
use Modules\Users\Entities\User;
use Auth;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends ApiController
{
    public function login(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationLogin($header, $request->mode, $request->code);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationLogin($header, $request->mode, $request->code);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            // ->where('site_id', $site['data']['id'])
            $user = User::where('email', $data_key['email'])->first();
            if ($user != null) {
                $passwordHasher = new PasswordHash(8, true);
                $passwordMatch  = $passwordHasher->CheckPassword($data_key['password'], $user->password);
                if ($passwordMatch) {
                    $token = $this->jwt($user);
                    $user -> access_token = $token;
                    $user -> save();
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $user]);
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
