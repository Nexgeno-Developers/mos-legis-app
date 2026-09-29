<x-layouts.admin title="Blog Categories">
    <x-admin.heading title="Blog Categories" description="Taxonomy used to organise published blogs across the admin console and the public blog listing.">
        <x-slot:actions>
            @can('blog-categories.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'blog-category' })">Add blog category</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search categories…" />
        <x-filter.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Category Name', 'Slug', 'Blogs Count', 'Status', 'Created Date', 'Actions']" :rows="$categories">
        @foreach ($categories as $category)
            <tr>
                <td class="font-medium">{{ $category->category_name }}</td>
                <td class="font-mono text-sm">{{ $category->slug }}</td>
                <td>{{ $category->blogs_count }}</td>
                <td><x-status-badge :status="$category->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($category->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('blog-categories.edit')
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'blog-category', record: {{ Js::from([
                                'category_name' => $category->category_name, 'slug' => $category->slug, 'description' => (string) $category->description,
                                'status' => $category->status->value, 'action' => route('admin.blog-categories.update', $category), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.blog-categories.toggle-status', $category) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$category->isActive() ? 'toggle-right' : 'toggle-left'">{{ $category->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                            </form>
                        @endcan
                        @can('blog-categories.create')
                            <form method="POST" action="{{ route('admin.blog-categories.duplicate', $category) }}">
                                @csrf
                                <x-action-link type="submit" icon="copy">Duplicate</x-action-link>
                            </form>
                        @endcan
                        @can('blog-categories.delete')
                            <x-delete-button :action="route('admin.blog-categories.destroy', $category)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $categories->links() }}

    <x-admin.crud-modal name="blog-category" title-add="Add blog category" title-edit="Edit blog category"
        subtitle="Categories appear as a filter on the public blog listing."
        :store-url="route('admin.blog-categories.store')" :defaults="['category_name' => '', 'slug' => '', 'description' => '', 'status' => 'Active']">
        <x-form.input name="category_name" label="Category name" x-model="form.category_name" required />
        <x-form.input name="slug" label="Slug" x-model="form.slug" hint="Leave blank to generate from the name." />
        <x-form.textarea name="description" label="Description" x-model="form.description" rows="3" />
        <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>
</x-layouts.admin>
