<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwoFactorAuthController extends Controller
{
    public function authenticate()
    {

        return redirect('dashboardnew');
        // return redirect(url()->previous());
    }

    public function reset()
    {
        $exitCode = \Artisan::call(
            '2fa:reset',
            [
                '--email' => \Auth::user()->email,
            ]
        );
        toastr()->success('Email sent to ' . \Auth::user()->email, langapp('response_status'));
        return redirect(url()->previous());
    }

    public function reset_2fa(Request $request){
        $exitCode = \Artisan::call(
            '2fa:reset',
            [
                '--email' => $request->email,
            ]
        );
        $response = [
            'status_code' => 200,
            'message' => 'Email sent to ' . $request->email, langapp('response_status')
        ];
        return response()->json($response);
    }
}
