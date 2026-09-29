@props(['name', 'label', 'checked' => false, 'value' => '1'])
<label {{ $attributes->merge(['class' => 'flex items-start gap-3 text-base text-foreground']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked)) class="mt-1.5 h-4 w-4 accent-primary">
    <span>{{ $label }}</span>
</label>
@error($name)<p class="-mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
