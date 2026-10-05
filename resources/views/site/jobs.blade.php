{{-- Title, intro, content and SEO from Admin → Pages ("Job postings" template); filters and listings are dynamic. --}}
<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt" :og-image="$page->og_image">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($jobs as $job)
                @php
                    $experience = is_numeric($job->experience) ? $job->experience.' yrs' : $job->experience;
                    $posted = match (true) {
                        $job->published_date->isToday() => 'Today',
                        $job->published_date->isYesterday() => 'Yesterday',
                        default => format_date($job->published_date),
                    };
                @endphp
                <article class="flex flex-col border border-border bg-card p-5 transition-colors hover:border-gold">
                    <div class="flex items-start justify-between gap-3">
                        <p class="label-caps min-w-0 truncate text-[0.65rem] text-primary">{{ $job->practice_area }}</p>
                        <span class="shrink-0 text-xs text-muted-foreground" title="Posted">{{ $posted }}</span>
                    </div>
                    <h2 class="mt-1.5 font-display text-xl leading-snug">
                        <button type="button" @click="job = {{ $job->id }}" class="text-left hover:text-primary">{{ $job->job_title }}</button>
                    </h2>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-sm text-muted-foreground">
                        <span>{{ $job->organisation }}</span><span aria-hidden="true">·</span>
                        <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="h-3.5 w-3.5" />{{ $job->location }}</span>
                    </p>
                    <ul class="mt-3 mb-4 flex flex-wrap gap-x-4 gap-y-1.5 text-sm">
                        <li class="inline-flex items-center gap-1.5" title="Experience"><x-icon name="briefcase" class="h-3.5 w-3.5 text-muted-foreground" />{{ $experience }}</li>
                        @if ($job->salary)<li class="inline-flex items-center gap-1.5" title="Salary"><x-icon name="badge-indian-rupee" class="h-3.5 w-3.5 text-muted-foreground" />{{ $job->salary }}</li>@endif
                        <li class="inline-flex items-center gap-1.5" title="Employment type · work mode"><x-icon name="clock" class="h-3.5 w-3.5 text-muted-foreground" />{{ $job->employment_type->value }} · {{ $job->work_mode->value }}</li>
                    </ul>
                    <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-border pt-3">
                        <p class="text-xs text-muted-foreground">Apply by <span class="font-semibold text-foreground">{{ format_date($job->application_deadline) }}</span></p>
                        <div class="flex gap-2">
                            <x-button size="sm" @click="job = {{ $job->id }}">Details</x-button>
                            <x-button size="sm" variant="primary" icon="external-link" :href="$job->source_url" target="_blank" rel="noopener">Apply</x-button>
                        </div>
                    </div>
                </article>

                <template x-teleport="body">
                    <div x-show="job === {{ $job->id }}" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-foreground/40 p-4 py-10" @keydown.escape.window="job = null">
                        <div role="dialog" aria-modal="true" aria-label="{{ $job->job_title }}" @click.outside="job = null" class="w-full max-w-2xl border border-border bg-card shadow-xl">
                            <div class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                                <div class="min-w-0">
                                    <p class="label-caps text-[0.65rem] text-primary">{{ $job->practice_area }}</p>
                                    <h2 class="mt-1 font-display text-xl leading-snug">{{ $job->job_title }}</h2>
                                    <p class="text-sm text-muted-foreground">{{ $job->organisation }} · {{ $job->location }}</p>
                                </div>
                                <button type="button" @click="job = null" class="-mt-1 text-2xl leading-none text-muted-foreground hover:text-foreground" aria-label="Close">&times;</button>
                            </div>
                            <div class="px-5 py-4">@include('job-postings._details')</div>
                            <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">
                                <x-button size="sm" variant="ghost" @click="job = null">Close</x-button>
                                <x-button size="sm" variant="primary" icon="external-link" :href="$job->source_url" target="_blank" rel="noopener">Apply now</x-button>
                            </div>
                        </div>
                    </div>
                </template>
            @empty
                <p class="border border-border bg-card p-8 text-muted-foreground md:col-span-2 xl:col-span-3">No open listings match your filters.</p>
            @endforelse
        </div>
        {{ $jobs->links() }}

        @if ($page?->content)<div class="prose-legis mt-12">{!! $page->content !!}</div>@endif

    </div>
</x-layouts.site>
