{{-- Read-only job detail blocks (admin view page and the public detail modal). Expects $job. --}}
<x-dl :items="[
    'Work mode' => e($job->work_mode->value),
    'Employment type' => e($job->employment_type->value),
    'Experience' => e($job->experience),
    'Practice area' => e($job->practice_area),
    'Salary' => e($job->salary),
    'Application deadline' => format_date($job->application_deadline),
    'Published date' => format_date($job->published_date),
    'Expiry date' => format_date($job->expiry_date),
]" class="lg:grid-cols-4" />

<div class="mt-8 grid gap-8 md:grid-cols-2">
    @foreach (['Summary' => $job->summary, 'Responsibilities' => $job->responsibilities, 'Qualifications' => $job->qualifications, 'Required skills' => $job->required_skills] as $heading => $text)
        <section>
            <h3 class="label-caps text-sm text-primary">{{ $heading }}</h3>
            <p class="mt-2 whitespace-pre-line text-base">{{ $text }}</p>
        </section>
    @endforeach
</div>

<section class="mt-8 border-t border-border pt-6">
    <h3 class="label-caps text-sm text-primary">Application & source</h3>
    <x-dl class="mt-3" :items="[
        'Application method' => e($job->application_method->value),
        'Apply via' => $job->application_method->value === 'Email'
            ? '<a class=\'text-primary hover:underline\' href=\'mailto:'.e($job->application_email_url).'\'>'.e($job->application_email_url).'</a>'
            : '<a class=\'text-primary hover:underline\' target=\'_blank\' rel=\'noopener\' href=\''.e($job->application_email_url).'\'>'.e($job->application_email_url).'</a>',
        'Source' => '<a class=\'text-primary hover:underline\' target=\'_blank\' rel=\'noopener\' href=\''.e($job->source_url).'\'>'.e($job->source_name).'</a>',
    ]" />
</section>
