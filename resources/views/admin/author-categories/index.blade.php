<x-layouts.admin title="Author Categories">
    <x-admin.heading title="Manuscript Author Categories" description="The author categories offered during submission (student, scholar, advocate, etc.), used to drive the fee matrix.">
        <x-slot:actions>
            @can('author-categories.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'author-category' })">Add author category</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search categories…" />
        <x-filter.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Status', 'Fee cells', 'Created Date', 'Actions']" :rows="$categories">
        @foreach ($categories as $category)
            <tr>
                <td class="font-medium">{{ $category->name }}</td>
                <td><x-status-badge :status="$category->status" /></td>
                <td class="text-sm text-muted-foreground">{{ $category->fees_count }}</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($category->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('author-categories.edit')
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'author-category', record: {{ Js::from([
                                'name' => $category->name, 'status' => $category->status->value,
                                'action' => route('admin.author-categories.update', $category), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.author-categories.toggle-status', $category) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$category->isActive() ? 'toggle-right' : 'toggle-left'">{{ $category->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                            </form>
                        @endcan
                        @can('author-categories.delete')
                            <x-delete-button :action="route('admin.author-categories.destroy', $category)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $categories->links() }}

    <x-admin.crud-modal name="author-category" title-add="Add author category" title-edit="Edit author category"
        subtitle="Shown to authors during manuscript submission and to admins in the fee matrix."
        :store-url="route('admin.author-categories.store')" :defaults="['name' => '', 'status' => 'Active']">
        <x-form.input name="name" label="Name" x-model="form.name" required />
        <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>
</x-layouts.admin>
