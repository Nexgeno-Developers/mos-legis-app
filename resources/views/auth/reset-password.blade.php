<x-layouts.auth title="Choose a new password" eyebrow="Account security">
    <form method="POST" action="{{ route('password.update') }}" class="mt-4 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-form.input name="email" type="email" label="Email" :value="$email" required autocomplete="username" />
        <x-form.input name="password" type="password" label="New password" required autocomplete="new-password" hint="At least 8 characters." />
        <x-form.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
        <x-button type="submit" variant="primary" class="w-full" icon="key-round">Reset password</x-button>
    </form>
</x-layouts.auth>
