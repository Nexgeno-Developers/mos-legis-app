@props(['name' => 'search', 'placeholder' => 'Search…'])
<input type="search" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'field-input w-full sm:w-auto sm:min-w-[16rem] sm:flex-1 md:max-w-sm']) }}>
