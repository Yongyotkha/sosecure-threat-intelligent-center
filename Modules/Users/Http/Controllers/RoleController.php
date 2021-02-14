<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Users\Entities\User;
use App\Menu;
use App\Menu_sub;
use Modules\Users\Entities\role_menu_permission;
use Modules\Users\Entities\role_menu_sub_permission;
use Modules\Users\Entities\permission_menu;
use Modules\Users\Entities\permission_menu_sub;
use App\transaction_client_role_permissions;
use Modules\Users\Entities\role_permissions;
use Modules\Users\Entities\permissions;
use Spatie\Permission\Models\Role;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use Artisan;
use App\Roles;
use Yajra\DataTables\DataTables;
class RoleController extends Controller
{
    /**
     * Request instance
     *
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa', 'reauthenticate']);
        $this->request = $request;
    }

    /**
     * Show roles
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data['page'] = $this->getPage();

        return view('users::roles')->with($data);
    }

    public function create()
    {
        return view('users::modal.createRole');
    }

    /**
     * Update role
     */
    public function edit(Role $role)
    {
        $data['role'] = $role;

        return view('users::modal.updateRole')->with($data);
    }
    public function save()
    {
        $this->request->validate(['name' => 'required|unique:roles,name']);
        Role::firstOrCreate(['name' => strtolower($this->request->name)], ['name' => $this->request->name]);
        $data['message']  = langapp('saved_successfully');
        $data['redirect'] = route('users.roles');

        return ajaxResponse($data, true, Response::HTTP_OK);
    }

    public function update($id = null)
    {
        $role = Role::findOrFail($id);
        $this->canChangeRole($role);
        $this->request->validate(['name' => 'required|unique:roles,name,' . $id . ',id']);
        $role->update(['name' => strtolower($this->request->name)]);
        $data['message']  = langapp('changes_saved_successful');
        $data['redirect'] = route('users.roles');

        return ajaxResponse($data, true, Response::HTTP_OK);
    }

    // public function permission_backup(Role $role)
    // {
    //     $data['role'] = $role;

    //     return view('users::modal.rolePermissions')->with($data);
    // }


    public function permission_role(Role $role)
    {
        $data['role'] = $role;

        return view('users::modal.rolePermissions_custom')->with($data);
    }

    public function permission(Role $role)
    {
        $data['role'] = $role;
        // $Menu = Menu::where('deleted_at', null)->whereNotIn('id', [8,9,10])->where('active', 1)->orderBy('order', 'asc')->get();
        $Menu = Menu::where('deleted_at', null)->where('active', 1)->orderBy('order', 'asc')->get();
        $result_menu_permission = DB::table("role_menu_permission")->select('menu_code')->where("role_id", @$role->id)->where("deleted_at", null)->get()->pluck('menu_code')->toArray();
        $result_menu_sub_permission = DB::table("role_menu_sub_permission")->select('menu_sub_code')->where("role_id", @$role->id)->where("deleted_at", null)->get()->pluck('menu_sub_code')->toArray();
        
        // $arr_menu_permission = array();
        // foreach ($result_menu_permission as $row) {
        //     array_push($arr_menu_permission, $row->menu_code);
        // }
        $data['role_menu_permission'] = $result_menu_permission;
        // dd($result_menu_permission);

        // $arr_menu_sub_permission = array();
        // foreach ($result_menu_sub_permission as $row) {
        //     array_push($arr_menu_sub_permission, $row->menu_sub_code);
        // }
        $data['role_menu_sub_permission'] = $result_menu_sub_permission;
        $data['menus'] = $Menu;
        
        return view('users::modal.rolePermissions')->with($data);
    }


    // public function changePermission(Request $request, Role $role)
    // {

    //     // dd($request);
    //     $request->validate(['role_id' => 'required']);
    //     $permissions = [];
    //     $permissions_id = [];
    //     if ($request->has('perm')) {
    //         foreach ($request->perm as $key => $value) {
    //             $permissions[] = $key;
    //             $permissions_id_where = permissions::select('id')->where('name', $key)->first();
    //             $permissions_id[] = $permissions_id_where->id;
    //         }

    //         $role_permissions_get = role_permissions::select('id')->where('role_id', $request->role_id)->get();
    //         $role_permissions_del = role_permissions::where('role_id', $request->role_id)->delete();
    //         if(count($permissions_id) > 0) {
    //             $SiteSettings = SiteSettings::select('id')->where('deleted_at',null)->get();

    //             if($SiteSettings) {
    //                 foreach($SiteSettings as $SiteSettings_val) {
    //                     if($role_permissions_get) {
    //                         foreach($role_permissions_get as $role_permissions_get_val) {
    //                             $transaction_client_role_permissions = new transaction_client_role_permissions();
    //                             $transaction_client_role_permissions -> site_id = $SiteSettings_val->id;
    //                             $transaction_client_role_permissions -> transaction_id = $role_permissions_get_val->id;
    //                             $transaction_client_role_permissions -> transaction_mode = 'delete';
    //                             $transaction_client_role_permissions -> transaction_data_status = 1;
    //                             $transaction_client_role_permissions -> status = 1;
    //                             $transaction_client_role_permissions -> save();
    //                         }
    //                     }
    //                 }
    //             }


    //             foreach($permissions_id as $permissions_id_val) {
    //                 $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
    //                 if($role_permissions_last) {
    //                     $role_permissions_last = $role_permissions_last->id+1;
    //                 } else {
    //                     $role_permissions_last = 1;
    //                 }

    //                 $role_permissions = new role_permissions;
    //                 $role_permissions->permission_id = $permissions_id_val;
    //                 $role_permissions->role_id = $request->role_id;
               
    //                 $role_permissions->id = $role_permissions_last;
    //                 $role_permissions->save();

                    
    //                 if($SiteSettings) {

    //                     foreach($SiteSettings as $SiteSettings_val) {

    //                         $transaction_client_role_permissions = new transaction_client_role_permissions();
    //                         $transaction_client_role_permissions -> site_id = $SiteSettings_val->id;
    //                         $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
    //                         $transaction_client_role_permissions -> transaction_mode = 'insert';
    //                         $transaction_client_role_permissions -> transaction_data_status = 1;
    //                         $transaction_client_role_permissions -> status = 1;
    //                         $transaction_client_role_permissions -> save();
    //                     }
    //                 }

                    
    //             }
    //         }
            


    //         // $role->syncPermissions($permissions);
    //     }
    //     $data['message']  = langapp('changes_saved_successful');
    //     $data['redirect'] = url()->previous();

    //     return ajaxResponse($data);
    // }


    public function changePermission_custom(Request $request, Role $role)
    {

        // dd($request);
        $request->validate(['role_id' => 'required']);
        $permissions = [];
        $permissions_id = [];
        if ($request->has('perm')) {
            foreach ($request->perm as $key => $value) {
                $permissions[] = $key;
                $permissions_id_where = permissions::select('id')->where('name', $key)->first();
                $permissions_id[] = $permissions_id_where->id;
            }

            $role_permissions_get = role_permissions::select('id')->where('role_id', $request->role_id)->get();
            $role_permissions_del = role_permissions::where('role_id', $request->role_id)->delete();
            if(count($permissions_id) > 0) {
                $SiteSettings = SiteSettings::select('id')->where('deleted_at',null)->get();

                if($SiteSettings) {
                    foreach($SiteSettings as $SiteSettings_val) {
                        if($role_permissions_get) {
                            foreach($role_permissions_get as $role_permissions_get_val) {
                                $transaction_client_role_permissions = new transaction_client_role_permissions();
                                $transaction_client_role_permissions -> site_id = $SiteSettings_val->id;
                                $transaction_client_role_permissions -> transaction_id = $role_permissions_get_val->id;
                                $transaction_client_role_permissions -> transaction_mode = 'delete';
                                $transaction_client_role_permissions -> transaction_data_status = 1;
                                $transaction_client_role_permissions -> status = 1;
                                $transaction_client_role_permissions -> save();
                            }
                        }
                    }
                }


                foreach($permissions_id as $permissions_id_val) {
                    $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                    if($role_permissions_last) {
                        $role_permissions_last = $role_permissions_last->id+1;
                    } else {
                        $role_permissions_last = 1;
                    }

                    $role_permissions = new role_permissions;
                    $role_permissions->permission_id = $permissions_id_val;
                    $role_permissions->role_id = $request->role_id;
               
                    $role_permissions->id = $role_permissions_last;
                    $role_permissions->save();

                    
                    if($SiteSettings) {

                        foreach($SiteSettings as $SiteSettings_val) {

                            $transaction_client_role_permissions = new transaction_client_role_permissions();
                            $transaction_client_role_permissions -> site_id = $SiteSettings_val->id;
                            $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                            $transaction_client_role_permissions -> transaction_mode = 'insert';
                            $transaction_client_role_permissions -> transaction_data_status = 1;
                            $transaction_client_role_permissions -> status = 1;
                            $transaction_client_role_permissions -> save();
                        }
                    }

                    
                }
            }
            


            // $role->syncPermissions($permissions);
        } else {
            $role_permissions_del = role_permissions::where('role_id', $request->role_id)->delete();
        }

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        $data['message']  = langapp('changes_saved_successful');
        $data['redirect'] = url()->previous();

        return ajaxResponse($data);
    }



    public function changePermission(Request $request, Role $role)
    {

        // dd($request);
        $request->validate(['role_id' => 'required']);

        if ($request->role_id) {

            $role_permissions_arr = role_permissions::select('id')->where('role_id', $request->role_id)->get()->pluck('id')->toArray();

            role_menu_permission::where('role_id', $request->role_id)->delete();
            role_permissions::where('role_id', $request->role_id)->delete();

            $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
            if(!empty($SiteSettings_id_arr)) {
                foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                    if(!empty($role_permissions_arr)) {
                        foreach($role_permissions_arr as $role_permissions_arr_val) {
                            $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions_arr_val)->first();
                            if($transaction_client_role_permissions){
                                $transaction_client_role_permissions -> transaction_mode = 'delete';
                                $transaction_client_role_permissions -> transaction_data_status = 1;
                                $transaction_client_role_permissions -> status = 1;
                                $transaction_client_role_permissions -> save();
                            }else{
                                $transaction_client_role_permissions = new transaction_client_role_permissions();
                                $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                $transaction_client_role_permissions -> transaction_id = $role_permissions_arr_val;
                                $transaction_client_role_permissions -> transaction_mode = 'delete';
                                $transaction_client_role_permissions -> transaction_data_status = 1;
                                $transaction_client_role_permissions -> status = 1;
                                $transaction_client_role_permissions -> save();
                            }
                        }
                    }
            
                }
            }
            // $UserSite = UserSite::where('user_id',$user->id)->get()->pluck('site_id')->toArray();

            


            if ($request->menu) {
                if (count($request->menu) > 0) {
                    foreach ($request->menu as $menu) {
                        $tb_menu = Menu::select("id")->where("code", $menu)->first();
                        $role_menu_permission = new role_menu_permission;
                        $role_menu_permission->role_id = $request->role_id;
                        $role_menu_permission->menu_id = $tb_menu->id;
                        $role_menu_permission->menu_code = $menu;
                        $role_menu_permission->save();

                        $permission_menu = permission_menu::select('permission_id')->where('menu_id',$tb_menu->id)->get()->pluck('permission_id')->toArray();
                        if(!empty($permission_menu)) {
                            // role_permissions::where('role_id', $request->role_id)->delete();

                            foreach($permission_menu as $permission_menu_val) {
                                $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                                if($role_permissions_last) {
                                    $role_permissions_last = $role_permissions_last->id+1;
                                } else {
                                    $role_permissions_last = 1;
                                }
                                
                                $role_permissions = new role_permissions;
                                $role_permissions->permission_id = $permission_menu_val;
                                $role_permissions->role_id = $request->role_id;
                                $role_permissions->id = $role_permissions_last;
                                $role_permissions->save();


                                $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                                if(!empty($SiteSettings_id_arr)) {
                                    foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                      
                              
                                            $transaction_client_role_permissions = new transaction_client_role_permissions();
                                            $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                            $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                            $transaction_client_role_permissions -> transaction_mode = 'insert';
                                            $transaction_client_role_permissions -> transaction_data_status = 1;
                                            $transaction_client_role_permissions -> status = 1;
                                            $transaction_client_role_permissions -> save();
                                       
                                    }
                                }
                            }
                        }
                    }

                    //---add permission default-------------
                    $permission_arr = [68,82];
                    if(!empty($permission_arr)) {
                        foreach($permission_arr as $permission_arr_val) {
                            $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                            if($role_permissions_last) {
                                $role_permissions_last = $role_permissions_last->id+1;
                            } else {
                                $role_permissions_last = 1;
                            }

                            $role_permissions = new role_permissions;
                            $role_permissions->permission_id = $permission_arr_val;
                            $role_permissions->role_id = $request->role_id;
                            $role_permissions->id = $role_permissions_last;
                            $role_permissions->save();


                            $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                            if(!empty($SiteSettings_id_arr)) {
                                foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                    // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions->id)->first();
                                    // if($transaction_client_role_permissions){
                                    //     $transaction_client_role_permissions -> transaction_mode = 'insert';
                                    //     $transaction_client_role_permissions -> transaction_data_status = 1;
                                    //     $transaction_client_role_permissions -> status = 1;
                                    //     $transaction_client_role_permissions -> save();
                                    // }else{
                                        $transaction_client_role_permissions = new transaction_client_role_permissions();
                                        $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                        $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                        $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        $transaction_client_role_permissions -> transaction_data_status = 1;
                                        $transaction_client_role_permissions -> status = 1;
                                        $transaction_client_role_permissions -> save();
                                    // }
                                }
                            }


                        }
                    }


                    if($request->role_id == 4 || $request->role_id == 5 || $request->role_id == 6) {//ฝั่ง site
                        $permission_arr = [174];
                        if(!empty($permission_arr)) {
                            foreach($permission_arr as $permission_arr_val) {
                                $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                                if($role_permissions_last) {
                                    $role_permissions_last = $role_permissions_last->id+1;
                                } else {
                                    $role_permissions_last = 1;
                                }
    
                                $role_permissions = new role_permissions;
                                $role_permissions->permission_id = $permission_arr_val;
                                $role_permissions->role_id = $request->role_id;
                                $role_permissions->id = $role_permissions_last;
                                $role_permissions->save();
    
    
                                $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                                if(!empty($SiteSettings_id_arr)) {
                                    foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                        // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions->id)->first();
                                        // if($transaction_client_role_permissions){
                                        //     $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        //     $transaction_client_role_permissions -> transaction_data_status = 1;
                                        //     $transaction_client_role_permissions -> status = 1;
                                        //     $transaction_client_role_permissions -> save();
                                        // }else{
                                            $transaction_client_role_permissions = new transaction_client_role_permissions();
                                            $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                            $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                            $transaction_client_role_permissions -> transaction_mode = 'insert';
                                            $transaction_client_role_permissions -> transaction_data_status = 1;
                                            $transaction_client_role_permissions -> status = 1;
                                            $transaction_client_role_permissions -> save();
                                        // }
                                    }
                                }
    
    
                            }
                        }
                    }  else if($request->role_id == 1) {//super admin
                        $permission_arr = [42,43,44,68,78,82,117,118,119,120,121,122,138,139,140,141,173];
                        if(!empty($permission_arr)) {
                            foreach($permission_arr as $permission_arr_val) {
                                $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                                if($role_permissions_last) {
                                    $role_permissions_last = $role_permissions_last->id+1;
                                } else {
                                    $role_permissions_last = 1;
                                }
    
                                $role_permissions = new role_permissions;
                                $role_permissions->permission_id = $permission_arr_val;
                                $role_permissions->role_id = $request->role_id;
                                $role_permissions->id = $role_permissions_last;
                                $role_permissions->save();
    
    
                                $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                                if(!empty($SiteSettings_id_arr)) {
                                    foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                        // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions->id)->first();
                                        // if($transaction_client_role_permissions){
                                        //     $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        //     $transaction_client_role_permissions -> transaction_data_status = 1;
                                        //     $transaction_client_role_permissions -> status = 1;
                                        //     $transaction_client_role_permissions -> save();
                                        // }else{
                                            $transaction_client_role_permissions = new transaction_client_role_permissions();
                                            $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                            $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                            $transaction_client_role_permissions -> transaction_mode = 'insert';
                                            $transaction_client_role_permissions -> transaction_data_status = 1;
                                            $transaction_client_role_permissions -> status = 1;
                                            $transaction_client_role_permissions -> save();
                                        // }
                                    }
                                }
    
    
                            }
                        }
                    } else {//client ฝั่ง center
                        $permission_arr = [173];
                        if(!empty($permission_arr)) {
                            foreach($permission_arr as $permission_arr_val) {
                                $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                                if($role_permissions_last) {
                                    $role_permissions_last = $role_permissions_last->id+1;
                                } else {
                                    $role_permissions_last = 1;
                                }
    
                                $role_permissions = new role_permissions;
                                $role_permissions->permission_id = $permission_arr_val;
                                $role_permissions->role_id = $request->role_id;
                                $role_permissions->id = $role_permissions_last;
                                $role_permissions->save();
    
    
                                $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                                if(!empty($SiteSettings_id_arr)) {
                                    foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                        // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions->id)->first();
                                        // if($transaction_client_role_permissions){
                                        //     $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        //     $transaction_client_role_permissions -> transaction_data_status = 1;
                                        //     $transaction_client_role_permissions -> status = 1;
                                        //     $transaction_client_role_permissions -> save();
                                        // }else{
                                            $transaction_client_role_permissions = new transaction_client_role_permissions();
                                            $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                            $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                            $transaction_client_role_permissions -> transaction_mode = 'insert';
                                            $transaction_client_role_permissions -> transaction_data_status = 1;
                                            $transaction_client_role_permissions -> status = 1;
                                            $transaction_client_role_permissions -> save();
                                        // }
                                    }
                                }
    
    
                            }
                        }
                    }
                    //-end--add permission default-------------

                }
            } else {
                if($request->role_id == 1) {//super admin
                    $permission_arr = [42,43,44,68,78,82,117,118,119,120,121,122,138,139,140,141,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173];//173
                    if(!empty($permission_arr)) {
                        foreach($permission_arr as $permission_arr_val) {
                            $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                            if($role_permissions_last) {
                                $role_permissions_last = $role_permissions_last->id+1;
                            } else {
                                $role_permissions_last = 1;
                            }

                            $role_permissions = new role_permissions;
                            $role_permissions->permission_id = $permission_arr_val;
                            $role_permissions->role_id = $request->role_id;
                            $role_permissions->id = $role_permissions_last;
                            $role_permissions->save();


                            $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                            if(!empty($SiteSettings_id_arr)) {
                                foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                    $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions_last)->first();
                                    if($transaction_client_role_permissions){
                                        $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        $transaction_client_role_permissions -> transaction_data_status = 1;
                                        $transaction_client_role_permissions -> status = 1;
                                        $transaction_client_role_permissions -> save();
                                    }else{
                                        $transaction_client_role_permissions = new transaction_client_role_permissions();
                                        $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                        $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                        $transaction_client_role_permissions -> transaction_mode = 'insert';
                                        $transaction_client_role_permissions -> transaction_data_status = 1;
                                        $transaction_client_role_permissions -> status = 1;
                                        $transaction_client_role_permissions -> save();
                                    }
                                }
                            }


                        }
                    }
                }
            }

            role_menu_sub_permission::where('role_id', $request->role_id)->delete();
            if ($request->menu_sub) {
                if (count($request->menu_sub) > 0) {
                    foreach ($request->menu_sub as $menu_sub) {
                        $tb_menu_sub = Menu_sub::select("id")->where("code", $menu_sub)->first();
                        $role_menu_sub_permission = new role_menu_sub_permission;
                        $role_menu_sub_permission->role_id = $request->role_id;
                        $role_menu_sub_permission->menu_sub_id = $tb_menu_sub->id;
                        $role_menu_sub_permission->menu_sub_code = $menu_sub;
                        $role_menu_sub_permission->save();


                        $permission_menu_sub = permission_menu_sub::select('permission_id')->where('menu_sub_id',$tb_menu_sub->id)->get()->pluck('permission_id')->toArray();
                        if(!empty($permission_menu_sub)) {
                            // role_permissions::where('role_id', $request->role_id)->delete();

                            foreach($permission_menu_sub as $permission_menu_sub_val) {
                                $role_permissions_last = role_permissions::select('id')->orderBy('id', 'desc')->first();
                                if($role_permissions_last) {
                                    $role_permissions_last = $role_permissions_last->id+1;
                                } else {
                                    $role_permissions_last = 1;
                                }
                                
                                $role_permissions = new role_permissions;
                                $role_permissions->permission_id = $permission_menu_sub_val;
                                $role_permissions->role_id = $request->role_id;
                                $role_permissions->id = $role_permissions_last;
                                $role_permissions->save();



                                $SiteSettings_id_arr = SiteSettings::select('id')->get()->pluck('id')->toArray();
                                if(!empty($SiteSettings_id_arr)) {
                                    foreach($SiteSettings_id_arr as $SiteSettings_id_arr_val) {
                                      
                                                // $transaction_client_role_permissions = transaction_client_role_permissions::where('site_id', $SiteSettings_id_arr_val)->where('transaction_id', $role_permissions_last)->first();
                                                // if($transaction_client_role_permissions){
                                                //     $transaction_client_role_permissions -> transaction_mode = 'insert';
                                                //     $transaction_client_role_permissions -> transaction_data_status = 1;
                                                //     $transaction_client_role_permissions -> status = 1;
                                                //     $transaction_client_role_permissions -> save();
                                                // }else{
                                                    $transaction_client_role_permissions = new transaction_client_role_permissions();
                                                    $transaction_client_role_permissions -> site_id = $SiteSettings_id_arr_val;
                                                    $transaction_client_role_permissions -> transaction_id = $role_permissions_last;
                                                    $transaction_client_role_permissions -> transaction_mode = 'insert';
                                                    $transaction_client_role_permissions -> transaction_data_status = 1;
                                                    $transaction_client_role_permissions -> status = 1;
                                                    $transaction_client_role_permissions -> save();
                                                // }
                                    }
                                }
                            }
                        }
                    }
                }
            }



            
            
            
        }


        // dd($request);
        
  


        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        
        $data['message']  = langapp('changes_saved_successful');
        $data['redirect'] = url()->previous();

        return ajaxResponse($data);
    }

    public function delete(Role $role)
    {
        $data['role'] = $role;

        return view('users::modal.deleteRole')->with($data);
    }
    public function destroy($id = null)
    {
        $role = Role::findOrFail($id);
        $this->canChangeRole($role);
        if (User::role($role->name)->count() > 0) {
            $data['message']  = 'Failed to delete role (Already in use)';
            $data['redirect'] = route('users.roles');

            return ajaxResponse($data, false, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $role->delete();
        $data['message']  = langapp('deleted_successfully');
        $data['redirect'] = route('users.roles');

        return ajaxResponse($data, true, Response::HTTP_OK);
    }

    private function canChangeRole($role)
    {
        if ($role->name == 'admin' || $role->name == 'client') {
            $error = \Illuminate\Validation\ValidationException::withMessages(
                [
                    'roles' => ['You are not allowed to change admin and client roles'],
                ]
            );
            throw $error;
        }
    }

    private function getPage()
    {
        return langapp('users');
    }

    public function data_table()
    {

        $model = role_menu_permission::
        select('roles.id as roles_id','roles.name as roles_name','menu.name as menu_name')
        ->leftjoin('menu', 'role_menu_permission.menu_id', '=', 'menu.id')
        ->rightjoin('roles', 'role_menu_permission.role_id', '=', 'roles.id');
        
 
        $model_sub = role_menu_sub_permission::select('roles.id as roles_id','roles.name as roles_name','menu_sub.name as menu_name')
        ->leftjoin('menu_sub', 'role_menu_sub_permission.menu_sub_id', '=', 'menu_sub.id')
        ->rightjoin('roles', 'role_menu_sub_permission.role_id', '=', 'roles.id')
        ->union($model)
        ->groupBy('roles_id');
        // ->get();

        $data =  DB::table(DB::raw("({$model_sub->toSql()}) AS fx_s"))
        ->select('s.roles_id as id','s.roles_name as name',DB::raw('group_concat(fx_s.menu_name) as sub_menu_name'))
         ->groupBy('s.roles_id')
        ->get();
        return DataTables::of($data)->toJson();

  

    




    }
}
