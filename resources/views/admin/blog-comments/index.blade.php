<x-layouts.admin title="Blog Comments">
    <x-admin.heading title="Blog Comments" description="Moderation queue for comments left on published blog posts, including threaded replies.">
        <x-slot:actions>
            @can('blog-comments.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'blog-comment' })">Add comment</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search name, email, comment…" />
        <x-filter.text name="blog" label="Blog" placeholder="Blog title" />
        <x-filter.select name="status" label="Status" :options="App\Enums\CommentStatus::options()" />
    </x-filter-bar>

    <x-table :columns="['Blog', 'Name', 'Email', 'Comment', 'Status', 'Created Date', 'Actions']" :rows="$comments">
        @foreach ($comments as $comment)
            <tr>
                <td class="max-w-[14rem] text-sm">{{ Str::limit($comment->blog->blog_title, 50) }}</td>
                <td class="whitespace-nowrap">{{ $comment->name }}</td>
                <td class="text-sm">{{ $comment->email }}</td>
                <td class="max-w-md text-sm">
                    @if ($comment->parent)<span class="label-caps block text-[0.65rem] text-muted-foreground">Reply to {{ $comment->parent->name }}</span>@endif
                    {{ Str::limit($comment->comment, 160) }}
                </td>
                <td><x-status-badge :status="$comment->status" /></td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($comment->created_at, true) }}</td>
                <td>
                    <x-row-actions>
                        @can('blog-comments.edit')
                            @foreach (['Approved' => 'check', 'Rejected' => 'ban'] as $status => $icon)
                                @if ($comment->status->value !== $status)
                                    <form method="POST" action="{{ route('admin.blog-comments.moderate', $comment) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <x-action-link type="submit" :icon="$icon">{{ $status === 'Approved' ? 'Approve' : 'Reject' }}</x-action-link>
                                    </form>
                                @endif
                            @endforeach
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'blog-comment-edit', record: {{ Js::from([
                                'comment' => $comment->comment, 'status' => $comment->status->value,
                                'action' => route('admin.blog-comments.update', $comment), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                        @endcan
                        @can('blog-comments.create')
                            @unless ($comment->parent_id)
                                <x-action-link icon="reply" @click="$dispatch('open-modal', { name: 'blog-comment-reply', record: {{ Js::from([
                                    'comment' => '', 'action' => route('admin.blog-comments.reply', $comment), 'method' => 'POST', 'to' => $comment->name,
                                ]) }} })">Reply</x-action-link>
                            @endunless
                        @endcan
                        @can('blog-comments.delete')
                            <x-delete-button :action="route('admin.blog-comments.destroy', $comment)" />
                        @endcan
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $comments->links() }}

    <x-admin.crud-modal name="blog-comment" title-add="Add comment" title-edit="Edit comment" subtitle="Posted under your name on the selected blog."
        :store-url="route('admin.blog-comments.store')" :defaults="['blog_id' => '', 'comment' => '', 'status' => 'Approved']">
        <x-form.select name="blog_id" label="Blog" :options="$blogs" placeholder="Select a blog" x-model="form.blog_id" required />
        <x-form.textarea name="comment" label="Comment" x-model="form.comment" required />
        <x-form.select name="status" label="Status" :options="App\Enums\CommentStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>

    <x-admin.crud-modal name="blog-comment-edit" title-add="Edit comment" title-edit="Edit comment"
        :store-url="route('admin.blog-comments.index')" :defaults="['comment' => '', 'status' => 'Pending']">
        <x-form.textarea name="comment" label="Comment" x-model="form.comment" required />
        <x-form.select name="status" label="Status" :options="App\Enums\CommentStatus::options()" x-model="form.status" required />
    </x-admin.crud-modal>

    <x-admin.crud-modal name="blog-comment-reply" title-add="Reply to comment" title-edit="Reply to comment"
        subtitle="Replies are published immediately under your name." :store-url="route('admin.blog-comments.index')" :defaults="['comment' => '']">
        <x-form.textarea name="comment" label="Reply" x-model="form.comment" required />
    </x-admin.crud-modal>
</x-layouts.admin>
