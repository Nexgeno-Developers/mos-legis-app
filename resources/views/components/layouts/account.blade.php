@props(['title', 'heading' => null, 'intro' => null])
@php
    $tabs = [
        'account.dashboard' => ['Dashboard', 'gauge'],
        'account.submissions.index' => ['My Submissions', 'inbox'],
        'account.blogs.index' => ['Blogs Posting', 'file-text'],
        'account.jobs.index' => ['Jobs Posting', 'briefcase'],
        'account.payments.index' => ['Certificates & Invoices', 'receipt'],
        'account.plagiarism-checks.index' => ['Plagiarism Checks', 'scan-search'],
        'account.profile.edit' => ['Profile', 'user'],
    ];
@endphp
<x-layouts.site :title="$title">
    <section class="border-b border-border bg-secondary">
        <div class="mx-auto max-w-[1200px] px-6 py-10">
            <p class="label-caps text-sm text-primary">Author Portal</p>
            <h1 class="mt-2 font-display text-4xl text-foreground">{{ $heading ?? $title }}</h1>
            @if ($intro)<p class="measure mt-3 text-lg text-muted-foreground">{{ $intro }}</p>@endif
        </div>
        <nav class="mx-auto flex max-w-[1200px] gap-1 overflow-x-auto px-6" aria-label="Account">
            @foreach ($tabs as $route => [$label, $icon])
                @php $active = request()->routeIs($route, preg_replace('/\.(index|edit)$/', '', $route).'.*'); @endphp
                <a href="{{ route($route) }}" @class([
                    'label-caps -mb-px inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm',
                    'border-primary text-primary' => $active,
                    'border-transparent text-muted-foreground hover:text-foreground' => ! $active,
                ])><x-icon :name="$icon" /> {{ $label }}</a>
            @endforeach
        </nav>
    </section>
    <div class="mx-auto max-w-[1200px] px-6 py-10">
        {{ $slot }}
    </div>
</x-layouts.site>
