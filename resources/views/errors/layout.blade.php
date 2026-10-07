@php
    // Error pages must render even when the database is down (500/503), so settings are read defensively
    // and the page uses inline styles only.
    $appName = rescue(fn () => settings('general.application_name'), null, false) ?: config('app.name', 'MOS Legis');
    $logoPath = rescue(fn () => settings('general.application_logo'), null, false);
    $logo = $logoPath ? Storage::disk('public')->url($logoPath) : asset('images/logo-mark.png');
    $code = $code ?? 500;
    $message = $message ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ $appName }}</title>
    <style>
        :root {
            --background: oklch(0.985 0.007 88.6); --foreground: oklch(0.207 0.008 67.4); --card: #fff;
            --primary: oklch(0.489 0.182 24.7); --muted: oklch(0.501 0.023 77.1); --gold: oklch(0.722 0.124 87.7);
            --border: oklch(0.891 0.024 82.1);
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               background: var(--background); color: var(--foreground); font-family: 'Times New Roman', Times, Georgia, serif; }
        .card { width: 100%; max-width: 560px; background: var(--card); border: 1px solid var(--border); border-top: 3px solid var(--primary);
                padding: 40px 32px; text-align: center; }
        .brand { display: inline-flex; align-items: center; gap: 12px; color: var(--primary); text-decoration: none;
                 font-family: 'Crimson Pro', 'Times New Roman', Georgia, serif; font-size: 22px; font-weight: 600; }
        .brand img { height: 48px; width: auto; }
        .code { margin: 28px 0 4px; font-family: 'Crimson Pro', 'Times New Roman', Georgia, serif; font-size: 72px; line-height: 1; color: var(--gold); }
        h1 { margin: 8px 0 12px; font-family: 'Crimson Pro', 'Times New Roman', Georgia, serif; font-size: 28px; font-weight: 600; }
        p { margin: 0 auto; max-width: 420px; color: var(--muted); font-size: 17px; line-height: 1.55; }
        .note { margin-top: 12px; color: var(--foreground); }
        .actions { margin-top: 28px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .btn { display: inline-block; padding: 10px 20px; border: 1px solid var(--border); color: var(--foreground); text-decoration: none;
               font-size: 13px; letter-spacing: .12em; text-transform: uppercase; background: transparent; cursor: pointer; font-family: inherit; }
        .btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn:hover { opacity: .9; }
    </style>
</head>
<body>
    <main class="card">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ $logo }}" alt="">
            <span>{{ $appName }}</span>
        </a>
        <div class="code">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
        @if ($message)<p class="note">{{ $message }}</p>@endif
        <div class="actions">
            <a href="{{ url('/') }}" class="btn primary">Go to home page</a>
            <button type="button" class="btn" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">Go back</button>
        </div>
    </main>
</body>
</html>
