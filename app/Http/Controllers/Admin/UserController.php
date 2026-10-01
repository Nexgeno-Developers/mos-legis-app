<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\SaveUser;
use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * SOW A.09 — Users Management.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles:id,name')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($request->enum('status', RecordStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('role')->value(), fn ($q, $role) => $q->role($role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name', 'name')->map(fn ($name) => ucfirst($name)),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.form', ['user' => new User] + $this->formOptions());
    }

    public function store(UserRequest $request, SaveUser $saveUser): RedirectResponse
    {
        $user = $saveUser->handle($request->validated());

        activity()->log('Users', 'Created user', $user, $request->safe()->except(['password', 'password_confirmation', 'profile_picture']));

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $user->load(['roles', 'permissions', 'reviewerContentCategories:id', 'authorProfile', 'address']);

        return view('admin.users.form', ['user' => $user] + $this->formOptions());
    }

    public function update(UserRequest $request, User $user, SaveUser $saveUser): RedirectResponse
    {
        $saveUser->handle($request->validated(), $user);

        activity()->log('Users', 'Updated user', $user, $request->safe()->except(['password', 'password_confirmation', 'profile_picture']));

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} updated.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        abort_if($user->is(auth()->user()), 422, 'You cannot deactivate your own account.');

        $user->update(['status' => $user->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);

        activity()->log('Users', $user->isActive() ? 'Activated user' : 'Deactivated user', $user);

        return back()->with('success', "{$user->name} is now {$user->status->label()}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        activity()->log('Users', 'Deleted user', $user, ['name' => $user->name, 'email' => $user->email]);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    private function formOptions(): array
    {
        $roles = Role::orderBy('name')->pluck('name', 'name')->map(fn ($name) => ucfirst($name));

        if (! auth()->user()->isSuperadmin()) {
            $roles->forget(RoleName::Superadmin->value);
        }

        return [
            'roles' => $roles,
            'contentCategories' => ContentCategory::active()->orderBy('name')->pluck('name', 'id'),
            'authorCategories' => AuthorCategory::active()->orderBy('name')->pluck('name', 'id'),
            'countries' => config('countries'),
            'modules' => Permissions::MODULES,
            // Permissions each role already grants, so the extra-permissions card can show them as included.
            'rolePermissions' => Role::with('permissions:id,name')->get()->mapWithKeys(fn (Role $role) => [$role->name => $role->permissions->pluck('name')->all()]),
        ];
    }
}
