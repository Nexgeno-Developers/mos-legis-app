@props(['name', 'label', 'options' => [], 'all' => 'All'])
<label class="flex flex-col gap-1">
    <span class="label-caps text-xs text-muted-foreground">{{ $label }}</span>
    <select name="{{ $name }}" class="field-input w-auto! min-w-40">
        <option value="">{{ $all }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected((string) request($name) === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
</label>
