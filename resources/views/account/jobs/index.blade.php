<x-layouts.account title="Jobs Posting" intro="Share openings with the MOS Legis community. Listings are hidden automatically after their expiry date.">
    <div class="flex justify-end"><x-button variant="primary" icon="plus" :href="route('account.jobs.create')">New job posting</x-button></div>
    <x-table class="mt-6" :columns="['Job Title', 'Organisation', 'Location', 'Deadline', 'Status', 'Actions']" :rows="$jobs" empty="You have not posted any jobs yet.">
        @foreach ($jobs as $job)
            <tr>
                <td class="font-medium">{{ $job->job_title }}</td>
                <td>{{ $job->organisation }}</td>
                <td>{{ $job->location }}</td>
                <td class="text-sm">{{ format_date($job->application_deadline) }}</td>
                <td>@if ($job->isExpired())<x-badge>Expired</x-badge>@else<x-status-badge :status="$job->status" />@endif</td>
                <td>
                    <div class="flex items-center gap-3">
                        <x-action-link :href="route('account.jobs.edit', $job)" icon="pencil">Edit</x-action-link>
                        <x-delete-button :action="route('account.jobs.destroy', $job)" />
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $jobs->links() }}
</x-layouts.account>
