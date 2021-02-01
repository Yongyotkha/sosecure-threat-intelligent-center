<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Users\Entities\User;
use App\transaction_client_role_permissions;
use Modules\Users\Entities\role_permissions;
use Modules\Users\Entities\permissions;
use Spatie\Permission\Models\Role;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use Modules\SiteSettings\Entities\Menu;
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


    public function permission(Role $role)
    {
        $data['role'] = $role;
        // $Menu = Menu::where('deleted_at', null)->whereNotIn('id', [8,9,10])->where('active', 1)->orderBy('order', 'asc')->get();
        $Menu = Menu::where('deleted_at', null)->where('active', 1)->orderBy('order', 'asc')->get();
        $result_menu_permission = DB::table("role_menu_permission")->select('menu_code')->where("role_id", $role)->where("deleted_at", null)->get()->toArray();
        $result_menu_sub_permission = DB::table("role_menu_sub_permission")->select('menu_sub_code')->where("role_id", $role)->where("deleted_at", null)->get()->toArray();
        
        $arr_menu_permission = array();
        foreach ($result_menu_permission as $row) {
            array_push($arr_menu_permission, $row->menu_code);
        }
        $data['site_menu_permission'] = $arr_menu_permission;

        $arr_menu_sub_permission = array();
        foreach ($result_menu_sub_permission as $row) {
            array_push($arr_menu_sub_permission, $row->menu_sub_code);
        }
        $data['site_menu_sub_permission'] = $arr_menu_sub_permission;
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



    public function changePermission(Request $request, Role $role)
    {
        // if ($request->page_setting == 'site_permission_settings') {
        //     // SiteCategory::where('site_id', $SiteSettings -> id)->delete();
        //     // foreach($request->category AS $category) {
        //     //     $SiteCategory = new SiteCategory;
        //     //     $SiteCategory->site_id = $SiteSettings->id;
        //     //     $SiteCategory->category_id = $category;
        //     //     $SiteCategory->save();
        //     // }
        //     site_menu_permission::where('site_id', $SiteSettings->id)->delete();
        //     if ($request->menu) {
        //         if (count($request->menu) > 0) {
        //             foreach ($request->menu as $menu) {
        //                 $tb_menu = Menu::select("id")->where("code", $menu)->first();
        //                 $site_menu_permission = new site_menu_permission;
        //                 $site_menu_permission->site_id = $SiteSettings->id;
        //                 $site_menu_permission->menu_id = $tb_menu->id;
        //                 $site_menu_permission->menu_code = $menu;
        //                 $site_menu_permission->save();
        //             }
        //         }
        //     }

        //     site_menu_sub_permission::where('site_id', $SiteSettings->id)->delete();
        //     if ($request->menu_sub) {
        //         if (count($request->menu_sub) > 0) {
        //             foreach ($request->menu_sub as $menu_sub) {
        //                 $tb_menu_sub = Menu_sub::select("id")->where("code", $menu_sub)->first();
        //                 $site_menu_sub_permission = new site_menu_sub_permission;
        //                 $site_menu_sub_permission->site_id = $SiteSettings->id;
        //                 $site_menu_sub_permission->menu_sub_id = $tb_menu_sub->id;
        //                 $site_menu_sub_permission->menu_sub_code = $menu_sub;
        //                 $site_menu_sub_permission->save();
        //             }
        //         }
        //     }

        //     site_config_email_alert::where('site_id', $SiteSettings->id)->delete();
        //     if ($request->email_alert) {
        //         if (count($request->email_alert) > 0) {
        //             foreach ($request->email_alert as $email_alert) {
        //                 $site_config_email_alert = new site_config_email_alert;
        //                 $site_config_email_alert->site_id = $SiteSettings->id;
        //                 $site_config_email_alert->email = $email_alert;
        //                 $site_config_email_alert->save();
        //             }
        //         }
        //     }

        //     // Tags_site::where('site_id', $SiteSettings -> id)->delete();
        //     // foreach($request->tag AS $tag) {
        //     //     $Tags_site = new Tags_site;
        //     //     $Tags_site->site_id = $SiteSettings->id;
        //     //     $Tags_site->tag_id = $tag;
        //     //     $Tags_site->save();
        //     // }

        // }


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
        }
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
}
