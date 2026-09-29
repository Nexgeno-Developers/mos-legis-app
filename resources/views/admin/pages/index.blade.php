<x-layouts.admin title="Pages">
    <x-admin.heading title="Pages" description="CMS pages rendered on the public site. The template decides which extra fields a page carries (team members, patron entries, contact details…).">
        <x-slot:actions>
            @can('pages.create')
                <x-button :href="route('admin.pages.create')" variant="primary" icon="plus">Add page</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search name or slug…" />
        <x-filter.select name="template" label="Template" :options="$templates" />
        <x-filter.select name="status" label="Status" :options="App\Enums\PublishStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Slug', 'Template', 'Status', 'Created Date', 'Actions']" :rows="$pages">
        @foreach ($pages as $page)
            <tr>
                <td class="font-medium">{{ $page->title }}</td>
                <td class="font-mono text-sm">/{{ $page->slug }}</td>
                <td><x-badge tone="gold">{{ $templates[$page->template->value] }}</x-badge></td>
                <td><x-status-badge :status="$page->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($page->created_at) }}</td>
                <td>
                    <div class="flex flex-wrap items-center gap-3">
                        @can('pages.edit')
                            <x-action-link :href="route('admin.pages.edit', $page)" icon="pencil">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.pages.toggle-status', $page) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$page->status->value === 'Published' ? 'eye-off' : 'eye'">{{ $page->status->value === 'Published' ? 'Unpublish' : 'Publish' }}</x-action-link>
                            </form>
                        @endcan
                        @can('pages.create')
                            <form method="POST" action="{{ route('admin.pages.duplicate', $page) }}">
                                @csrf
                                <x-action-link type="submit" icon="copy">Duplicate</x-action-link>
                            </form>
                        @endcan
                        @can('pages.delete')
                            <x-delete-button :action="route('admin.pages.destroy', $page)" />
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $pages->links() }}
</x-layouts.admin>
