<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
//use Spatie\Permission\Models\Role;
use App\Models\Role;
use App\Services\ActivityLogService;
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
        $pageData = $roles = Role::whereNot('name', Role::CUSTOMER)->paginate(10);
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

        ActivityLogService::store('roles', 'create', (int) $role->id, $request->all(), 'Role created');

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

        ActivityLogService::store('roles', 'update', (int) $role->id, $request->all(), 'Role updated');

        return response()->json(['status' => true, 'notification' => __('messages.updated')]);
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            ActivityLogService::store('roles', 'delete', (int) $role->id, ['name' => $role->name], 'Role deleted');

            $role->delete();

            return response()->json(['status' => true, 'notification' => __('messages.deleted')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }
}
