@props(['label' => 'Date range', 'from' => 'from', 'to' => 'to'])
<div class="flex flex-col gap-1">
    <span class="label-caps text-xs text-muted-foreground">{{ $label }}</span>
    <div class="flex items-center gap-2">
        <input type="date" name="{{ $from }}" value="{{ request($from) }}" class="field-input w-auto!" aria-label="From">
        <span class="text-muted-foreground">–</span>
        <input type="date" name="{{ $to }}" value="{{ request($to) }}" class="field-input w-auto!" aria-label="To">
    </div>
</div>
