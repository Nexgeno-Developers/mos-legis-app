<x-layouts.admin title="Roles & Permissions">
    <x-admin.heading title="Roles & Permissions" description="Superadmin has all access by default. Reviewer and Author are fixed system roles; add custom roles for editorial staff.">
        <x-slot:actions>
            @can('roles.create')
                <x-button :href="route('admin.roles.create')" variant="primary" icon="plus">Add role</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search roles…" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Permissions', 'Users', 'Created Date', 'Actions']" :rows="$roles">
        @foreach ($roles as $role)
            <tr>
                <td class="font-medium capitalize">
                    {{ $role->name }}
                    @if (in_array($role->name, $systemRoles, true))<x-badge class="ml-2">System</x-badge>@endif
                </td>
                <td class="text-sm text-muted-foreground">
                    @if ($role->name === 'superadmin') All access
                    @elseif ($role->name === 'author') Website only
                    @else {{ $role->permissions_count }} permissions
                    @endif
                </td>
                <td>{{ $role->users_count }}</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($role->created_at) }}</td>
                <td>
                    <x-row-actions>
                        @if ($role->name !== 'superadmin' && $role->name !== 'author')
                            @can('roles.edit')
                                <x-action-link :href="route('admin.roles.edit', $role)" icon="pencil">Edit</x-action-link>
                            @endcan
                        @endif
                        @if (! in_array($role->name, $systemRoles, true))
                            @can('roles.delete')
                                <x-delete-button :action="route('admin.roles.destroy', $role)" />
                            @endcan
                        @endif
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $roles->links() }}
</x-layouts.admin>
