<x-layouts.site title="Create an author account">
    <x-auth-card title="Create your author account" :intro="$social ? 'Confirm your details and email address to finish signing up with '.ucfirst($social['provider']).'.' : 'Register to submit manuscripts. We will email you a one-time code to verify your address.'">
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf
            <x-form.input name="name" label="Full name" :value="$social['name'] ?? null" required autocomplete="name" />
            <x-form.input name="email" type="email" label="Email" required autocomplete="email" />
            <x-form.input name="phone" label="Mobile number" placeholder="10-digit mobile number" autocomplete="tel" hint="Used for SMS and WhatsApp updates on your manuscripts." />
            <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" placeholder="Select (you can set this later)" />
            @unless ($social)
                <x-form.input name="password" type="password" label="Password" required autocomplete="new-password" hint="At least 8 characters." />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
            @endunless
            <x-form.checkbox name="terms" label="I accept the Terms & Conditions, Privacy Policy and Publication Ethics." />
            <x-button type="submit" variant="primary" class="w-full" icon="mail">Send verification code</x-button>
        </form>
        @unless ($social) @include('auth._social') @endunless
        <p class="mt-8 text-center text-sm text-muted-foreground">Already registered? <a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a></p>
    </x-auth-card>
</x-layouts.site>
