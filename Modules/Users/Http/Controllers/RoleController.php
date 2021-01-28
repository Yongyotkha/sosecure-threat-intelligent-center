<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Users\Entities\User;
use Modules\Users\Entities\role_permissions;
use Modules\Users\Entities\permissions;
use Spatie\Permission\Models\Role;

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

    public function permission(Role $role)
    {
        $data['role'] = $role;

        return view('users::modal.rolePermissions')->with($data);
    }

    public function changePermission(Request $request, Role $role)
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

            
            $role_permissions_del = role_permissions::where('role_id', $request->role_id)->delete();
            if(count($permissions_id) > 0) {
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
