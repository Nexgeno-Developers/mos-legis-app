@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'full' => false])
{{--
    Rich text field. Default: the simple editor (Trix) used by authors — no file/image attachments.
    full: the admin's full editor (Jodit) with headings, fonts, colours, alignment, tables and image upload.
    Either way the HTML is posted in the hidden input and sanitised on save.
--}}
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <input id="{{ $name }}" type="hidden" name="{{ $name }}" value="{{ old($name, $value) }}" data-validate-hidden @required($required)>
    @if ($full)
        <textarea data-rich-editor data-input="{{ $name }}" data-upload-url="{{ route('admin.editor-images.store') }}" class="field-input min-h-80" aria-label="{{ $label ?? $name }}">{{ old($name, $value) }}</textarea>
    @else
        <trix-editor input="{{ $name }}" class="prose-legis"></trix-editor>
    @endif
</x-form.field>
