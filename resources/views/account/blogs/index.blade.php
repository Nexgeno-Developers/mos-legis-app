<x-layouts.account title="Blogs Posting" intro="Write for the MOS Legis blog. {{ settings()->bool('approvals.blog_author_approval_required') ? 'Posts are reviewed by the editors before they go live.' : 'Published posts go live immediately.' }}">
    <div class="flex justify-end"><x-button variant="primary" icon="plus" :href="route('account.blogs.create')">New blog post</x-button></div>
    <x-table class="mt-6" :columns="['Title', 'Category', 'Status', 'Publish Date', 'Views', 'Actions']" :rows="$blogs" empty="You have not written any posts yet.">
        @foreach ($blogs as $blog)
            <tr>
                <td class="font-medium">{{ $blog->blog_title }}</td>
                <td>{{ $blog->category->category_name }}</td>
                <td><x-status-badge :status="$blog->status" />@if ($blog->status->value === 'Pending')<span class="block text-xs text-muted-foreground">Awaiting approval</span>@endif</td>
                <td class="text-sm">{{ format_date($blog->publish_date) }}</td>
                <td class="text-sm">{{ number_format($blog->views) }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        @if ($blog->status->value === 'Published')<x-action-link :href="route('blogs.show', $blog->slug)" icon="external-link">View</x-action-link>@endif
                        <x-action-link :href="route('account.blogs.edit', $blog)" icon="pencil">Edit</x-action-link>
                        <x-delete-button :action="route('account.blogs.destroy', $blog)" />
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $blogs->links() }}
</x-layouts.account>
