<x-layouts.admin title="Profile">
    <x-admin.heading title="Profile" description="Your account details and password." />

    <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-8 grid gap-8 lg:grid-cols-2">
        @csrf
        @method('PUT')

        <x-admin.panel title="Account">
            <div class="space-y-5">
                <x-form.input name="name" label="Name" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
                <x-form.input name="phone" label="Phone" :value="$user->phone" />
                <x-dl :items="['Role' => e($user->primaryRole()?->label()), 'Member since' => format_date($user->created_at)]" />
            </div>
        </x-admin.panel>

        <x-admin.panel title="Change password" description="Leave blank to keep your current password.">
            <div class="space-y-5">
                <x-form.input name="current_password" type="password" label="Current password" autocomplete="current-password" />
                <x-form.input name="password" type="password" label="New password" autocomplete="new-password" />
                <x-form.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" />
            </div>
        </x-admin.panel>

        <div class="lg:col-span-2">
            <x-button type="submit" variant="primary" icon="save">Save profile</x-button>
        </div>
    </form>
</x-layouts.admin>
