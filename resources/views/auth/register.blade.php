<x-layouts.site title="Create an author account">
    <x-auth-card title="Create your author account" :intro="$social
        ? 'You signed in with '.($social['provider'] === 'orcid' ? 'ORCID' : 'Google').'. Confirm your details and email address to finish creating your account.'
        : 'Register to submit manuscripts. Sign up with Google or ORCID, or with your email and a one-time verification code.'">

        @if ($social)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border border-gold/60 bg-secondary px-4 py-3 text-sm">
                <span>
                    Connected: <strong>{{ $social['provider'] === 'orcid' ? 'ORCID' : 'Google' }}</strong>
                    @if (! empty($social['orcid']))<span class="font-mono"> · {{ $social['orcid'] }}</span>@endif
                </span>
                <form method="POST" action="{{ route('register.reset') }}">
                    @csrf
                    <button type="submit" class="text-primary hover:underline">Start over</button>
                </form>
            </div>
        @else
            @include('auth._social', ['verb' => 'Sign up'])
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf
            <x-form.input name="name" label="Full name" :value="$social['name'] ?? null" required autocomplete="name" />
            <x-form.input name="email" type="email" label="Email" required autocomplete="email" :hint="$social ? 'We will send a one-time code to confirm this address.' : null" />
            <x-form.input name="phone" label="Mobile number" placeholder="10-digit mobile number" autocomplete="tel" hint="Used for SMS and WhatsApp updates on your manuscripts." />
            <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" placeholder="Select (you can set this later)" />
            @unless ($social)
                <x-form.input name="password" type="password" label="Password" required autocomplete="new-password" hint="At least 8 characters." />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
            @endunless
            <x-form.checkbox name="terms" label="I accept the Terms & Conditions, Privacy Policy and Publication Ethics." />
            <x-button type="submit" variant="primary" class="w-full" icon="mail">Send verification code</x-button>
        </form>
        <p class="mt-8 text-center text-sm text-muted-foreground">Already registered? <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a></p>
    </x-auth-card>
</x-layouts.site>
