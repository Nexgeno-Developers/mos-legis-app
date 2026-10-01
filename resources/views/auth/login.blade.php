<x-layouts.site title="Author sign in">
    <x-auth-card title="Sign in" intro="Sign in to submit manuscripts, track their progress, pay fees and download certificates.">
        @include('auth._social', ['verb' => 'Sign in'])
        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
            @csrf
            <x-form.input name="email" type="email" label="Email" required autofocus autocomplete="username" />
            <x-form.input name="password" type="password" label="Password" required autocomplete="current-password" />
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-muted-foreground"><input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-primary"> Keep me signed in</label>
                <a href="{{ route('password.request') }}" class="text-primary hover:underline">Forgot password?</a>
            </div>
            <x-button type="submit" variant="primary" class="w-full" icon="log-in">Sign in</x-button>
        </form>
        <p class="mt-8 text-center text-sm text-muted-foreground">New to {{ settings('general.application_name') }}? <a href="{{ route('register') }}" class="text-primary hover:underline">Create an author account</a></p>
    </x-auth-card>
</x-layouts.site>
