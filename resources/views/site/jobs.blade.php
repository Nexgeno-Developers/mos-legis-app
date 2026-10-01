<x-layouts.site title="Job Postings" description="Legal job listings from firms, chambers and institutions.">
    <x-page-header eyebrow="Notice Board" title="Job Postings" intro="Vacancies submitted by firms, chambers and institutions. MOS Legis publishes listings as a service to readers and takes no part in recruitment." />
    <div class="mx-auto max-w-[1200px] px-4 pt-2 pb-10 sm:px-6" x-data="{ job: null }">
        <x-filter-bar>
            <x-filter.search placeholder="Title, organisation or skill…" />
            <x-filter.text name="location" label="Location" />
            <x-filter.select name="practice_area" label="Practice area" :options="$practiceAreas" />
            <x-filter.select name="work_mode" label="Work mode" :options="App\Enums\WorkMode::options()" />
            <x-filter.select name="employment_type" label="Employment type" :options="App\Enums\EmploymentType::options()" />
            <x-filter.text name="experience" label="Experience" placeholder="e.g. 2–4" />
            <x-filter.text name="deadline_after" label="Deadline after" type="date" />
        </x-filter-bar>

        <div class="grid gap-px bg-border md:grid-cols-2">
            @forelse ($jobs as $job)
                <article class="flex flex-col bg-card p-6">
                    <p class="label-caps text-xs text-muted-foreground">{{ $job->practice_area }} · {{ $job->employment_type->value }} · {{ $job->work_mode->value }}</p>
                    <h2 class="mt-2 font-display text-2xl">{{ $job->job_title }}</h2>
                    <p class="text-muted-foreground">{{ $job->organisation }} — {{ $job->location }}</p>
                    <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div><dt class="text-muted-foreground">Experience</dt><dd>{{ $job->experience }}</dd></div>
                        <div><dt class="text-muted-foreground">Salary</dt><dd>{{ $job->salary ?? '—' }}</dd></div>
                        <div><dt class="text-muted-foreground">Apply by</dt><dd>{{ format_date($job->application_deadline) }}</dd></div>
                        <div><dt class="text-muted-foreground">Posted</dt><dd>{{ format_date($job->published_date) }}</dd></div>
                    </dl>
                    <div class="mt-5 flex gap-3">
                        <x-button size="sm" icon="eye" @click="job = {{ $job->id }}">View details</x-button>
                        <x-button size="sm" variant="primary" icon="external-link" :href="$job->source_url" target="_blank" rel="noopener">Apply now</x-button>
                    </div>
                </article>

                <template x-teleport="body">
                    <div x-show="job === {{ $job->id }}" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-foreground/40 p-4 py-12" @keydown.escape.window="job = null">
                        <div role="dialog" aria-modal="true" @click.outside="job = null" class="w-full max-w-3xl border border-border bg-card shadow-xl">
                            <div class="flex items-start justify-between gap-6 border-b border-border p-7">
                                <div>
                                    <h2 class="font-display text-2xl">{{ $job->job_title }}</h2>
                                    <p class="text-muted-foreground">{{ $job->organisation }} — {{ $job->location }}</p>
                                </div>
                                <button type="button" @click="job = null" class="text-2xl text-muted-foreground" aria-label="Close">&times;</button>
                            </div>
                            <div class="p-7">@include('job-postings._details')</div>
                            <div class="flex justify-end gap-3 border-t border-border p-7">
                                <x-button variant="ghost" @click="job = null">Close</x-button>
                                <x-button variant="primary" icon="external-link" :href="$job->source_url" target="_blank" rel="noopener">Apply now</x-button>
                            </div>
                        </div>
                    </div>
                </template>
            @empty
                <p class="bg-card p-8 text-muted-foreground md:col-span-2">No open listings match your filters.</p>
            @endforelse
        </div>
        {{ $jobs->links() }}

        <div class="mt-12 border border-border bg-secondary p-6">
            <p class="label-caps text-sm text-primary">For Employers</p>
            <p class="mt-1 font-display text-xl">Post a Vacancy</p>
            <p class="mt-2 text-muted-foreground">Registered members can post listings from their account. <a href="{{ auth()->user()?->isAuthor() ? route('account.jobs.create') : route('register') }}" class="text-primary hover:underline">Post a job</a></p>
        </div>
    </div>
</x-layouts.site>
