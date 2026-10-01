@php $viaGoogle = ! empty($social['email_verified']); @endphp
<x-layouts.site title="Create an author account">
    <x-auth-card title="Create your author account" :intro="$viaGoogle
        ? 'Google has confirmed your email. Add a few details to finish creating your account.'
        : 'Register to submit manuscripts and track them. It takes about a minute.'">

        @if ($viaGoogle)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border border-gold/60 bg-secondary px-4 py-3 text-sm">
                <span class="min-w-0 break-all">Signed in with <strong>Google</strong> · {{ $social['email'] }}</span>
                <form method="POST" action="{{ route('register.reset') }}">
                    @csrf
                    <button type="submit" class="text-primary hover:underline">Use another way</button>
                </form>
            </div>
        @else
            @include('auth._social', ['verb' => 'Sign up'])
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf
            <x-form.input name="name" label="Full name" :value="$social['name'] ?? ($orcid['name'] ?? null)" required autocomplete="name" />
            @if ($viaGoogle)
                <x-form.field label="Email" hint="Verified by Google.">
                    <input type="email" value="{{ $social['email'] }}" class="field-input" readonly>
                </x-form.field>
            @else
                <x-form.input name="email" type="email" label="Email" required autocomplete="email" hint="We’ll send a 6-digit code to confirm it." />
            @endif
            <x-form.input name="phone" label="Mobile number" placeholder="10-digit mobile number" autocomplete="tel" hint="Optional — for SMS and WhatsApp updates on your manuscripts." />

            <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" placeholder="Select your category" required
                hint="Decides the publication fee for your manuscripts." />
            <x-form.input name="institution" label="Institution / organisation" required autocomplete="organization" placeholder="e.g. National Law School of India University" />

            <x-orcid-connect :orcid="$orcid['id'] ?? null" removable />

            @unless ($viaGoogle)
                <x-form.input name="password" type="password" label="Password" required autocomplete="new-password" hint="At least 8 characters." />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
            @endunless
            <x-form.checkbox name="terms" label="I accept the Terms & Conditions, Privacy Policy and Publication Ethics." required />
            <x-button type="submit" variant="primary" class="w-full" :icon="$viaGoogle ? 'user-check' : 'mail'">{{ $viaGoogle ? 'Create my account' : 'Send verification code' }}</x-button>
        </form>
        <p class="mt-8 text-center text-sm text-muted-foreground">Already registered? <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a></p>
    </x-auth-card>
</x-layouts.site>
