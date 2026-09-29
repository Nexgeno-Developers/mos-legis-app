<x-layouts.auth title="Sign in">
    <p class="text-center text-base text-muted-foreground">Sign in with your {{ settings('general.application_name') }} admin account.</p>

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
        @csrf
        <x-form.input name="email" type="email" label="Email" placeholder="you@moslegis.com" required autofocus autocomplete="username" />
        <x-form.input name="password" type="password" label="Password" placeholder="••••••••" required autocomplete="current-password" />

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-muted-foreground">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-primary"> Keep me signed in
            </label>
            <a href="{{ route('admin.password.request') }}" class="text-primary hover:underline">Forgot password?</a>
        </div>

        <x-button type="submit" variant="primary" class="w-full" icon="log-in">Sign in</x-button>
    </form>
</x-layouts.auth>
