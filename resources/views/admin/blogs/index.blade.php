<x-layouts.admin title="Blogs">
    <x-admin.heading title="Blogs" description="Editorial and contributor blog posts shown on the public site.">
        <x-slot:actions>
            @can('create', App\Models\Blog::class)
                <x-button :href="route('admin.blogs.create')" variant="primary" icon="plus">Add blog</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    @if ($pendingCount)
        <a href="{{ route('admin.blogs.index', ['status' => 'Pending']) }}" class="mt-6 flex items-center gap-2 border-l-2 border-warning bg-card px-4 py-3 text-base hover:bg-secondary">
            <x-icon name="clock" class="text-warning" /> {{ $pendingCount }} author {{ Str::plural('post', $pendingCount) }} awaiting approval
        </a>
    @endif

    <x-filter-bar>
        <x-filter.search placeholder="Search title, slug, author…" />
        <x-filter.select name="category_id" label="Category" :options="$categories" />
        <x-filter.select name="status" label="Status" :options="App\Enums\BlogStatus::options()" />
        <x-filter.date-range label="Publish date" />
    </x-filter-bar>

    <x-table :columns="['Title', 'Category', 'Author', 'Status', 'Publish Date', 'Views', 'Featured', 'Updated', 'Actions']" :rows="$blogs">
        @foreach ($blogs as $blog)
            <tr>
                <td class="max-w-xs">
                    <span class="font-medium">{{ $blog->blog_title }}</span>
                    <span class="block font-mono text-xs text-muted-foreground">/{{ $blog->slug }}</span>
                </td>
                <td>{{ $blog->category->category_name }}</td>
                <td class="whitespace-nowrap">{{ $blog->author_name }}</td>
                <td><x-status-badge :status="$blog->status" /></td>
                <td class="whitespace-nowrap text-sm">{{ format_date($blog->publish_date) }}</td>
                <td class="text-sm">{{ number_format($blog->views) }}</td>
                <td>@if ($blog->featured_post)<x-icon name="star" class="text-gold" />@else<span class="text-muted-foreground">—</span>@endif</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($blog->updated_at) }}</td>
                <td>
                    <x-row-actions>
                        @can('update', $blog)
                            <x-action-link :href="route('admin.blogs.edit', $blog)" icon="pencil">Edit</x-action-link>
                            <form method="POST" action="{{ route('admin.blogs.toggle-status', $blog) }}">
                                @csrf @method('PATCH')
                                <x-action-link type="submit" :icon="$blog->status->value === 'Published' ? 'eye-off' : 'check'">
                                    {{ match ($blog->status->value) { 'Published' => 'Deactivate', 'Pending' => 'Approve', default => 'Activate' } }}
                                </x-action-link>
                            </form>
                        @endcan
                        @can('create', App\Models\Blog::class)
                            <form method="POST" action="{{ route('admin.blogs.duplicate', $blog) }}">
                                @csrf
                                <x-action-link type="submit" icon="copy">Duplicate</x-action-link>
                            </form>
                        @endcan
                        @can('delete', $blog)
                            <x-delete-button :action="route('admin.blogs.destroy', $blog)" />
                        @endcan
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $blogs->links() }}
</x-layouts.admin>
