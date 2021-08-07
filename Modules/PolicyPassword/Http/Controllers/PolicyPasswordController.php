<?php

namespace Modules\PolicyPassword\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Users\Entities\User;

class PolicyPasswordController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $data['page'] = 'Policy';
        return view('policypassword::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('policypassword::create');
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
        return view('policypassword::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('policypassword::edit');
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

    public function resetPasswordExpire(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => array(
                'required', 
                'string', 
                'min:8',  
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/',
                'regex:/[~!@#$%^&*-_+=?><]/', 
                'confirmed'
            ),
        ]);
        if ($validator->fails()){
            $validation = $validator->getMessageBag()->toArray();
            $error_current = null;
            if(auth()->check()){
                $user = User::find(auth()->user()->id);
                if(!Hash::check($request->current_password, $user->password)){
                    $error_current = 'The current password is invalid.';
                }
            }
            return response()->json(['errors' => $validation, 'error_current' => $error_current]);
        }else{
            if(auth()->check()){
                $user = User::find(auth()->user()->id);
                if(!Hash::check($request->current_password, $user->password)){
                    return response()->json(['errors' => [
                        'current_password' => ['The current password is invalid.']
                    ]]);
                }else{
                    if(empty($user->password_days_expire)){
                        $user->password_days_expire = '60';
                        $user -> password_start_reset = Carbon::now()->addDays(60);
                    }else{
                        $user -> password_start_reset = Carbon::now();
                    }
                    $user->password = $request->password;
                    $user -> save();
                    event(new PasswordReset($user));
                    Auth::logout();
                }
            }
            return response()->json(['success' => 'successfully']);
        }
    }
}
