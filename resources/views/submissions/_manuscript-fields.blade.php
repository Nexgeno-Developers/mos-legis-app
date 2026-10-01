{{-- Manuscript information fields shared by admin and author forms. Expects $submission and $contentCategories (models). --}}
<x-admin.panel title="Manuscript information">
    <div class="space-y-5" x-data="docxWordCount()">
        <x-form.input name="title" label="Title" :value="$submission->title" required />
        <x-form.field label="Content category" name="content_category_id" required>
            <select name="content_category_id" id="content_category_id" class="field-input" required>
                <option value="">Select a content category</option>
                @foreach ($contentCategories as $category)
                    <option value="{{ $category->id }}" @selected(old('content_category_id', $submission->content_category_id) == $category->id)>
                        {{ $category->name }} ({{ $category->wordLimitLabel() }})
                    </option>
                @endforeach
            </select>
        </x-form.field>
        <x-form.field label="Manuscript file (.docx)" name="manuscript" :required="! $submission->exists"
            :hint="$submission->exists ? 'Upload only to replace the current file.' : 'Word documents (.docx) only, up to '.App\Support\UploadLimits::label(20480).'. The word count is calculated automatically.'">
            <input type="file" name="manuscript" id="manuscript" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                class="field-input" data-rule-maxbytes="{{ App\Support\UploadLimits::bytes(20480) }}" data-msg-maxbytes="This file is larger than {{ App\Support\UploadLimits::label(20480) }}. Please upload a smaller file." @change="count($event, '#word_count_display')">
            <p x-show="counting" class="text-sm text-muted-foreground">Counting words…</p>
            <p x-show="error" x-text="error" class="text-sm text-destructive"></p>
        </x-form.field>
        <x-form.field label="Word count" name="word_count" hint="Filled automatically from the uploaded file and verified on submission.">
            <input type="number" id="word_count_display" value="{{ $submission->word_count }}" class="field-input" readonly>
        </x-form.field>
        <x-form.input name="keywords" label="Keywords" :value="implode(', ', $submission->keywords ?? [])" placeholder="constitutional law, federalism, taxation" hint="3 to 6 keywords, separated by commas." required />
        <x-form.textarea name="abstract" label="Abstract" :value="$submission->abstract" rows="6" required hint="Not more than 250 words." />
    </div>
</x-admin.panel>
