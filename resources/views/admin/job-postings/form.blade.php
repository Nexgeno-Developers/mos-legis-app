@php $editing = $job->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit job posting' : 'Add job posting'">
    <x-admin.heading :title="$editing ? 'Edit job posting' : 'Add job posting'" description="Shown on the public Job Postings page while Active and before the expiry date.">
        <x-slot:actions>
            <x-button :href="route('admin.job-postings.index')" icon="arrow-left">Back to job postings</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.job-postings.update', $job) : route('admin.job-postings.store') }}" class="mx-auto mt-8 max-w-5xl">
        @csrf
        @if ($editing) @method('PUT') @endif
        @include('job-postings._form-fields')
        <div class="mt-8 flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save job posting' : 'Create job posting' }}</x-button>
            <x-button :href="route('admin.job-postings.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
