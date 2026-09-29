<x-layouts.admin title="Job Postings">
    <x-admin.heading title="Job Postings" description="Practice-area job listings shown on the public site. Postings auto-expire once the expiry date passes.">
        <x-slot:actions>
            @can('create', App\Models\JobPosting::class)
                <x-button :href="route('admin.job-postings.create')" variant="primary" icon="plus">Add job posting</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar x-data="{ more: {{ request()->hasAny(['published_from', 'deadline_from', 'expiry_from', 'experience']) ? 'true' : 'false' }} }">
        <x-filter.search placeholder="Search title, organisation, location, skills…" />
        <x-filter.select name="practice_area" label="Practice area" :options="$practiceAreas" />
        <x-filter.select name="work_mode" label="Work mode" :options="App\Enums\WorkMode::options()" />
        <x-filter.select name="employment_type" label="Type" :options="App\Enums\EmploymentType::options()" />
        <x-filter.select name="status" label="Status" :options="['Active' => 'Active', 'Inactive' => 'Inactive', 'expired' => 'Expired']" />
        <button type="button" @click="more = !more" class="text-sm text-primary hover:underline" x-text="more ? 'Fewer filters' : 'More filters'"></button>
        <div x-show="more" x-cloak class="flex w-full flex-wrap items-end gap-3">
            <label class="flex flex-col gap-1">
                <span class="label-caps text-xs text-muted-foreground">Experience</span>
                <input type="search" name="experience" value="{{ request('experience') }}" placeholder="e.g. 2–4 years" class="field-input w-44!">
            </label>
            <x-filter.date-range label="Published date" from="published_from" to="published_to" />
            <x-filter.date-range label="Application deadline" from="deadline_from" to="deadline_to" />
            <x-filter.date-range label="Expiry date" from="expiry_from" to="expiry_to" />
        </div>
    </x-filter-bar>

    <x-table :columns="['Job Title', 'Organisation', 'Location', 'Work Mode', 'Employment Type', 'Experience', 'Application Deadline', 'Published Date', 'Status', 'Actions']" :rows="$jobs">
        @foreach ($jobs as $job)
            <tr>
                <td class="font-medium"><a href="{{ route('admin.job-postings.show', $job) }}" class="hover:text-primary">{{ $job->job_title }}</a></td>
                <td>{{ $job->organisation }}</td>
                <td>{{ $job->location }}</td>
                <td class="whitespace-nowrap">{{ $job->work_mode->value }}</td>
                <td class="whitespace-nowrap">{{ $job->employment_type->value }}</td>
                <td class="whitespace-nowrap">{{ $job->experience }}</td>
                <td class="whitespace-nowrap text-sm">{{ format_date($job->application_deadline) }}</td>
                <td class="whitespace-nowrap text-sm">{{ format_date($job->published_date) }}</td>
                <td>
                    @if ($job->isExpired())<x-badge>Expired</x-badge>@else<x-status-badge :status="$job->status" />@endif
                </td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-action-link :href="route('admin.job-postings.show', $job)" icon="eye">View</x-action-link>
                        @can('update', $job)
                            <x-action-link :href="route('admin.job-postings.edit', $job)" icon="pencil">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.job-postings.toggle-status', $job) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$job->isActive() ? 'toggle-right' : 'toggle-left'">{{ $job->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                            </form>
                        @endcan
                        @can('delete', $job)
                            <x-delete-button :action="route('admin.job-postings.destroy', $job)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $jobs->links() }}
</x-layouts.admin>
