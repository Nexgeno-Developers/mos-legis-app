<x-layouts.site :title="$blog->meta_title ?: $blog->blog_title" :description="$blog->meta_description ?: $blog->excerpt" :og-image="$blog->og_image ?: $blog->featured_image">
    <x-page-header :crumbs="['Blogs' => route('blogs.index'), $blog->category->category_name => route('blogs.index', ['category' => $blog->category->slug])]" :title="$blog->blog_title" :intro="$blog->author_name.' · '.format_date($blog->publish_date)" />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[1fr_18rem]">
        <article>
            @if ($blog->featured_image)<img src="{{ Storage::disk('public')->url($blog->featured_image) }}" alt="" class="mb-10 max-h-[28rem] w-full object-cover">@endif
            <div class="prose-legis measure">{!! $blog->content !!}</div>
            <div class="mt-10 flex flex-wrap gap-2">
                @foreach ($blog->tags as $tag)<a href="{{ route('blogs.index', ['tag' => $tag->slug]) }}" class="rounded-full border border-border px-3 py-1 text-xs hover:border-gold">#{{ $tag->tag_name }}</a>@endforeach
            </div>
            <div class="mt-6 flex items-center gap-3 text-sm">
                <span class="text-muted-foreground">Share:</span>
                <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" class="hover:text-primary"><x-social-icon network="linkedin" /></a>
                <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->blog_title) }}" class="hover:text-primary"><x-social-icon network="twitter" /></a>
                <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" class="hover:text-primary"><x-social-icon network="facebook" /></a>
            </div>

            <section class="mt-16" id="comments">
                <x-section-heading eyebrow="Discussion" :title="$comments->count().' '.Str::plural('comment', $comments->count())" />
                <ul class="mt-8 space-y-6">
                    @foreach ($comments as $comment)
                        <li class="border-l-2 border-gold/60 pl-5" x-data="{ reply: false }">
                            <p class="text-sm"><strong>{{ $comment->name }}</strong> <span class="text-muted-foreground">· {{ format_date($comment->created_at) }}</span></p>
                            <p class="mt-1 whitespace-pre-line">{{ $comment->comment }}</p>
                            @auth<button type="button" @click="reply = !reply" class="mt-1 text-sm text-primary hover:underline">Reply</button>@endauth
                            @foreach ($comment->replies as $reply)
                                <div class="mt-4 ml-6 border-l border-border pl-4">
                                    <p class="text-sm"><strong>{{ $reply->name }}</strong> <span class="text-muted-foreground">· {{ format_date($reply->created_at) }}</span></p>
                                    <p class="mt-1 whitespace-pre-line">{{ $reply->comment }}</p>
                                </div>
                            @endforeach
                            @auth
                                <form x-show="reply" x-cloak method="POST" action="{{ route('blogs.comments.store', $blog->slug) }}" class="mt-3 space-y-2">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="comment" rows="2" class="field-input" required placeholder="Write a reply…"></textarea>
                                    <x-button type="submit" size="sm">Post reply</x-button>
                                </form>
                            @endauth
                        </li>
                    @endforeach
                </ul>
                @auth
                    <form method="POST" action="{{ route('blogs.comments.store', $blog->slug) }}" class="mt-10 space-y-3">
                        @csrf
                        <x-form.textarea name="comment" label="Leave a comment" rows="4" required hint="Comments are published after moderation." />
                        <x-button type="submit" variant="primary" icon="send">Post comment</x-button>
                    </form>
                @else
                    <p class="mt-10 border-l-2 border-gold/60 bg-card px-4 py-3"><a href="{{ route('login') }}" class="text-primary hover:underline">Sign in</a> to join the discussion.</p>
                @endauth
            </section>
        </article>
        <aside class="space-y-6">
            <x-dl class="sm:grid-cols-1!" :items="['Author' => e($blog->author_name), 'Published' => format_date($blog->publish_date), 'Category' => e($blog->category->category_name), 'Views' => number_format($blog->views)]" />
            @if ($related->isNotEmpty())
                <div>
                    <p class="label-caps text-xs text-muted-foreground">Related posts</p>
                    <ul class="mt-2 space-y-3">@foreach ($related as $item)<li><a href="{{ route('blogs.show', $item->slug) }}" class="hover:text-primary">{{ $item->blog_title }}</a></li>@endforeach</ul>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.site>
