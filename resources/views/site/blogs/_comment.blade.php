{{-- One comment or reply. Its writer sees "Awaiting approval" while it is pending and can delete it (replies go with it). --}}
@php
    $mine = auth()->id() && $comment->user_id === auth()->id();
    $pending = $comment->status === App\Enums\CommentStatus::Pending;
@endphp
<div id="comment-{{ $comment->id }}" class="flex gap-3">
    <span @class([
        'grid shrink-0 place-items-center rounded-full font-semibold',
        'h-10 w-10 bg-primary text-sm text-primary-foreground' => ! $isReply,
        'h-8 w-8 bg-gold/20 text-xs text-foreground' => $isReply,
    ])>{{ Str::upper(Str::substr($comment->name, 0, 1)) }}</span>
    <div class="min-w-0 flex-1">
        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
            <strong class="font-semibold">{{ $comment->name }}</strong>
            <span class="text-muted-foreground">{{ $comment->created_at->diffForHumans() }}</span>
            @if ($pending)<x-badge tone="warning">Awaiting approval</x-badge>@endif
        </p>
        <p class="mt-1 whitespace-pre-line break-words leading-relaxed">{{ $comment->comment }}</p>
        <div class="mt-1.5 flex items-center gap-4 text-sm">
            @if (! $isReply && auth()->check() && ! $pending)
                <button type="button" @click="reply = !reply" class="inline-flex items-center gap-1 text-primary hover:underline"><x-icon name="reply" /> Reply</button>
            @endif
            @if ($mine)
                <x-delete-button :action="route('blogs.comments.destroy', [$blog->slug, $comment])"
                    :confirm="! $isReply && $comment->replies->isNotEmpty() ? 'Delete your comment? Its replies will be deleted too.' : 'Delete your comment?'" />
            @endif
        </div>
    </div>
</div>
