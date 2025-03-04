<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

/**
 * Class PermissionsController
 *
 * Controller for handling permission-related backend requests.
 */
class PermissionsController extends Controller
{
    /**
     * PermissionsController constructor.
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
     * Display a listing of the permissions.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_permissions,show_permissions')){
            return redirect('admin/index');
        }

        $permissions = Permission::query()
                                 ->when(request('keyword') != '', function ($query){
                                     $query->search(request('keyword'));
                                 })
                                 ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                                 ->paginate(request('limit_by')?? '10')
                                 ->withQueryString();

        return view('backend.permissions.index', compact( 'permissions'));
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!\auth()->user()->ability('admin', 'create_permissions')){
            return redirect('admin/index');
        }
        $main_permissions = Permission::whereParent(0)->select('id', 'display_name', 'display_name_en')->get();
        return view('backend.permissions.create', compact('main_permissions'));

    }

    /**
     * Store a newly created permission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!\auth()->user()->ability('admin', 'create_permissions')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required|unique:permissions,name',
            'display_name' =>'required',
            'display_name_en' =>'required',
            'description'    => 'nullable',
            'description_en' => 'nullable',
            'route'         => 'required',
            'module'        => 'required',
            'as'            => 'required',
            'icon'          => 'required',
            'parent'        => 'required',
            'parent_show'   => 'required',
            'parent_original' => 'required',
            'sidebar_link'  => 'required',
            'appear'        => 'required',
            'ordering'      => 'required',
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
            'route'         => $request->route,
            'module'        => $request->module,
            'as'            => $request->as,
            'icon'          => $request->icon,
            'parent'        => $request->parent,
            'parent_show'   => $request->parent_show,
            'parent_original' => $request->parent_original,
            'sidebar_link'  => $request->sidebar_link,
            'appear'        => $request->appear,
            'ordering'      => $request->ordering,
        ];

        Permission::create($data);

        return redirect()->route('admin.permissions.index')->with([
            'message' => 'Permission created successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified permission.
     *
     * @param int $id
     */
    public function show($id)
    {
        /*        if (!\auth()->user()->ability('admin', 'display_post')){
                    return redirect('admin/index');
                }*/

    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_permissions')){
            return redirect('admin/index');
        }
        $permission= Permission::whereId($id)->first();
        $main_permissions = Permission::whereParent(0)->select('id', 'display_name', 'display_name_en')->get();
        return view('backend.permissions.edit', compact('permission', 'main_permissions') );

    }

    /**
     * Update the specified permission in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_permissions')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'name'          => 'required|unique:permissions,name,' . $id,
            'display_name' =>'required',
            'display_name_en' =>'required',
            'description'    => 'nullable',
            'description_en' => 'nullable',
            'route'         => 'required',
            'module'        => 'required',
            'as'            => 'required',
            'icon'          => 'required',
            'parent'        => 'required',
            'parent_show'   => 'required',
            'parent_original' => 'required',
            'sidebar_link'  => 'required',
            'appear'        => 'required',
            'ordering'      => 'required',
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
            'route'         => $request->route,
            'module'        => $request->module,
            'as'            => $request->as,
            'icon'          => $request->icon,
            'parent'        => $request->parent,
            'parent_show'   => $request->parent_show,
            'parent_original' => $request->parent_original,
            'sidebar_link'  => $request->sidebar_link,
            'appear'        => $request->appear,
            'ordering'      => $request->ordering,
        ];

        Permission::whereId($id)->update($data);


        return redirect()->route('admin.permissions.index')->with([
            'message'    => 'Permission Updated Successfully',
            'alert-type' => 'success',
        ]);


    }

    /**
     * Remove the specified permission from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_permissions')){
            return redirect('admin/index');
        }
        Permission::whereId($id)->delete();
        return  redirect()->route('admin.permissions.index')->with([
            'message' => 'Permission Deleted Successfully',
            'alert-type' => 'success',
        ]);

    }
}
