@props(['label' => 'Date range', 'from' => 'from', 'to' => 'to'])
<div class="flex w-full flex-col gap-1 sm:w-auto">
    <span class="label-caps text-xs text-muted-foreground">{{ $label }}</span>
    <div class="flex items-center gap-2">
        <input type="date" name="{{ $from }}" value="{{ request($from) }}" class="field-input min-w-0 flex-1 sm:w-auto! sm:flex-none" aria-label="{{ $label }} from">
        <span class="text-muted-foreground">–</span>
        <input type="date" name="{{ $to }}" value="{{ request($to) }}" class="field-input min-w-0 flex-1 sm:w-auto! sm:flex-none" aria-label="{{ $label }} to">
    </div>
</div>
