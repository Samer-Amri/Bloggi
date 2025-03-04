<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class RolesController
 *
 * Controller for handling role-related backend requests.
 */
class RolesController extends Controller
{
    /**
     * RolesController constructor.
     * Redirects to login form if the user is not authenticated.
     */
    public function __construct() {
        if(\auth()->check()){
            $this->middleware('auth');
        }
        else {
            return view('backend.auth.login');
        }
    }

    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_roles,show_roles')){
            return redirect('admin/index');
        }

        $roles = Role::query()
                     ->when(request('keyword') != '', function ($query){
                         $query->search(request('keyword'));
                     })
                     ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                     ->paginate(request('limit_by')?? '10')
                     ->withQueryString();

        return view('backend.roles.index', compact( 'roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!\auth()->user()->ability('admin', 'create_roles')){
            return redirect('admin/index');
        }
        $permissions = Permission::select('id', 'display_name', 'display_name_en' )->get();
        return view('backend.roles.create', compact('permissions'));

    }

    /**
     * Store a newly created role in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!\auth()->user()->ability('admin', 'create_roles')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required|unique:roles,name',
            'display_name'  => 'required',
            'display_name_en'  => 'required',
            'description'    => 'nullable',
            'description_en' => 'nullable',
            'allowed_route' => 'nullable',
            'permissions'    => 'nullable',
        ]);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name'          => $request->name,
            'display_name'  => $request->display_name,
            'display_name_en'  => $request->display_name_en,
            'description'    => $request->description,
            'description_en' => $request->description_en,
            'allowed_route'  => $request->allowed_route,
        ];

        $role =  Role::create($data);
        if(count($request->permissions) > 0) {
            $role->attachPermissions($request->permissions);
        }
        return redirect()->route('admin.roles.index')->with([
            'message' => 'Role created successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified role.
     *
     * @param int $id
     */
    public function show($id)
    {
        /* if (!\auth()->user()->ability('admin', 'display_post')){
            return redirect('admin/index');
        } */
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_roles')){
            return redirect('admin/index');
        }

        $role= Role::whereId($id)->first();
        $permissions = Permission::select('id', 'display_name', 'display_name_en' )->get();
        $rolePerms = $role->perms()->pluck('id')->toArray();
        return view('backend.roles.edit', compact('role', 'permissions', 'rolePerms') );
    }

    /**
     * Update the specified role in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_roles')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'name'          => 'required|unique:roles,name,'. $id,
            'display_name'  => 'required',
            'display_name_en'  => 'required',
            'description'    => 'nullable',
            'description_en' => 'nullable',
            'allowed_route' => 'nullable',
            'permissions'    => 'nullable',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name'          => $request->name,
            'display_name'  => $request->display_name,
            'display_name_en'  => $request->display_name_en,
            'description'    => $request->description,
            'description_en' => $request->description_en,
            'allowed_route'  => $request->allowed_route,
        ];
        $role = Role::whereId($id)->first();
        $role->update($data);
        if(count($request->permissions) > 0) {
            $role->perms()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with([
            'message'    => 'Role Updated Successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Remove the specified role from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_roles')){
            return redirect('admin/index');
        }
        Role::whereId($id)->delete();
        return  redirect()->route('admin.roles.index')->with([
            'message' => 'Role Deleted Successfully',
            'alert-type' => 'success',
        ]);
    }
}
