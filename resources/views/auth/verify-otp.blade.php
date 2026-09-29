<x-layouts.site title="Verify your email">
    <x-auth-card title="Enter the one-time password" :intro="'We sent a 6-digit code to '.$email.'. It expires in 10 minutes.'">
        <form method="POST" action="{{ route('register.verify.store') }}" class="mt-6 space-y-5">
            @csrf
            <x-form.input name="otp" label="Verification code" placeholder="000000" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus class="font-mono" />
            <x-button type="submit" variant="primary" class="w-full" icon="shield-check">Verify and create account</x-button>
        </form>
        <form method="POST" action="{{ route('register.resend') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-primary hover:underline">Resend code</button>
            <span class="text-sm text-muted-foreground"> · </span>
            <a href="{{ route('register') }}" class="text-sm text-muted-foreground hover:text-foreground">Change email</a>
        </form>
    </x-auth-card>
</x-layouts.site>
