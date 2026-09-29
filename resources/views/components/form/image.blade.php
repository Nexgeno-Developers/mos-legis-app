@props(['name', 'label' => null, 'current' => null, 'hint' => 'JPG, PNG or WebP, up to 2 MB.'])
<x-form.field :label="$label" :name="$name" :hint="$hint" :class="$attributes->get('class')">
    @if ($current)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($current) }}" alt="" class="mb-2 h-24 w-auto border border-border object-cover">
    @endif
    <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="field-input">
</x-form.field>
