@props(['name' => 'search', 'placeholder' => 'Search…'])
<input type="search" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'field-input min-w-[16rem] flex-1 md:max-w-sm']) }}>
