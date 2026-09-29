@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'required' => false, 'rows' => 4])
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
        {{ $attributes->except('class')->merge(['class' => 'field-input']) }}>{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}</textarea>
</x-form.field>
