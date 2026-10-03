@props(['name' => 'state', 'value' => null, 'label' => 'State / region', 'required' => true])
{{--
    Billing state: a dropdown of Indian states/UTs when the country is India (it decides CGST+SGST vs IGST),
    a free-text field otherwise. Needs an Alpine `country` value in the surrounding x-data.
    Only the visible field is enabled, so only it is submitted.
--}}
@php
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $id = str_replace(['[', ']'], ['_', ''], $name);
    $current = (string) old($errorKey, $value);
    $states = config('indian_states');
    $matched = collect($states)->first(fn ($s) => strcasecmp($s, trim($current)) === 0);
@endphp
<x-form.field :label="$label" :name="$id" :class="$attributes->get('class')">
    <div x-show="country === 'IN'">
        <select name="{{ $name }}" id="{{ $id }}" class="field-input" @required($required) data-placeholder="Select a state"
            data-msg-required="Select the state — it is needed for GST." x-bind:disabled="country !== 'IN'">
            <option value="">Select a state</option>
            @foreach ($states as $state)
                <option value="{{ $state }}" @selected($matched === $state)>{{ $state }}</option>
            @endforeach
        </select>
    </div>
    <div x-show="country !== 'IN'" x-cloak>
        <input type="text" name="{{ $name }}" id="{{ $id }}_text" value="{{ $matched ? '' : $current }}" class="field-input"
            placeholder="State / province / region" autocomplete="address-level1" x-bind:disabled="country === 'IN'">
    </div>
    @if ($id !== $errorKey)
        @error($errorKey)<p class="text-sm text-destructive" role="alert" data-server-error>{{ $message }}</p>@enderror
    @endif
</x-form.field>
