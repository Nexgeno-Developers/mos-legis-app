@props(['title', 'eyebrow' => 'Superadmin Console'])
{{-- Centered card layout for admin sign-in and password reset (wireframe admin.login.tsx). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ settings('general.application_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-secondary">
    <x-flash />
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md border border-border bg-card p-8 shadow-sm md:p-10">
            <div class="text-center">
                <img src="{{ asset('images/logo.png') }}" alt="{{ settings('general.application_name') }}" class="mx-auto h-24 w-24 object-contain mix-blend-multiply">
                <p class="label-caps mt-2 text-xs text-primary">{{ $eyebrow }}</p>
                <h1 class="mt-2 font-display text-3xl text-foreground">{{ $title }}</h1>
                <div class="gold-rule mx-auto my-5 max-w-xs"></div>
            </div>
            {{ $slot }}
        </div>
    </main>
</body>
</html>
