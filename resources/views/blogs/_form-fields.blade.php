{{-- Blog editor fields shared by the admin panel and the author portal. Expects $blog, $categories, $tags, $admin. --}}
<div class="space-y-8">
    <x-admin.panel title="Basic information">
        <div class="space-y-5">
            <div @class(['grid gap-5', 'md:grid-cols-3' => $admin, 'md:grid-cols-2' => ! $admin])>
                <x-form.input name="blog_title" label="Title" :value="$blog->blog_title" required class="md:col-span-full" />
                <x-form.input name="slug" label="Slug" :value="$blog->slug" hint="Leave blank to generate from the title." />
                <x-form.select name="category_id" label="Category" :options="$categories" :value="$blog->category_id" placeholder="Select a category" required />
                @if ($admin)
                    <x-form.input name="author_name" label="Author" :value="$blog->author_name" required hint="Byline shown on the post." />
                @endif
            </div>
            <x-form.textarea name="excerpt" label="Excerpt" :value="$blog->excerpt" rows="2" required />
            <x-form.multi-select name="tag_ids" label="Tags" :options="$tags" :value="$blog->relationLoaded('tags') ? $blog->tags->pluck('id')->all() : []" placeholder="Search and select tags…" />
            <x-form.image name="featured_image" label="Featured image" :current="$blog->featured_image" />
        </div>
    </x-admin.panel>

    <x-admin.panel title="Blog content">
        <x-form.rich-text name="content" :value="$blog->content" required />
    </x-admin.panel>

    <x-admin.panel title="SEO">
        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input name="meta_title" label="Meta title" :value="$blog->meta_title" />
            <x-form.image name="og_image" label="OG image" :current="$blog->og_image" />
            <x-form.textarea name="meta_description" label="Meta description" :value="$blog->meta_description" rows="2" class="md:col-span-2" />
        </div>
    </x-admin.panel>

    <x-admin.panel title="Publication">
        <div @class(['grid gap-5', 'md:grid-cols-3' => $admin, 'md:grid-cols-2' => ! $admin])>
            <x-form.select name="status" label="Status" :value="$blog->status"
                :options="$admin ? App\Enums\BlogStatus::options() : ['Draft' => 'Draft', 'Published' => 'Publish']" required
                :hint="$admin ? null : (settings()->bool('general.blog_author_approval_required') ? 'Published posts are reviewed by the editors before they go live.' : null)" />
            <x-form.input name="publish_date" type="date" label="Publish date" :value="$blog->publish_date?->toDateString()" required />
            @if ($admin)
                <div class="flex items-end pb-2">
                    <x-form.checkbox name="featured_post" label="Featured post" :checked="$blog->featured_post" />
                </div>
            @endif
        </div>
    </x-admin.panel>
</div>
