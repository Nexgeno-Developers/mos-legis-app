<x-layouts.account :title="$blog->exists ? 'Edit blog post' : 'New blog post'">
    <form method="POST" action="{{ $blog->exists ? route('account.blogs.update', $blog) : route('account.blogs.store') }}" enctype="multipart/form-data" class="max-w-5xl">
        @csrf
        @if ($blog->exists) @method('PUT') @endif
        @include('blogs._form-fields', ['admin' => false])
        <div class="mt-8 flex gap-3">
            <x-button type="submit" variant="primary" icon="save">Save post</x-button>
            <x-button :href="route('account.blogs.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.account>
