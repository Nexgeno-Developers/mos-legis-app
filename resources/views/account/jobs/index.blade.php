<x-layouts.account title="Jobs Posting" intro="Share openings with the MOS Legis community. Listings are hidden automatically after their expiry date.">
    @if (settings()->bool('approvals.job_author_approval_required'))
        <p class="mb-4 flex items-center gap-2 text-sm text-muted-foreground"><x-icon name="info" /> New job postings are listed on the job board once an admin approves them.</p>
    @endif
    <div class="flex justify-end"><x-button variant="primary" icon="plus" :href="route('account.jobs.create')">New job posting</x-button></div>
    <x-table class="mt-6" :columns="['Job Title', 'Organisation', 'Location', 'Deadline', 'Status', 'Actions']" :rows="$jobs" empty="You have not posted any jobs yet.">
        @foreach ($jobs as $job)
            <tr>
                <td class="font-medium">{{ $job->job_title }}</td>
                <td>{{ $job->organisation }}</td>
                <td>{{ $job->location }}</td>
                <td class="text-sm">{{ format_date($job->application_deadline) }}</td>
                <td>@if ($job->isPendingApproval())<x-badge tone="warning">Awaiting approval</x-badge>@elseif ($job->isExpired())<x-badge>Expired</x-badge>@else<x-status-badge :status="$job->status" />@endif</td>
                <td>
                    <x-row-actions>
                        <x-action-link :href="route('account.jobs.edit', $job)" icon="pencil">Edit</x-action-link>
                        <x-delete-button :action="route('account.jobs.destroy', $job)" />
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $jobs->links() }}
</x-layouts.account>
