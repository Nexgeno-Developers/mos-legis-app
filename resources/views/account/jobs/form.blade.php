<x-layouts.account :title="$job->exists ? 'Edit job posting' : 'New job posting'">
    <form method="POST" action="{{ $job->exists ? route('account.jobs.update', $job) : route('account.jobs.store') }}" class="max-w-5xl">
        @csrf
        @if ($job->exists) @method('PUT') @endif
        @include('job-postings._form-fields')
        <div class="mt-8 flex gap-3">
            <x-button type="submit" variant="primary" icon="save">Save job posting</x-button>
            <x-button :href="route('account.jobs.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.account>
