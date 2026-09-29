<x-layouts.auth title="Reset your password" :eyebrow="$admin ? 'Superadmin Console' : 'Author Portal'">
    <p class="text-center text-base text-muted-foreground">Enter the email on your account and we'll send a reset link.</p>

    @if (session('status'))
        <div class="mt-6 border-l-2 border-gold/60 bg-secondary px-4 py-3 text-base text-foreground">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ $admin ? route('admin.password.email') : route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <x-form.input name="email" type="email" label="Email" placeholder="you@example.com" required autofocus />
        <x-button type="submit" variant="primary" class="w-full" icon="key-round">Send reset link</x-button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ $admin ? route('admin.login') : route('login') }}" class="text-muted-foreground hover:text-foreground">Back to sign in</a>
    </p>
</x-layouts.auth>
