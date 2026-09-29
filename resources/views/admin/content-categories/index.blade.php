<x-layouts.admin title="Content Categories">
    <x-admin.heading title="Manuscript Content Categories" description="Article types authors may submit (Research Articles, Case Notes, etc.), each with its own word-limit and guideline.">
        <x-slot:actions>
            @can('content-categories.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'content-category' })">Add content category</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search categories…" />
        <x-filter.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Word Limit', 'Guideline', 'Reviewers', 'Status', 'Created Date', 'Actions']" :rows="$categories">
        @foreach ($categories as $category)
            <tr>
                <td class="font-medium">{{ $category->name }}</td>
                <td class="whitespace-nowrap text-sm">{{ $category->wordLimitLabel() }}</td>
                <td class="max-w-sm text-sm text-muted-foreground">{{ Str::limit($category->guideline, 110) }}</td>
                <td class="text-sm">
                    @if ($category->reviewers_count)
                        {{ $category->reviewers_count }}
                    @else
                        <x-badge tone="warning">None</x-badge>
                    @endif
                </td>
                <td><x-status-badge :status="$category->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($category->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('content-categories.edit')
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'content-category', record: {{ Js::from([
                                'name' => $category->name, 'min_word_limit' => $category->min_word_limit, 'max_word_limit' => $category->max_word_limit,
                                'guideline' => $category->guideline, 'status' => $category->status->value,
                                'action' => route('admin.content-categories.update', $category), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.content-categories.toggle-status', $category) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$category->isActive() ? 'toggle-right' : 'toggle-left'">{{ $category->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                            </form>
                        @endcan
                        @can('content-categories.delete')
                            <x-delete-button :action="route('admin.content-categories.destroy', $category)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $categories->links() }}

    <x-admin.crud-modal name="content-category" title-add="Add content category" title-edit="Edit content category"
        subtitle="Drives the word-count guardrail on the submission wizard and the column axis of the fee matrix."
        :store-url="route('admin.content-categories.store')"
        :defaults="['name' => '', 'min_word_limit' => '', 'max_word_limit' => '', 'guideline' => '', 'status' => 'Active']">
        <x-form.input name="name" label="Name" x-model="form.name" required />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="min_word_limit" type="number" min="0" label="Min word limit" x-model="form.min_word_limit" required />
            <x-form.input name="max_word_limit" type="number" min="0" label="Max word limit" x-model="form.max_word_limit" required />
        </div>
        <x-form.textarea name="guideline" label="Guideline" x-model="form.guideline" required />
        <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>
</x-layouts.admin>
