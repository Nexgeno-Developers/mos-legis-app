<x-layouts.admin :title="$job->job_title">
    <x-admin.heading :title="$job->job_title" :description="$job->organisation.' — '.$job->location">
        <x-slot:actions>
            @can('update', $job)
                @if ($job->isPendingApproval())
                    <form method="POST" action="{{ route('admin.job-postings.approve', $job) }}">
                        @csrf @method('PATCH')
                        <x-button type="submit" variant="primary" icon="check">Approve</x-button>
                    </form>
                @endif
                <x-button :href="route('admin.job-postings.edit', $job)" icon="pencil">Edit</x-button>
            @endcan
            <x-button :href="route('admin.job-postings.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
        @if ($job->isPendingApproval())<x-badge tone="warning">Awaiting approval</x-badge>@elseif ($job->isExpired())<x-badge>Expired</x-badge>@else<x-status-badge :status="$job->status" />@endif
        <span>Posted by {{ $job->user->name }}</span>
    </div>

    <x-admin.panel class="mt-6">
        @include('job-postings._details')
    </x-admin.panel>
</x-layouts.admin>
