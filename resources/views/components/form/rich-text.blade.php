@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false])
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <input id="{{ $name }}" type="hidden" name="{{ $name }}" value="{{ old($name, $value) }}" data-validate-hidden @required($required)>
    <trix-editor input="{{ $name }}" class="prose-legis"></trix-editor>
</x-form.field>
