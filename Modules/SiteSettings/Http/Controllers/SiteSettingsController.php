<?php

namespace Modules\SiteSettings\Http\Controllers;

use Auth;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\Tags_site;
use Modules\SiteSettings\Jobs\BulkDeleteSiteSettings;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Modules\Users\Entities\model_has_roles;
use App\transaction_client_model_has_roles;
use App\CredentialsController;
use App\transaction_client_profiles;
use App\transaction_client_site;
use App\transaction_client_site_category;
use App\transaction_client_user_site;
use App\transaction_client_users;
use App\transcation_jobs_clients;

class SiteSettingsController extends Controller
{
   
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;
    protected $logos_dir;

    public function __construct(Request $request, SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
        $this->logos_dir = config('system.logos_dir') . '/';
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $data['filter'] = $this->request->filter;
        $data['page'] = 'SiteSettings';
        return view('sitesettings::index')->with($data);
    }

    public function test_mongo()
    {
        $client = new \MongoDB\Client("mongodb://localhost:27017"); //Client
        // $client = new \MongoDB\Driver\Manager("mongodb://localhost:27017");//Client
        $collection = $client->demo->threat_intelligent_center;
        $insertOneResult = $collection->insertOne([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);
        $data['filter'] = $this->request->filter;
        $data['page'] = $this->getPage();
        return view('sitesettings::index')->with($data);
    }

    public function test_mongo2()
    {
        $mongo_client = new MongoDBDriverManager();
        var_dump($mongo_client);
        //    return view('sitesettings::index')->with($data);
    }

    public function artisan_call(Request $request){
        $transcation_jobs_clients_check = transcation_jobs_clients::where('site_id', $request->site_id)->where('mode', $request -> mode)->orderBy('created_at', 'desc')->first();
        if(empty($transcation_jobs_clients_check)){
            $transcation_jobs_clients_check = new transcation_jobs_clients();
            $transcation_jobs_clients_check  -> site_id = $request->site_id;
            $transcation_jobs_clients_check  -> mode = $request -> mode;
            $transcation_jobs_clients_check  -> status = 1;
            $transcation_jobs_clients_check  -> transaction_data_status = 1;
            $transcation_jobs_clients_check  -> save();

            $status = true;
            $job_key = null;
            $message = 'Success';
        }else if($transcation_jobs_clients_check -> transaction_data_status == 3){
            $transcation_jobs_clients_check = new transcation_jobs_clients();
            $transcation_jobs_clients_check  -> site_id = $request->site_id;
            $transcation_jobs_clients_check  -> mode = $request -> mode;
            $transcation_jobs_clients_check  -> status = 1;
            $transcation_jobs_clients_check  -> transaction_data_status = 1;
            $transcation_jobs_clients_check  -> save();

            $status = true;
            $job_key = null;
            $message = 'Success';
        }else{
            $status = false;
            $message = 'There is transaction information in the system, please wait a moment.';
            $job_key = null;
        }
        $dataout = [
            'status' => $status,
            'data' => $job_key,
            'message' => $message,
        ];
        return response()->json($dataout);
    }

    public function phpinfo()
    {
        phpinfo();
    }

    public function test()
    {
        $data['page'] = langapp('site_settings');
        return $data['page'];
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::modal.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    private function randPass($length, $strength=8) {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength >= 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength >= 2) {
            $vowels .= "AEUY";
        }
        if ($strength >= 4) {
            $consonants .= '0123456789012345678901234567890123456789';
        }
        if ($strength >= 8) {
            $consonants .= '@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%@#$%';
        }
    
        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    public function store(Request $request)
    {
        $dt = Carbon::now();
        $SiteSettings = $this->siteSettings;
        $SiteSettings->code = generator_uuid();
        $SiteSettings->name = $request->name;
        $SiteSettings->descript = $request->descript;
        $SiteSettings->address = $request->address;
        $SiteSettings->remark = $request->remark;
        $SiteSettings->created_by = @Auth::user()->id;
        $SiteSettings->active = $request->active ? 1 : 0;

        $SiteSettings->system_web_online = 1;
        $SiteSettings->system_site_online = 1;
        $SiteSettings->no_expiration_active = 0;
        $SiteSettings->start_active = $dt;
        $SiteSettings->end_active = $dt->addYear();
        // $SiteSettings->system_key = generator_uuid();
        $SiteSettings->public_key = str_random(135);
        $SiteSettings->mysql_user = str_random(10);
        $SiteSettings->mysql_password = $this->randPass(15);
        $SiteSettings->mongo_user = str_random(10);
        $SiteSettings->mongo_password = str_random(15);
        $SiteSettings->save();

        $transaction_client_site = new transaction_client_site();
        $transaction_client_site -> site_id = $SiteSettings->id;
        $transaction_client_site -> transaction_id = $SiteSettings->id;
        $transaction_client_site -> transaction_mode = 'insert';
        $transaction_client_site -> transaction_data_status = 1;
        $transaction_client_site -> status = 1;
        $transaction_client_site -> save();

        if ($request->category) {
            foreach ($request->category as $category) {
                $SiteCategory = new SiteCategory;
                $SiteCategory->site_id = $SiteSettings->id;
                $SiteCategory->category_id = $category;
                $SiteCategory->save();

                $transaction_client_site_category = transaction_client_site_category::where('site_id', $SiteSettings->id)->where('transaction_id', $SiteCategory->id)->first();
                if($transaction_client_site_category){
                    $transaction_client_site_category -> transaction_mode = 'insert';
                    $transaction_client_site_category -> transaction_data_status = 1;
                    $transaction_client_site_category -> status = 1;
                    $transaction_client_site_category -> save();
                }else{
                    $transaction_client_site_category = new transaction_client_site_category();
                    $transaction_client_site_category -> site_id = $SiteSettings->id;
                    $transaction_client_site_category -> transaction_id = $SiteCategory->id;
                    $transaction_client_site_category -> transaction_mode = 'insert';
                    $transaction_client_site_category -> transaction_data_status = 1;
                    $transaction_client_site_category -> status = 1;
                    $transaction_client_site_category -> save();
                }
            }
        }

        if ($request->hasFile('logo')) {
            $this->uploadLogo($request, $SiteSettings);
        }

        //---start---gen user_support-------//
        $user = new User;
        $user->code = generator_uuid();
        $user->username = 'support_'. $SiteSettings->id .'@sosecure.co.th';
        $user->email = 'support_'. $SiteSettings->id .'@sosecure.co.th';
        $user->email_verified_at = Carbon::now();
        $user->name = 'Admin Support';
        $user->password = 'support';
        $user->verify = 1;
        // $user->calendar_token = generator_uuid();
        // $user->access_token = generator_uuid();
        $user->site_id = $SiteSettings->id;
        $user->site_role_id = 6;
        $user->password_time_expire = Carbon::now();
        // dd($user);
        // exit();
        // $user->password = Hash::make(Str::uuid());
        // $user->password_time_expire = Carbon::now()->addMinutes(10);
        $user->active = 1;
        $user->save();

        $UserSite = new UserSite;
        $UserSite->user_id = $user->id;
        $UserSite->site_id = $user->site_id;
        $UserSite->created_by = @Auth::user()->id;
        $UserSite->active = 1;
        $UserSite->save();
        //----end------gen user_support----------------//

        $transaction_client_users = new transaction_client_users();
        $transaction_client_users -> site_id = $SiteSettings->id;
        $transaction_client_users -> transaction_id = $user->id;
        $transaction_client_users -> transaction_mode = 'insert';
        $transaction_client_users -> transaction_data_status = 1;
        $transaction_client_users -> status = 1;
        $transaction_client_users -> save();

        $transaction_client_user_site = new transaction_client_user_site();
        $transaction_client_user_site -> site_id = $SiteSettings->id;
        $transaction_client_user_site -> transaction_id = $UserSite->id;
        $transaction_client_user_site -> transaction_mode = 'insert';
        $transaction_client_user_site -> transaction_data_status = 1;
        $transaction_client_user_site -> status = 1;
        $transaction_client_user_site -> save();

        $transaction_client_profiles = new transaction_client_profiles();
        $transaction_client_profiles -> site_id = $SiteSettings->id;
        $transaction_client_profiles -> transaction_id = $user->id;
        $transaction_client_profiles -> transaction_mode = 'insert';
        $transaction_client_profiles -> transaction_data_status = 1;
        $transaction_client_profiles -> status = 1;
        $transaction_client_profiles -> save();


        if($user) {
            $model_has_roles = model_has_roles::where('model_id',$user->id)->first();
            if($model_has_roles) {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }

                $model_has_roles->role_id = 6;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                // $model_has_roles->model_id = $User->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();
            } else {
                $model_has_roles_q = model_has_roles::select('id')->orderBy('id','desc')->first();
                if($model_has_roles_q) {
                    $id_last = $model_has_roles_q->id+1;
                } else {
                    $id_last = 1;
                }
                
                $model_has_roles = new model_has_roles;
                $model_has_roles->role_id = 6;
                $model_has_roles->model_type = 'Modules\Users\Entities\User';
                $model_has_roles->model_id = $User->id;
                $model_has_roles->id = $id_last;
                $model_has_roles->save();
            }

            // $site_role_id = null;
            // if($role_id == 1 || $role_id == 4) {
            //     $role = 'admin';
            //     // $site_role_id = 99;
            // } else {
            //     $role = 'admin';//client_site
            //     // $site_role_id = $role_id;
            // } 

            // $User->syncRoles($role);

            $transaction_client_model_has_roles = new transaction_client_model_has_roles();
            $transaction_client_model_has_roles -> site_id = $SiteSettings->id;
            $transaction_client_model_has_roles -> transaction_id = $model_has_roles->id;
            $transaction_client_model_has_roles -> transaction_mode = 'insert';
            $transaction_client_model_has_roles -> transaction_data_status = 1;
            $transaction_client_model_has_roles -> status = 1;
            $transaction_client_model_has_roles -> save();
        }

        
            

        return ajaxResponse(
            [
                'id' => $SiteSettings->id,
                'message' => langapp('saved_successfully'),
                'redirect' => route('sitesettings.edit', ['id' => $SiteSettings->code]),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $Tags = Tags::all();
        $categories = CategorySettings::where([
            ['active', 1],
            ['deleted_at', '=', null],
        ])->get();
        $data['categories'] = $categories;
        $data['siteSettings'] = $get_data;
        $data['tags'] = $Tags;
        $data['page'] = "SiteSettings";
        return view('sitesettings::edit_site')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update($id, Request $request)
    {
        $SiteSettings = SiteSettings::where('code', $id)->first();
        if ($request->page_setting == 'site_settings') {
            $SiteSettings->name = $request->name;
            $SiteSettings->descript = $request->descript;
            $SiteSettings->address = $request->address;
            $SiteSettings->remark = $request->remark;
            $SiteSettings->active = $request->active ? 1 : 0;
        } else if ($request->page_setting == 'system_settings') {
            $SiteSettings->system_web_online = $request->system_web_online ? 1 : 0;
            $SiteSettings->system_site_online = $request->system_site_online ? 1 : 0;
            $SiteSettings->no_expiration_active = $request->no_expiration_active ? 1 : 0;
            if ($request->no_expiration_active) {
                $SiteSettings->start_active_key = Carbon::parse($request->start_active_key);
                $SiteSettings->end_active_key = Carbon::parse($request->end_active_key);
            }
            $SiteSettings->start_active = Carbon::parse($request->start_active);
            $SiteSettings->end_active = Carbon::parse($request->end_active);
            $SiteSettings->ip_key = $request->ip_key;
            $SiteSettings->ip_public = $request->ip_public;
            $SiteSettings->mac_address_key = $request->mac_address_key;
            $SiteSettings->system_key = $this->encrypt_decrypt('encrypt', $id . '&' . $request->ip_key . '&' . $request->mac_address_key, $request->ip_key, $request->mac_address_key);
        }
        $SiteSettings->save();

        $transaction_client_site = transaction_client_site::where('site_id', $SiteSettings->id)->where('transaction_id', $SiteSettings->id)->first();
        if($transaction_client_site){
            $transaction_client_site -> transaction_mode = 'update';
            $transaction_client_site -> transaction_data_status = 1;
            $transaction_client_site -> status = 1;
            $transaction_client_site -> save();
        }else{
            $transaction_client_site = new transaction_client_site();
            $transaction_client_site -> site_id = $SiteSettings->id;
            $transaction_client_site -> transaction_id = $SiteSettings->id;
            $transaction_client_site -> transaction_mode = 'update';
            $transaction_client_site -> transaction_data_status = 1;
            $transaction_client_site -> status = 1;
            $transaction_client_site -> save();
        }

        if ($request->page_setting == 'site_settings') {
            SiteCategory::where('site_id', $SiteSettings->id)->delete();
            if(!empty($request->category)) {
                foreach ($request->category as $category) {
                    $SiteCategory_check = SiteCategory::where('site_id', $SiteSettings->id)->where("category_id", $category)->first();
                    if ($SiteCategory_check) {

                    } else {
                        $SiteCategory = new SiteCategory;
                        $SiteCategory->site_id = $SiteSettings->id;
                        $SiteCategory->category_id = $category;
                        $SiteCategory->save();

                        $transaction_client_site_category = transaction_client_site_category::where('site_id', $SiteSettings->id)->where('transaction_id', $SiteCategory->id)->first();
                        if($transaction_client_site_category){
                            $transaction_client_site_category -> transaction_mode = 'update';
                            $transaction_client_site_category -> transaction_data_status = 1;
                            $transaction_client_site_category -> status = 1;
                            $transaction_client_site_category -> save();
                        }else{
                            $transaction_client_site_category = new transaction_client_site_category();
                            $transaction_client_site_category -> site_id = $SiteSettings->id;
                            $transaction_client_site_category -> transaction_id = $SiteCategory->id;
                            $transaction_client_site_category -> transaction_mode = 'update';
                            $transaction_client_site_category -> transaction_data_status = 1;
                            $transaction_client_site_category -> status = 1;
                            $transaction_client_site_category -> save();
                        }
                    }

                }
            }
            Tags_site::where('site_id', $SiteSettings->id)->delete();
            if ($request->tag) {
                if (count($request->tag) > 0) {
                    foreach ($request->tag as $tag) {
                        $Tags = Tags::where('id', $tag)->first();
                        if ($Tags) {

                        } else {
                            $Tags = new Tags;
                            $Tags->name = $tag;
                            $Tags->save();
                        }
                        $Tags_site = new Tags_site;
                        $Tags_site->site_id = $SiteSettings->id;
                        $Tags_site->tag_id = $Tags->id;
                        $Tags_site->save();
                    }
                }
            }

            // Tags_site::where('site_id', $SiteSettings -> id)->delete();
            // foreach($request->tag AS $tag) {
            //     $Tags_site = new Tags_site;
            //     $Tags_site->site_id = $SiteSettings->id;
            //     $Tags_site->tag_id = $tag;
            //     $Tags_site->save();
            // }

            if($request->input_img_logo_base64) {
                // dd($request->input_img_logo_base64);

                //---start----save----img_base64-------------------------//
                $img_base64_site_logo_login = $request->input_img_logo_base64;
                if($img_base64_site_logo_login) {
                        $data = $img_base64_site_logo_login;
                        list($type, $data) = explode(';', $data);
                        list(, $data)= explode(',', $data);
                        $data = base64_decode($data);
                    //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                        // $image_name= time().$k.'.png';
                        $image_name = 'logo'.time().'.png';
    
                        $target_dir = "images/logo_site/";
                        if (!is_dir($target_dir)) {
                            mkdir($target_dir, 0777, TRUE);
                        }
    
                        $target_file = $target_dir . $image_name;
                    //อัพโหลดภาพไปยัง public
                        // $path = public_path('images/file_editor') .'/'. $image_name;
                    //ทำการอัพโหลดภาพ
                        // file_put_contents($path, $data);
                        $image_path = $SiteSettings->logo;
    
                        if (file_put_contents($target_file, $data)) {
                            $SiteSettings->logo = $target_file;
                            $SiteSettings->save();
                                
                                if (File::exists($image_path)) {
                                    File::delete($image_path);
                                }
                                // $data = $this->Clients_model->get_clients($client_id);
                                // $data = $this->Login_setting_model->get_by_id_clients($client_id);
                                // if($data) {
                                //     if($data->site_logo) {
                                //                         // echo ($data[0]->site_logo);
                                //                         // exit();
                                //         if(file_exists($data->site_logo)) {
                                //             unlink($data->site_logo);
                                //         }
                                //     }
                                // }
                                // $this->Login_setting_model->edit_login_setting($save_id_arr,'site_logo',$target_file);//save_db
                        } else {
                            // echo json_encode(array("success" => false, 'message' => 'Sorry, there was an error uploading your file.'));
                            // exit();
                        }
                }
                //---stop----save----img_base64-------------------------//
            }

            // if ($request->logo) {
            //     $request->validate([
            //         'logo' => 'mimes:jpg,png,jpeg,gif,svg|max:2048',
            //     ]);
            //     $image_path = $SiteSettings->logo;
            //     if (File::exists($image_path)) {
            //         File::delete($image_path);
            //     }
            //     $image = $request->file('logo');
            //     $imagename = time() . '.' . $image->getClientOriginalExtension();
            //     $destinationPath = public_path('images/logo_site');
            //     $image->move($destinationPath, $imagename);
            //     $SiteSettings->logo = 'images/logo_site/' . $imagename;
            //     $SiteSettings->save();
                
            // }
        }
        if ($request->page_setting == 'site_settings') {
            return ajaxResponse(
                [
                    'id' => $SiteSettings->id,
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('sitesettings.edit', ['id' => $SiteSettings->code]),
                ],
                true,
                Response::HTTP_OK
            );
        } else if ($request->page_setting == 'system_settings') {
            return ajaxResponse(
                [
                    'id' => $SiteSettings->id,
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('systemsetting.index', ['id' => $SiteSettings->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function delete($id)
    {
        // dd($id);
        $SiteSettings = SiteSettings::where('code', $id)->first();

        $data['siteSettings'] = $SiteSettings;
        return view('sitesettings::modal.delete')->with($data);
    }

    public function holiday($status = null)
    {
        if ($status == 'enable') {
            Auth::user()->update(['on_holiday' => 1]);
            toastr()->warning(langapp('holiday_mode_enabled'), langapp('response_status'));
        } else {
            Auth::user()->update(['on_holiday' => 0]);
            toastr()->info(langapp('holiday_mode_disabled'), langapp('response_status'));
        }
        return redirect(url()->previous());
    }

    public function bulkDelete()
    {
        if ($this->request->has('checked')) {
            BulkDeleteSiteSettings::dispatch($this->request->checked, Auth::id());
            $data['message'] = langapp('deleted_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    }

    public function change_status(Request $request)
    {
        $SiteSettings = SiteSettings::where('code', $request->code)->first();
        $SiteSettings->active = $request->active;
        $SiteSettings->save();

        $transaction_client_site = transaction_client_site::where('site_id', $SiteSettings->id)->where('transaction_id', $SiteSettings->id)->first();
        if($transaction_client_site){
            $transaction_client_site -> transaction_mode = 'update';
            $transaction_client_site -> transaction_data_status = 1;
            $transaction_client_site -> status = 1;
            $transaction_client_site -> save();
        }else{
            $transaction_client_site = new transaction_client_site();
            $transaction_client_site -> site_id = $SiteSettings->id;
            $transaction_client_site -> transaction_id = $SiteSettings->id;
            $transaction_client_site -> transaction_mode = 'update';
            $transaction_client_site -> transaction_data_status = 1;
            $transaction_client_site -> status = 1;
            $transaction_client_site -> save();
        }

        $SiteCategory = SiteCategory::where('site_id',$SiteSettings->id)->get();
        // dd($SiteCategory);
        if($SiteCategory) {
            foreach($SiteCategory as $SiteCategory_key => $SiteCategory_val) {
                $SiteCategory_val->active = $request->active;
                $transaction_client_site_category = transaction_client_site_category::where('site_id', $SiteSettings->id)->where('transaction_id', $SiteCategory_val->id)->first();
                if($transaction_client_site_category){
                    $transaction_client_site_category -> transaction_mode = 'update';
                    $transaction_client_site_category -> transaction_data_status = 1;
                    $transaction_client_site_category -> status = 1;
                    $transaction_client_site_category -> save();
                }else{
                    $transaction_client_site_category = new transaction_client_site_category();
                    $transaction_client_site_category -> site_id = $SiteSettings->id;
                    $transaction_client_site_category -> transaction_id = $SiteCategory_val->id;
                    $transaction_client_site_category -> transaction_mode = 'update';
                    $transaction_client_site_category -> transaction_data_status = 1;
                    $transaction_client_site_category -> status = 1;
                    $transaction_client_site_category -> save();
                }
                $SiteCategory_val->save();
            }
        }
        // $SiteCategory->active = $request->active;
        // $SiteCategory->save();

        return ajaxResponse(
            [
                'id' => $SiteSettings->id,
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableData()
    {
        $model = $this->siteSettings->with(['get_categorys']);
        $model = $this->siteSettings->query();
        $test = 1;
        $model->when(
            $test == 1,
            function ($q) {
                return $q->where("deleted_at", null);
            }
        );

        return DataTables::eloquent($model)
            ->editColumn('no', function ($model) {
                return $model->code;
            })
            ->editColumn('chk', function ($model) {
                return '<label><input type="checkbox" class="site_settings_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->editColumn('logo', function ($model) {
                if ($model->logo) {
                    $site_logo = asset($model->logo);
                } else {
                    $site_logo = '';
                }
                $logo = '<div class="logo-site-tb"><img src="' . $site_logo . '" onerror="setDefaultPic(this)"></div>';
                return $logo;
            })
            ->editColumn('name', function ($model) {
                $html = '';
                $html .= "<a href='" . route('sitesettings.edit', ['id' => $model->code]) . "' >$model->name</a>";
                return $html;
            })
            ->editColumn('categorys', function ($model) {
                $return = '';
                foreach ($model->get_categorys as $data) {
                    $return .= $data->category->name . ', ';
                }
                return rtrim($return, ", ");
            })
            ->editColumn('assets_use', function ($model) {
                $return = '';
                return $return;
            })
            ->editColumn('start_active', function ($model) {
                $html = '';
                $html.= '<strong>Start Active:</strong> '.@$model->start_active.'<br>';
                if($model->start_active_key) {
                    $end_active_key = $model->end_active_key;
                } else {
                    $end_active_key = 'Lifetime';
                }
                $html.= '<strong>Start Active Key:</strong> '.$end_active_key;
                return $html;
            })
            ->editColumn('end_active', function ($model) {
                $html = '';
                $html.= '<strong>End Active:</strong> '.@$model->end_active.'<br>';
                if($model->end_active_key) {
                    $end_active_key = $model->end_active_key;
                } else {
                    $end_active_key = 'Lifetime';
                }
                $html.= '<strong>End Active Key:</strong> '.$end_active_key;
                return $html;
            })
            ->editColumn('status', function ($model) {
                if ($model->active == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="site-active-' . $model->code . '" onchange="change_site_active(\'' . $model->code . '\')" ' . $checked_val . ' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->editColumn('action', function ($model) {
                $html = '';
                $html .= "<a href='" . route('sitesettings.edit', ['id' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                        </a>
                        <a href='" . route('sitesettings.delete', ['id' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                        </a>";
                return $html;
            })
            ->rawColumns(['no', 'chk', 'logo', 'name', 'categorys', 'assets_use', 'start_active', 'end_active', 'status', 'action'])
            ->make(true);
    }

    // protected function applyFilter()
    // {
    //     if ($this->request->filled('filter')) {
    //         return $this->user->role($this->request->filter);
    //     }
    //     return $this->user->query();
    // }

    private function getPage()
    {
        return langapp('site_settings');
    }

    private function encrypt_decrypt($action, $string, $ip, $mac) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'secret-key-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
        $secret_iv = 'secret-iv-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
        // hash
        $key = hash('sha256', $secret_key);
    
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }


    // public function gdprExport()
    // {
    //     GDPRExportData::dispatch(Auth::user());
    //     toastr()->info('We will send you an email when your data is available', langapp('response_status'));

    //     return redirect(url()->previous());
    // }
    // /**
    //  * Export users as CSV
    //  */
    // public function export()
    // {
    //     if (isAdmin()) {
    //         return (new UsersExport)->download('users_' . now()->toIso8601String() . '.csv');
    //     }
    //     abort(404);
    // }

    public function modal_create_credentials(Request $request){


        
        $html='';
        // $id=$request->id;
        //     $credentials = Credentials::where('id',$id)->first();
        // if($id){
            
        // }else{
        //     $html='<div class="modal" id="myModal">
        //     <div class="modal-dialog">
        //       <div class="modal-content">
          
        //         <!-- Modal Header -->
        //         <div class="modal-header">
        //           <h4 class="modal-title">Modal Heading</h4>
        //           <button type="button" class="close" data-dismiss="modal">&times;</button>
        //         </div>
          
        //         <!-- Modal body -->
        //         <div class="modal-body">
        //           Modal body..
        //         </div>
          
        //         <!-- Modal footer -->
        //         <div class="modal-footer">
        //           <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        //         </div>
          
        //       </div>
        //     </div>
        //   </div>';
        // }
        
        $html='<div class="modal" id="myModal" >
                    <div class="modal-dialog">
                    <div class="modal-content">
                
                        
                        <div class="modal-header">
                        <h4 class="modal-title">Modal Heading</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                
                        <div class="modal-body">
                        Modal body..
                        </div>

                        <div class="modal-footer">
                        <button type="button" id ="btn_close_md_cr" class="btn btn-danger" >Close</button>
                        </div>
                
                    </div>
                    </div>
                </div>';

    return ajaxResponse(
        [
            'html' => $html,
            'message' => '',
        ],
        true,
        Response::HTTP_OK
    );


    }

    function sitesettings_delete(Request $request){
        {

            // dd($request->id);
                
                foreach($request->id as $id_change ){
                    $model = SiteSettings::select('id')->where("code", $id_change )->first();
                    $transaction_client_site = transaction_client_site::where('site_id', $model->id)->where('transaction_id', $model->id)->first();
                    if($transaction_client_site){
                        $transaction_client_site -> transaction_mode = 'delete';
                        $transaction_client_site -> transaction_data_status = 1;
                        $transaction_client_site -> status = 1;
                        $transaction_client_site -> save();
                    }else{
                        $transaction_client_site = new transaction_client_site();
                        $transaction_client_site -> site_id = $model->id;
                        $transaction_client_site -> transaction_id = $model->id;
                        $transaction_client_site -> transaction_mode = 'delete';
                        $transaction_client_site -> transaction_data_status = 1;
                        $transaction_client_site -> status = 1;
                        $transaction_client_site -> save();
                    }
                    SiteSettings::where("code", $id_change )->delete();

                    $SiteCategory = SiteCategory::where('site_id',$model->id)->first();
                    $transaction_client_site_category = transaction_client_site_category::where('site_id', $model->id)->where('transaction_id', $SiteCategory->id)->first();
                    if($transaction_client_site_category){
                        $transaction_client_site_category -> transaction_mode = 'delete';
                        $transaction_client_site_category -> transaction_data_status = 1;
                        $transaction_client_site_category -> status = 1;
                        $transaction_client_site_category -> save();
                    }else{
                        $transaction_client_site_category = new transaction_client_site_category();
                        $transaction_client_site_category -> site_id = $model->id;
                        $transaction_client_site_category -> transaction_id = $SiteCategory->id;
                        $transaction_client_site_category -> transaction_mode = 'delete';
                        $transaction_client_site_category -> transaction_data_status = 1;
                        $transaction_client_site_category -> status = 1;
                        $transaction_client_site_category -> save();
                    }
                    $SiteCategory -> delete();
    
                }

    
    
    
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('sitesettings.index'),
      
                ],
                true,
                Response::HTTP_OK
            );
    
        }

    }



}
