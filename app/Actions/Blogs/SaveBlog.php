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
 * "Blog posts approval required" setting is on. Authors don't pick a publish date:
 * it is the day the post first goes live (or is approved).
 */
class SaveBlog
{
    /** @param array<string, mixed> $data validated BlogRequest data */
    public function handle(array $data, User $actor, ?Blog $blog = null, bool $asAuthor = false): Blog
    {
        return DB::transaction(function () use ($data, $actor, $blog, $asAuthor) {
            $blog ??= new Blog(['user_id' => $actor->id]);
            $status = BlogStatus::from($data['status']);

            if ($asAuthor && $status === BlogStatus::Published && settings()->bool('approvals.blog_author_approval_required')) {
                $status = BlogStatus::Pending;
            }

            $blog->fill([
                'blog_title' => $data['blog_title'],
                'slug' => $this->uniqueSlug(($data['slug'] ?? null) ?: $data['blog_title'], $blog->id),
                'category_id' => $data['category_id'],
                'author_name' => $data['author_name'] ?? $actor->name,
                'excerpt' => $data['excerpt'],
                // Admins use the full editor (formatting kept); authors the simple one.
                'content' => $asAuthor ? Html::clean($data['content']) : Html::cleanRich($data['content']),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'status' => $status,
                'publish_date' => $asAuthor ? $this->authorPublishDate($blog) : ($data['publish_date'] ?? today()),
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

            if ($wasPending && $status === BlogStatus::Published) {
                $this->approved($blog, dateToday: false); // the admin form sets the date
            }

            if ($status === BlogStatus::Pending && ! $wasPending) {
                app(WorkflowNotifier::class)->toAdmins('blog_pending_approval', [
                    'title' => $blog->blog_title,
                    'author_name' => $blog->author_name,
                ]);
            }

            return $blog;
        });
    }

    /** An approved author post goes live (today, unless the admin set the date); its author is told. */
    public function approved(Blog $blog, bool $dateToday = true): void
    {
        if ($dateToday) {
            $blog->forceFill(['publish_date' => today()])->save();
        }

        if ($blog->user?->isAuthor()) {
            app(WorkflowNotifier::class)->toUser('blog_approved', $blog->user, ['title' => $blog->blog_title]);
        }
    }

    /** Keeps the date of a post that is already live; otherwise today. */
    private function authorPublishDate(Blog $blog): mixed
    {
        return $blog->exists && $blog->getOriginal('status') === BlogStatus::Published && $blog->publish_date
            ? $blog->publish_date
            : today();
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
