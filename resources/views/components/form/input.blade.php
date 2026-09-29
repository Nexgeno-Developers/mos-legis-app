@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false])
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
        @if ($type !== 'password' && $type !== 'file') value="{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}" @endif
        @required($required)
        {{ $attributes->except('class')->merge(['class' => 'field-input']) }}
        @error(str_replace(['[', ']'], ['.', ''], $name)) aria-invalid="true" @enderror>
</x-form.field>
