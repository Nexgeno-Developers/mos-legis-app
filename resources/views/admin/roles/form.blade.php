@php
    $editing = $role->exists;
    $isSystem = in_array($role->name, App\Enums\RoleName::values(), true);
    $granted = old('permissions', $granted);
@endphp
<x-layouts.admin :title="$editing ? 'Edit role' : 'Add role'">
    <x-admin.heading :title="$editing ? 'Edit role' : 'Add role'" description="Select the modules this role can access in the Superadmin console.">
        <x-slot:actions>
            <x-button :href="route('admin.roles.index')" icon="arrow-left">Back to roles</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="mt-8 space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="max-w-md">
            <x-form.input name="name" label="Role name" :value="$role->name" required :readonly="$isSystem" />
        </div>

        <x-admin.panel title="Permissions" description="Grant access per module. “View all” on submissions lets the role see every manuscript, not just those assigned to it.">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($modules as $module => $definition)
                    <fieldset class="border border-border p-4" x-data>
                        <legend class="label-caps px-1 text-xs text-foreground">{{ $definition['label'] }}</legend>
                        <div class="mt-1 space-y-2">
                            @foreach ($definition['abilities'] as $ability)
                                @php $permission = "{$module}.{$ability}"; @endphp
                                <label class="flex items-center gap-2 text-base">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $granted, true)) class="h-4 w-4 accent-primary">
                                    {{ Str::of($ability)->replace('-', ' ')->ucfirst() }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
            @error('permissions.*')<p class="mt-3 text-sm text-destructive">{{ $message }}</p>@enderror
        </x-admin.panel>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save role' : 'Create role' }}</x-button>
            <x-button :href="route('admin.roles.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
