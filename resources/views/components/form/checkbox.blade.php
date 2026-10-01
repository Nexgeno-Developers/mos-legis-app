@props(['name', 'label', 'checked' => false, 'value' => '1', 'required' => false])
<div data-field>
    <label {{ $attributes->merge(['class' => 'flex items-start gap-3 text-base text-foreground']) }}>
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked)) @required($required)
            @if ($required) data-msg-required="Please accept this to continue." @endif class="mt-1.5 h-4 w-4 shrink-0 accent-primary">
        <span>{{ $label }}@if ($required)<span class="text-primary"> *</span>@endif</span>
    </label>
    @error($name)<p class="choice-error mt-1 text-sm text-destructive" role="alert" data-server-error>{{ $message }}</p>@enderror
</div>
