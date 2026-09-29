@php $editing = $blog->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit blog' : 'Add blog'">
    <x-admin.heading :title="$editing ? 'Edit blog' : 'Add blog'" description="Blog content, categorisation and SEO fields.">
        <x-slot:actions>
            @if ($editing && $blog->status->value === 'Published')
                <x-button :href="route('blogs.show', $blog->slug)" target="_blank" icon="external-link">View post</x-button>
            @endif
            <x-button :href="route('admin.blogs.index')" icon="arrow-left">Back to blogs</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}" enctype="multipart/form-data" class="mt-8 max-w-5xl">
        @csrf
        @if ($editing) @method('PUT') @endif
        @include('blogs._form-fields', ['admin' => true])
        <div class="mt-8 flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save blog' : 'Create blog' }}</x-button>
            <x-button :href="route('admin.blogs.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
