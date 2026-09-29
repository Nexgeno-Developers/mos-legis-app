@php $editing = $submission->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit '.$submission->reference() : 'Add submission'">
    <x-admin.heading :title="$editing ? 'Edit '.$submission->reference() : 'Add submission'" description="Author details, manuscript information and declarations (the author pays the pre-screening fee from their account).">
        <x-slot:actions>
            <x-button :href="$editing ? route('admin.submissions.show', $submission) : route('admin.submissions.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.submissions.update', $submission) : route('admin.submissions.store') }}" enctype="multipart/form-data" class="mt-8 max-w-4xl space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel title="Author details">
            <div class="grid gap-5 md:grid-cols-2">
                @unless ($editing)
                    <x-form.multi-select name="user_id" label="Author account" :options="$authors" :multiple="false" placeholder="Search authors…" required class="md:col-span-2" />
                @endunless
                <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" :value="$submission->author_category_id" placeholder="Select" required />
                <x-form.input name="institution" label="Institution" :value="$submission->institution" required />
                <x-form.input name="country" label="Country" :value="$submission->country ?? settings('general.default_country')" required />
            </div>
            @include('submissions._co-authors')
        </x-admin.panel>

        @include('submissions._manuscript-fields')

        <x-admin.panel title="Confirmations">
            <div class="space-y-3">
                @foreach (App\Models\ManuscriptSubmission::DECLARATIONS as $field => $label)
                    <x-form.checkbox :name="$field" :label="$label" :checked="$submission->{$field}" />
                @endforeach
            </div>
        </x-admin.panel>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save changes' : 'Create submission' }}</x-button>
        </div>
    </form>
</x-layouts.admin>
