@php
    $profile = $blog->user?->authorProfile;
    $shareUrl = urlencode(url()->current());
    $needsApproval = settings()->bool('approvals.blog_comment_approval_required');
@endphp
<x-layouts.site :title="$blog->meta_title ?: $blog->blog_title" :description="$blog->meta_description ?: $blog->excerpt" :og-image="$blog->og_image ?: $blog->featured_image">
    <x-page-header :crumbs="['Blogs' => route('blogs.index'), $blog->category->category_name => route('blogs.index', ['category' => $blog->category->slug])]" :title="$blog->blog_title">
        @if ($blog->excerpt)<p class="measure mt-2 text-base leading-relaxed text-muted-foreground">{{ $blog->excerpt }}</p>@endif
        <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
            <span class="flex items-center gap-2 text-foreground">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">{{ Str::upper(Str::substr($blog->author_name, 0, 1)) }}</span>
                <span class="font-medium">{{ $blog->author_name }}</span>
            </span>
            <span class="flex items-center gap-1.5"><x-icon name="calendar-range" /> {{ format_date($blog->publish_date) }}</span>
            <span class="flex items-center gap-1.5"><x-icon name="clock" /> {{ $blog->readingMinutes() }} min read</span>
            <span class="flex items-center gap-1.5"><x-icon name="eye" /> {{ number_format($blog->views) }} {{ Str::plural('view', $blog->views) }}</span>
            <a href="#comments" class="flex items-center gap-1.5 hover:text-primary"><x-icon name="message-square" /> {{ $commentCount }}</a>
        </div>
    </x-page-header>

    <div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-[30px] md:py-[70px] sm:px-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <article class="min-w-0">
            @if ($blog->featured_image)
                <img src="{{ Storage::disk('public')->url($blog->featured_image) }}" alt="" class="mb-10 aspect-[16/9] w-full border border-border object-cover">
            @endif

            <div class="prose-legis measure">{!! $blog->content !!}</div>

            {{-- Tags and sharing --}}
            <div class="measure mt-10 flex flex-col gap-5 border-y border-border py-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    @foreach ($blog->tags as $tag)
                        <a href="{{ route('blogs.index', ['tag' => $tag->slug]) }}" class="rounded-full border border-border px-3 py-1 text-xs hover:border-gold hover:text-primary">#{{ $tag->tag_name }}</a>
                    @endforeach
                </div>
                <div class="flex items-center gap-3 text-sm" x-data="{ copied: false }">
                    <span class="text-muted-foreground">Share</span>
                    <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Share on LinkedIn"><x-social-icon network="linkedin" /></a>
                    <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($blog->blog_title) }}" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Share on X"><x-social-icon network="twitter" /></a>
                    <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Share on Facebook"><x-social-icon network="facebook" /></a>
                    <button type="button" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Copy link"
                        @click="navigator.clipboard.writeText(@js(url()->current())); copied = true; setTimeout(() => copied = false, 2000)">
                        <span x-show="!copied"><x-icon name="link" /></span><span x-show="copied" x-cloak><x-icon name="check" class="text-success" /></span>
                    </button>
                </div>
            </div>

            {{-- About the author --}}
            @if ($profile?->bio || $profile?->institution)
                <div class="measure mt-8 flex gap-4 border border-border bg-card p-5">
                    @if ($profile->profile_picture)
                        <img src="{{ Storage::disk('public')->url($profile->profile_picture) }}" alt="" class="h-14 w-14 shrink-0 rounded-full object-cover">
                    @else
                        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-primary font-display text-xl text-primary-foreground">{{ Str::upper(Str::substr($blog->author_name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="label-caps text-xs text-muted-foreground">About the author</p>
                        <p class="mt-1 font-display text-lg">{{ $blog->author_name }}</p>
                        @if ($profile->institution)<p class="text-sm text-muted-foreground">{{ $profile->institution }}</p>@endif
                        @if ($profile->bio)<p class="mt-2 text-sm leading-relaxed">{{ $profile->bio }}</p>@endif
                    </div>
                </div>
            @endif

            {{-- Discussion --}}
            <section class="measure mt-14 scroll-mt-28" id="comments">
                <x-section-heading eyebrow="Discussion" :title="$commentCount.' '.Str::plural('comment', $commentCount)" />

                @auth
                    <form method="POST" action="{{ route('blogs.comments.store', $blog->slug) }}" class="mt-6 border border-border bg-card p-5">
                        @csrf
                        <x-form.textarea name="comment" label="Join the discussion" rows="4" required
                            :hint="$needsApproval ? 'Comments appear once an editor approves them.' : 'Be respectful and stay on topic.'" />
                        <div class="mt-3 flex justify-end"><x-button type="submit" variant="primary" icon="send">Post comment</x-button></div>
                    </form>
                @else
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border border-border bg-card px-5 py-4">
                        <p class="text-sm">Sign in to join the discussion.</p>
                        <x-button :href="route('login')" size="sm" icon="log-in">Sign in</x-button>
                    </div>
                @endauth

                @if ($comments->isEmpty())
                    <p class="mt-8 text-sm text-muted-foreground">No comments yet. Start the conversation.</p>
                @else
                    <ul class="mt-8 divide-y divide-border">
                        @foreach ($comments as $comment)
                            <li class="py-6 first:pt-0" x-data="{ reply: false }">
                                @include('site.blogs._comment', ['comment' => $comment, 'isReply' => false])

                                @if ($comment->replies->isNotEmpty())
                                    <div class="mt-5 ml-12 space-y-5 border-l-2 border-gold/40 pl-5">
                                        @foreach ($comment->replies as $reply)
                                            @include('site.blogs._comment', ['comment' => $reply, 'isReply' => true])
                                        @endforeach
                                    </div>
                                @endif

                                @auth
                                    <form x-show="reply" x-cloak method="POST" action="{{ route('blogs.comments.store', $blog->slug) }}" class="mt-4 ml-12 space-y-2">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea name="comment" rows="2" class="field-input" required minlength="3" placeholder="Reply to {{ $comment->name }}…"></textarea>
                                        <div class="flex gap-2">
                                            <x-button type="submit" size="sm" variant="primary">Post reply</x-button>
                                            <x-button size="sm" variant="ghost" x-on:click="reply = false">Cancel</x-button>
                                        </div>
                                    </form>
                                @endauth
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </article>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            <div class="border border-border bg-card p-5">
                <p class="label-caps text-xs text-muted-foreground">About this post</p>
                <dl class="mt-3 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Category</dt><dd><a href="{{ route('blogs.index', ['category' => $blog->category->slug]) }}" class="text-primary hover:underline">{{ $blog->category->category_name }}</a></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Published</dt><dd>{{ format_date($blog->publish_date) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Reading time</dt><dd>{{ $blog->readingMinutes() }} min</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted-foreground">Views</dt><dd>{{ number_format($blog->views) }}</dd></div>
                </dl>
            </div>

            @if ($related->isNotEmpty())
                <div>
                    <p class="label-caps text-xs text-muted-foreground">Related posts</p>
                    <ul class="mt-3 space-y-3">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('blogs.show', $item->slug) }}" class="group flex gap-3 border border-border bg-card p-3 hover:border-gold">
                                    @if ($item->featured_image)
                                        <img src="{{ Storage::disk('public')->url($item->featured_image) }}" alt="" loading="lazy" class="h-16 w-20 shrink-0 object-cover">
                                    @endif
                                    <span class="min-w-0">
                                        <span class="line-clamp-2 text-sm font-medium group-hover:text-primary">{{ $item->blog_title }}</span>
                                        <span class="mt-1 block text-xs text-muted-foreground">{{ format_date($item->publish_date) }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-button :href="route('blogs.index')" icon="arrow-left" class="w-full">All posts</x-button>
        </aside>
    </div>
</x-layouts.site>
