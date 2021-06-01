<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Redirect;
use Modules\Users\Entities\User;

class CheckBanned
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->banned) {
            auth()->logout();
            toastr()->warning('Your account has been suspended. Please contact administrator.', langapp('response_status'));
            return redirect()->route('login');
        }

        if(auth()->check()){
            $user = User::find(auth()->user()->id);
            if($user){
                if((Carbon::parse($user->password_start_reset)->addDays($user->password_days_expire) <= Carbon::now() || $user->password_start_reset == '') && $request->path() !== 'resetPasswordExpire'){
                    $page = 'Policy';
                    return response()->view('policypassword::index', compact('page'))->setStatusCode(200);
                }
            }
        }

        return $next($request);
    }
}
