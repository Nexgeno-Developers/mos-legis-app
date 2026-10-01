@props(['name' => 'phone', 'label' => 'Mobile number', 'value' => null, 'required' => false, 'hint' => null, 'id' => null])
{{--
    Phone number with a country picker (intl-tel-input, resources/js/forms.js). The visible
    field is for typing; the hidden "{name}" field carries the full international number
    (+919876543210) that the server validates and stores.
--}}
@php
    $id ??= $name;
    $current = old($name, $value);
@endphp
<x-form.field :label="$label" :name="$id" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <input type="tel" id="{{ $id }}" name="{{ $name }}__display" value="{{ $current }}" autocomplete="tel" inputmode="tel"
        data-phone="{{ $name }}" data-phone-country="{{ strtolower(App\Support\PhoneNumbers::defaultCountry()) }}" data-rule-intlphone="true"
        @required($required) {{ $attributes->except('class')->merge(['class' => 'field-input']) }}>
    <input type="hidden" name="{{ $name }}" value="{{ $current }}" data-phone-value>
    @if ($id !== $name)
        @error($name)<p class="text-sm text-destructive" role="alert" data-server-error>{{ $message }}</p>@enderror
    @endif
</x-form.field>
