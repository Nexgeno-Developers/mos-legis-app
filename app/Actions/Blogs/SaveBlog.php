<?php

namespace App\Actions\Blogs;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\User;
use App\Notifications\WorkflowNotifier;
use App\Support\Html;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Creates/updates a blog post for the admin panel (A.05) and the author portal (B.05).
 * Author posts that ask to be published become "Pending" when the
 * "Author blog posts require approval" setting is on.
 */
class SaveBlog
{
    /** @param array<string, mixed> $data validated BlogRequest data */
    public function handle(array $data, User $actor, ?Blog $blog = null, bool $asAuthor = false): Blog
    {
        return DB::transaction(function () use ($data, $actor, $blog, $asAuthor) {
            $blog ??= new Blog(['user_id' => $actor->id]);
            $status = BlogStatus::from($data['status']);

            if ($asAuthor && $status === BlogStatus::Published && settings()->bool('general.blog_author_approval_required')) {
                $status = BlogStatus::Pending;
            }

            $blog->fill([
                'blog_title' => $data['blog_title'],
                'slug' => $this->uniqueSlug(($data['slug'] ?? null) ?: $data['blog_title'], $blog->id),
                'category_id' => $data['category_id'],
                'author_name' => $data['author_name'] ?? $actor->name,
                'excerpt' => $data['excerpt'],
                'content' => Html::clean($data['content']),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'status' => $status,
                'publish_date' => $data['publish_date'] ?? today(),
                'featured_post' => $asAuthor ? $blog->featured_post ?? false : (bool) ($data['featured_post'] ?? false),
            ]);

            foreach (['featured_image', 'og_image'] as $field) {
                if (($data[$field] ?? null) instanceof UploadedFile) {
                    if ($blog->{$field}) {
                        Storage::disk('public')->delete($blog->{$field});
                    }
                    $blog->{$field} = $data[$field]->store('blogs', 'public');
                }
            }

            $wasPending = $blog->getOriginal('status') === BlogStatus::Pending;
            $blog->save();
            $blog->tags()->sync($data['tag_ids'] ?? []);

            if ($status === BlogStatus::Pending && ! $wasPending) {
                app(WorkflowNotifier::class)->toAdmins('blog_pending_approval', [
                    'title' => $blog->blog_title,
                    'author_name' => $blog->author_name,
                ]);
            }

            return $blog;
        });
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Blog::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
