{{-- Manuscript information fields shared by admin and author forms. Expects $submission and $contentCategories (models). --}}
<x-admin.panel title="Manuscript information">
    <div class="space-y-5" x-data="docxWordCount()">
        <x-form.input name="title" label="Title" :value="$submission->title" required maxlength="255" />
        <x-form.field label="Content category" name="content_category_id" required>
            <select name="content_category_id" id="content_category_id" class="field-input" required
                @change="const m = $root.querySelector('[name=manuscript]'); if (m.files.length) window.jQuery(m).valid()">
                <option value="">Select a content category</option>
                @foreach ($contentCategories as $category)
                    <option value="{{ $category->id }}" data-name="{{ $category->name }}" data-min="{{ $category->min_word_limit }}" data-max="{{ $category->max_word_limit }}" @selected(old('content_category_id', $submission->content_category_id) == $category->id)>
                        {{ $category->name }} ({{ $category->wordLimitLabel() }})
                    </option>
                @endforeach
            </select>
        </x-form.field>
        <x-form.field label="Manuscript file (.docx)" name="manuscript" :required="! $submission->exists"
            :hint="$submission->exists ? 'Upload only to replace the current file.' : 'Word documents (.docx) only, up to '.App\Support\UploadLimits::label(20480).'. The word count is calculated automatically.'">
            <input type="file" name="manuscript" id="manuscript" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                class="field-input" data-rule-docx="true" data-rule-wordrange="#content_category_id" data-rule-maxbytes="{{ App\Support\UploadLimits::bytes(20480) }}" data-msg-maxbytes="This file is larger than {{ App\Support\UploadLimits::label(20480) }}. Please upload a smaller file." @change="count($event, '#word_count_display')">
            <p x-show="counting" class="text-sm text-muted-foreground">Counting words…</p>
            <p x-show="error" x-text="error" class="text-sm text-destructive"></p>
        </x-form.field>
        <x-form.field label="Word count" name="word_count" hint="Filled automatically from the uploaded file and verified on submission.">
            <input type="number" id="word_count_display" value="{{ $submission->word_count }}" class="field-input" readonly>
        </x-form.field>
        <x-form.input name="keywords" label="Keywords" :value="implode(', ', $submission->keywords ?? [])" placeholder="constitutional law, federalism, taxation" hint="3 to 6 keywords, separated by commas." required maxlength="500" data-rule-keywords="3,6" />
        <x-form.textarea name="abstract" label="Abstract" :value="$submission->abstract" rows="6" required maxlength="5000" data-rule-maxwords="250" hint="Not more than 250 words." />
    </div>
</x-admin.panel>
