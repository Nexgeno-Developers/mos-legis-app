@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false, 'multiple' => false])
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $selected = collect(old($key, $value instanceof \BackedEnum ? $value->value : $value))->map(fn ($v) => (string) ($v instanceof \BackedEnum ? $v->value : $v))->all();
@endphp
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <select id="{{ $name }}" name="{{ $multiple ? $name.'[]' : $name }}" @required($required) @if ($multiple) multiple @endif
        {{ $attributes->except('class')->merge(['class' => 'field-input']) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-form.field>
