{{-- Read-only job details (admin view page and the public popup), compact. Expects $job. Empty optional fields are hidden. --}}
@php
    $facts = array_filter([
        'Experience' => is_numeric($job->experience) ? $job->experience.' yrs' : $job->experience,
        'Salary' => $job->salary,
        'Type' => $job->employment_type->value.' · '.$job->work_mode->value,
        'Apply by' => format_date($job->application_deadline),
        'Posted' => format_date($job->published_date),
    ]);
    $sections = array_filter([
        'Summary' => $job->summary,
        'Responsibilities' => $job->responsibilities,
        'Qualifications' => $job->qualifications,
        'Required skills' => $job->required_skills,
    ]);
@endphp
<dl @class(['grid grid-cols-2 gap-px border border-border bg-border', 'sm:grid-cols-3' => count($facts) === 3, 'sm:grid-cols-4' => count($facts) === 4, 'sm:grid-cols-5' => count($facts) >= 5])>
    @foreach ($facts as $label => $value)
        <div class="bg-secondary/50 px-3 py-2">
            <dt class="label-caps text-[0.6rem] text-muted-foreground">{{ $label }}</dt>
            <dd class="mt-0.5 text-sm font-medium text-foreground">{{ $value }}</dd>
        </div>
    @endforeach
</dl>

<div class="mt-4 space-y-4">
    @foreach ($sections as $heading => $text)
        <section>
            <h3 class="label-caps text-xs text-primary">{{ $heading }}</h3>
            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-foreground">{{ $text }}</p>
        </section>
    @endforeach
</div>

<p class="mt-4 border-t border-border pt-3 text-sm text-muted-foreground">
    Apply at
    <a class="break-all text-primary hover:underline" target="_blank" rel="noopener" href="{{ $job->source_url }}">{{ $job->source_name ?: (parse_url($job->source_url, PHP_URL_HOST) ?: $job->source_url) }}</a>
    @if ($job->application_email_url)
        {{-- Older listings may still carry a separate application email / URL. --}}
        · or
        <a class="break-all text-primary hover:underline" @if ($job->application_method?->value !== 'Email') target="_blank" rel="noopener" @endif
            href="{{ $job->application_method?->value === 'Email' ? 'mailto:'.$job->application_email_url : $job->application_email_url }}">{{ $job->application_email_url }}</a>
    @endif
</p>
