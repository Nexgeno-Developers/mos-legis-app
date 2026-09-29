<x-layouts.admin title="Blog Tags">
    <x-admin.heading title="Blog Tags" description="Free-form labels attached to blog posts, used for related-content filtering.">
        <x-slot:actions>
            @can('blog-tags.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'blog-tag' })">Add blog tag</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search tags…" />
        <x-filter.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Tag Name', 'Slug', 'Blogs Count', 'Status', 'Created Date', 'Actions']" :rows="$tags">
        @foreach ($tags as $tag)
            <tr>
                <td class="font-medium">{{ $tag->tag_name }}</td>
                <td class="font-mono text-sm">{{ $tag->slug }}</td>
                <td>{{ $tag->blogs_count }}</td>
                <td><x-status-badge :status="$tag->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($tag->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('blog-tags.edit')
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'blog-tag', record: {{ Js::from([
                                'tag_name' => $tag->tag_name, 'slug' => $tag->slug, 'status' => $tag->status->value,
                                'action' => route('admin.blog-tags.update', $tag), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.blog-tags.toggle-status', $tag) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$tag->isActive() ? 'toggle-right' : 'toggle-left'">{{ $tag->isActive() ? 'Deactivate' : 'Activate' }}</x-action-link>
                            </form>
                        @endcan
                        @can('blog-tags.create')
                            <form method="POST" action="{{ route('admin.blog-tags.duplicate', $tag) }}">
                                @csrf
                                <x-action-link type="submit" icon="copy">Duplicate</x-action-link>
                            </form>
                        @endcan
                        @can('blog-tags.delete')
                            <x-delete-button :action="route('admin.blog-tags.destroy', $tag)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $tags->links() }}

    <x-admin.crud-modal name="blog-tag" title-add="Add blog tag" title-edit="Edit blog tag"
        subtitle="Tags are selected on the blog editor and shown on the public post."
        :store-url="route('admin.blog-tags.store')" :defaults="['tag_name' => '', 'slug' => '', 'status' => 'Active']">
        <x-form.input name="tag_name" label="Tag name" x-model="form.tag_name" required />
        <x-form.input name="slug" label="Slug" x-model="form.slug" hint="Leave blank to generate from the name." />
        <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>
</x-layouts.admin>
