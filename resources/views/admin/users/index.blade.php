<x-layouts.admin title="Users">
    <x-admin.heading title="Users" description="Superadmin, reviewer and author accounts. Reviewers are matched to content categories; authors carry a single author category.">
        <x-slot:actions>
            @can('create', App\Models\User::class)
                <x-button :href="route('admin.users.create')" variant="primary" icon="plus">Add user</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search by name, email or phone…" />
        <x-filter.select name="role" label="Role" :options="$roles" />
        <x-filter.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Email', 'Phone', 'Role', 'Status', 'Created Date', 'Actions']" :rows="$users">
        @foreach ($users as $user)
            <tr>
                <td class="font-medium">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? '—' }}</td>
                <td>
                    @foreach ($user->roles as $role)
                        <x-badge :tone="$role->name === 'superadmin' ? 'primary' : ($role->name === 'reviewer' ? 'info' : 'gold')">{{ $role->name }}</x-badge>
                    @endforeach
                </td>
                <td><x-status-badge :status="$user->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($user->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('update', $user)
                            <x-action-link :href="route('admin.users.edit', $user)" icon="pencil">Edit</x-action-link>
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf @method('PATCH')
                                    <x-action-link type="submit" :icon="$user->isActive() ? 'toggle-right' : 'toggle-left'">{{ $user->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                                </form>
                            @endunless
                        @endcan
                        @can('delete', $user)
                            <x-delete-button :action="route('admin.users.destroy', $user)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $users->links() }}
</x-layouts.admin>
