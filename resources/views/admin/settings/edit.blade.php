@php
    $descriptions = [
        'general' => 'Identity, contact details and locale defaults.',
        'manuscript' => 'Screening thresholds and manuscript-pipeline toggles.',
        'payment' => 'Currency and gateway configuration. Gateway keys are set in the server .env file.',
        'seo_social' => 'Default metadata and social links used across the public site.',
        'approvals' => 'Choose what an admin must approve before it appears on the website. When a setting is off, it goes live straight away.',
    ];
    $canEdit = auth()->user()->can('settings.edit');
@endphp
<x-layouts.admin title="Settings">
    <x-admin.heading title="Settings" description="General, Manuscript, Payment and SEO & Social configuration for the platform." />

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-8 space-y-8"
        x-data="{ tab: @js(request('tab', 'general')) }">
        @csrf
        @method('PUT')

        <div class="flex flex-wrap gap-1 border-b border-border">
            @foreach ($groups as $group => $definition)
                <button type="button" @click="tab = @js($group)"
                    :class="tab === @js($group) ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="label-caps -mb-px border-b-2 px-4 py-3 text-sm">{{ $definition['label'] }}</button>
            @endforeach
        </div>

        @foreach ($groups as $group => $definition)
            <div x-show="tab === @js($group)" @if (! $loop->first) x-cloak @endif>
                <x-admin.panel :title="$definition['label']" :description="$descriptions[$group]">
                    {{-- Three fields per row on wide screens, two on tablets; 'span' => 'full' fields take the whole row. --}}
                    <fieldset @disabled(! $canEdit) class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($definition['fields'] as $key => $field)
                            @php
                                $name = "{$group}_{$key}";
                                $value = $values["{$group}.{$key}"] ?? $field['default'];
                                $span = ($field['span'] ?? null) === 'full' || $field['type'] === 'textarea' ? 'md:col-span-2 xl:col-span-3' : null;
                            @endphp
                            @switch($field['type'])
                                @case('boolean')
                                    <label @class(['flex items-center justify-between gap-4 border border-border px-4 py-3', $span])>
                                        <span class="text-base">{{ $field['label'] }}</span>
                                        <input type="hidden" name="{{ $name }}" value="0">
                                        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $value) == '1') class="h-5 w-5 accent-primary">
                                    </label>
                                    @break
                                @case('image')
                                    <x-form.image :name="$name" :label="$field['label']" :current="$value ?: null" :class="$span" />
                                    @break
                                @case('textarea')
                                    <x-form.textarea :name="$name" :label="$field['label']" :value="$value" :class="$span" rows="3" />
                                    @break
                                @case('select')
                                    <x-form.select :name="$name" :label="$field['label']" :options="array_combine($field['options'], $field['options'])" :value="$value" :class="$span" />
                                    @break
                                @case('timezone')
                                    <x-form.select :name="$name" :label="$field['label']" :options="$timezones" :value="$value" :class="$span" />
                                    @break
                                @default
                                    <x-form.input :name="$name" :label="$field['label']" :value="$value"
                                        :type="match ($field['type']) { 'number' => 'number', 'email' => 'email', 'url' => 'url', default => 'text' }"
                                        :step="$field['type'] === 'number' ? '0.01' : null" :class="$span" />
                            @endswitch
                        @endforeach
                    </fieldset>
                </x-admin.panel>
            </div>
        @endforeach

        @if ($canEdit)
            <x-button type="submit" variant="primary" icon="save">Save settings</x-button>
        @endif
    </form>
</x-layouts.admin>
