{{-- Read-only job detail blocks (admin view page and the public detail modal). Expects $job. Empty optional fields are hidden. --}}
<x-dl :items="array_filter([
    'Work mode' => e($job->work_mode->value),
    'Employment type' => e($job->employment_type->value),
    'Experience' => e($job->experience),
    'Practice area' => e($job->practice_area),
    'Salary' => $job->salary ? e($job->salary) : null,
    'Application deadline' => format_date($job->application_deadline),
    'Posted' => format_date($job->published_date),
])" class="lg:grid-cols-4" />

<div class="mt-8 grid gap-8 md:grid-cols-2">
    @foreach (array_filter(['Summary' => $job->summary, 'Responsibilities' => $job->responsibilities, 'Qualifications' => $job->qualifications, 'Required skills' => $job->required_skills]) as $heading => $text)
        <section>
            <h3 class="label-caps text-sm text-primary">{{ $heading }}</h3>
            <p class="mt-2 whitespace-pre-line text-base">{{ $text }}</p>
        </section>
    @endforeach
</div>

<section class="mt-8 border-t border-border pt-6">
    <h3 class="label-caps text-sm text-primary">How to apply</h3>
    <x-dl class="mt-3" :items="array_filter([
        'Apply at' => '<a class=\'text-primary hover:underline break-all\' target=\'_blank\' rel=\'noopener\' href=\''.e($job->source_url).'\'>'.e($job->source_name ?: (parse_url($job->source_url, PHP_URL_HOST) ?: $job->source_url)).'</a>',
        // Older listings may still carry a separate application email / URL.
        'Also apply via' => $job->application_email_url
            ? ($job->application_method?->value === 'Email'
                ? '<a class=\'text-primary hover:underline\' href=\'mailto:'.e($job->application_email_url).'\'>'.e($job->application_email_url).'</a>'
                : '<a class=\'text-primary hover:underline break-all\' target=\'_blank\' rel=\'noopener\' href=\''.e($job->application_email_url).'\'>'.e($job->application_email_url).'</a>')
            : null,
    ])" />
</section>
