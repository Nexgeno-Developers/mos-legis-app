@props(['name', 'label' => null, 'options' => [], 'value' => [], 'multiple' => true, 'placeholder' => 'Select…', 'hint' => null, 'required' => false])
{{--
    Searchable single/multi select (SOW A.09: "dropdown … with search & multi selection"),
    rendered as a native <select> enhanced by Select2 (resources/js/forms.js).
    Submits `name[]` (multiple) or `name` (single); works without JavaScript too.
--}}
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $selected = collect(old($key, $value))->map(fn ($v) => (string) $v)->all();
@endphp
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <select id="{{ $name }}" name="{{ $multiple ? $name.'[]' : $name }}" @if ($multiple) multiple @endif @required($required)
        data-search data-placeholder="{{ $placeholder }}"
        {{ $attributes->except('class')->merge(['class' => 'field-input']) }}>
        @unless ($multiple)<option value="">{{ $placeholder }}</option>@endunless
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-form.field>
