<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * SOW A.10 — Roles & Permissions. Superadmin always has every permission;
 * Reviewer and Author are fixed system roles; custom roles may be added.
 */
class RoleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('roles.view');

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', ['roles' => $roles, 'systemRoles' => RoleName::values()]);
    }

    public function create(): View
    {
        Gate::authorize('roles.create');

        return view('admin.roles.form', ['role' => new Role, 'granted' => [], 'modules' => Permissions::MODULES]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->validated('permissions', []));

        activity()->log('Roles & Permissions', 'Created role', $role->id, $request->validated());

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} created.");
    }

    public function edit(Role $role): View
    {
        Gate::authorize('roles.edit');

        return view('admin.roles.form', [
            'role' => $role,
            'granted' => $role->permissions()->pluck('name')->all(),
            'modules' => Permissions::MODULES,
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->name === RoleName::Superadmin->value, 403, 'Superadmin always has every permission.');

        if (! in_array($role->name, RoleName::values(), true)) {
            $role->update(['name' => $request->validated('name')]);
        }

        $role->syncPermissions($request->validated('permissions', []));

        activity()->log('Roles & Permissions', 'Updated role permissions', $role->id, $request->validated());

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('roles.delete');

        if (in_array($role->name, RoleName::values(), true)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Reassign the users of this role before deleting it.');
        }

        activity()->log('Roles & Permissions', 'Deleted role', $role->id, ['name' => $role->name]);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
