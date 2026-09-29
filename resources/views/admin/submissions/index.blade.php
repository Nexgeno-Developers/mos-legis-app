<x-layouts.admin title="Submissions">
    <x-admin.heading title="Manuscript Submissions" description="Full pipeline: pending → plagiarism check → peer review → revision → approval → publication, with payments and best-paper flagging.">
        <x-slot:actions>
            @can('create', App\Models\ManuscriptSubmission::class)
                <x-button :href="route('admin.submissions.create')" variant="primary" icon="plus">Add submission</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search ID, title, author…" />
        <x-filter.select name="stage" label="Stage" :options="App\Enums\ManuscriptStage::options()" />
        @can('submissions.view-all')
            <x-filter.select name="reviewer" label="Reviewer" :options="$reviewers" />
        @endcan
        <x-filter.select name="content_category_id" label="Category" :options="$contentCategories" />
        <x-filter.date-range label="Submitted" />
    </x-filter-bar>

    <x-table :columns="['ID', 'Title', 'Stage', 'Waiting', 'Plagiarism', 'Reviewer', 'Actions']" :rows="$submissions" empty="No manuscripts match these filters.">
        @foreach ($submissions as $submission)
            <tr>
                <td class="whitespace-nowrap font-mono text-sm">{{ $submission->reference() }}</td>
                <td class="max-w-md">
                    <a href="{{ route('admin.submissions.show', $submission) }}" class="font-medium hover:text-primary">{{ $submission->title }}</a>
                    <span class="block text-sm text-muted-foreground">{{ $submission->author->name }} · {{ $submission->contentCategory->name }}</span>
                </td>
                <td><x-status-badge :status="$submission->stage" /></td>
                <td class="whitespace-nowrap text-sm" title="{{ format_date($submission->waitingSince(), true) }}">{{ (int) $submission->waitingSince()->diffInDays() }}d</td>
                <td><x-similarity :value="$submission->plagiarism_similarity" :threshold="$threshold" /></td>
                <td class="whitespace-nowrap text-sm">{!! $submission->reviewer ? e($submission->reviewer->name) : '<span class="text-muted-foreground">Unassigned</span>' !!}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-action-link :href="route('admin.submissions.show', $submission)" icon="eye">View</x-action-link>
                        @can('update', $submission)
                            <x-action-link :href="route('admin.submissions.edit', $submission)" icon="pencil">Edit</x-action-link>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $submissions->links() }}
</x-layouts.admin>
