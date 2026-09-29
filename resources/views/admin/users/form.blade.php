@php $editing = $user->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit user' : 'Add user'">
    <x-admin.heading :title="$editing ? 'Edit user' : 'Add user'" description="Reviewer role reveals a content-category assignment; author role reveals an author category.">
        <x-slot:actions>
            <x-button :href="route('admin.users.index')" icon="arrow-left">Back to users</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}"
        x-data="{ role: @js(old('role', $user->roles->first()?->name ?? 'author')) }" class="mt-8 max-w-3xl space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel title="Account">
            <div class="grid gap-5 md:grid-cols-2">
                <x-form.input name="name" label="Name" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
                <x-form.input name="phone" label="Phone" :value="$user->phone" />
                <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" :value="$user->status ?? 'Active'" required />
                <x-form.input name="password" type="password" :label="$editing ? 'New password' : 'Password'" :required="! $editing"
                    :hint="$editing ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" autocomplete="new-password" />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" />
            </div>
        </x-admin.panel>

        <x-admin.panel title="Role">
            <div class="space-y-5">
                <x-form.field label="Role" name="role" required>
                    <select name="role" id="role" x-model="role" class="field-input" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-form.field>

                <div x-show="role === 'reviewer'" x-cloak>
                    <x-form.multi-select name="content_category_ids" label="Content categories (reviewer)" :options="$contentCategories"
                        :value="$user->reviewerContentCategories?->pluck('id')->all() ?? []" placeholder="Search and select content categories…"
                        hint="Manuscripts in these categories are auto-assigned to this reviewer." />
                </div>

                <div x-show="role === 'author'" x-cloak>
                    <x-form.multi-select name="author_category_id" label="Author category" :options="$authorCategories" :multiple="false"
                        :value="$user->authorProfile?->author_category_id ? [$user->authorProfile->author_category_id] : []" placeholder="Search and select an author category…" />
                </div>
            </div>
        </x-admin.panel>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save changes' : 'Create user' }}</x-button>
            <x-button :href="route('admin.users.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
