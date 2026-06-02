<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller as BaseController;

class RoleController extends BaseController
{
    protected $module;       

    public function __construct()
    {
        $this->module = 'roles';
        view()->share('module', $this->module);

        $this->middleware('permission:roles view')->only(['index', 'show']);
        $this->middleware('permission:roles create')->only(['create', 'store']);
        $this->middleware('permission:roles edit')->only(['edit', 'update']);
        $this->middleware('permission:roles delete')->only(['destroy']);         
    }

    public function index()
    {
        $pageData = Role::paginate(10);
        return view('backend.' . $this->module . '.index', compact('pageData'));
    }

    public function create()
    {
        return view('backend.' . $this->module . '.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions([]);

        return response()->json(['status' => true, 'notification' => __('messages.created')]);
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy(fn($p) => explode(' ', $p->name)[0]);

        return view('backend.' . $this->module . '.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        // 🔥 Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json(['status' => true, 'notification' => __('messages.updated')]);
    }

    public function destroy($id)
    {
        Role::destroy($id);
        return redirect()->route($this->module . '.index')->with('success', __('messages.deleted'));
    }
}
