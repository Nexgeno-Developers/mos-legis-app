@php $editing = $submission->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit '.$submission->reference() : 'Add submission'">
    <x-admin.heading :title="$editing ? 'Edit '.$submission->reference() : 'Add submission'" description="Author details, manuscript information and declarations (the author pays the pre-screening fee from their account).">
        <x-slot:actions>
            <x-button :href="$editing ? route('admin.submissions.show', $submission) : route('admin.submissions.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.submissions.update', $submission) : route('admin.submissions.store') }}" enctype="multipart/form-data" class="mx-auto mt-8 max-w-4xl space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel title="Author" :description="$editing ? 'Recorded when the manuscript was submitted.' : 'Author category, institution and country are taken from the author’s profile.'">
            @if ($editing)
                <dl class="grid gap-4 border border-border bg-secondary/50 p-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Author</dt><dd class="font-medium">{{ $submission->author?->name }}</dd></div>
                    <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Author category</dt><dd>{{ $submission->authorCategory?->name ?? '—' }}</dd></div>
                    <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Institution</dt><dd>{{ $submission->institution ?: '—' }}</dd></div>
                    <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Country</dt><dd>{{ $submission->country ?: '—' }}</dd></div>
                </dl>
            @else
                <div x-data="{ author: @js((string) old('user_id', '')), details: @js($authorDetails) }" class="space-y-4">
                    <x-form.multi-select name="user_id" label="Author account" :options="$authors" :multiple="false" placeholder="Search authors…" required x-model="author" />
                    <template x-if="details[author]">
                        <div>
                            <dl class="grid gap-4 border border-border bg-secondary/50 p-4 sm:grid-cols-3">
                                <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Author category</dt><dd x-text="details[author].category || 'Missing'" :class="! details[author].category && 'text-destructive'"></dd></div>
                                <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Institution</dt><dd x-text="details[author].institution || 'Missing'" :class="! details[author].institution && 'text-destructive'"></dd></div>
                                <div><dt class="label-caps text-[0.65rem] text-muted-foreground">Country</dt><dd x-text="details[author].country || '—'"></dd></div>
                            </dl>
                            <p x-show="! details[author].category || ! details[author].institution" class="mt-2 text-sm text-destructive">
                                Complete this author’s profile first. <a :href="details[author].editUrl" class="underline">Edit author</a>
                            </p>
                        </div>
                    </template>
                    @error('author_category_id')<p class="text-sm text-destructive" role="alert">{{ $message }}</p>@enderror
                    @error('institution')<p class="text-sm text-destructive" role="alert">{{ $message }}</p>@enderror
                </div>
            @endif
            @include('submissions._co-authors')
        </x-admin.panel>

        @include('submissions._manuscript-fields')

        <x-admin.panel title="Confirmations">
            <div class="space-y-3">
                @foreach (App\Models\ManuscriptSubmission::DECLARATIONS as $field => $label)
                    <x-form.checkbox :name="$field" :label="$label" :checked="$submission->{$field}" :required="! $submission->exists" />
                @endforeach
            </div>
        </x-admin.panel>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save changes' : 'Create submission' }}</x-button>
        </div>
    </form>
</x-layouts.admin>
