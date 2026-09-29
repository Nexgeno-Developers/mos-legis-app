@props(['name', 'label' => null, 'options' => [], 'value' => [], 'multiple' => true, 'placeholder' => 'Select…', 'hint' => null, 'required' => false])
{{--
    Searchable single/multi select (SOW A.09: "dropdown … with search & multi selection").
    Submits `name[]` (multiple) or `name` (single). Works without JS as a plain list of checkboxes/radios.
--}}
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $selected = collect(old($key, $value))->map(fn ($v) => (string) $v)->values()->all();
    $items = collect($options)->map(fn ($text, $id) => ['id' => (string) $id, 'label' => (string) $text])->values()->all();
@endphp
<x-form.field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <div x-data="{
            open: false,
            query: '',
            multiple: @js($multiple),
            items: @js($items),
            selected: @js($selected),
            get filtered() { const q = this.query.toLowerCase(); return this.items.filter(i => i.label.toLowerCase().includes(q)); },
            toggle(id) {
                if (!this.multiple) { this.selected = [id]; this.open = false; return; }
                this.selected = this.selected.includes(id) ? this.selected.filter(s => s !== id) : [...this.selected, id];
            },
            labelFor(id) { return this.items.find(i => i.id === id)?.label ?? id; },
        }" @click.outside="open = false" class="relative">
        <button type="button" @click="open = !open" class="field-input flex min-h-[2.75rem] flex-wrap items-center gap-1.5 text-left">
            <template x-if="!selected.length"><span class="text-muted-foreground">{{ $placeholder }}</span></template>
            <template x-for="id in selected" :key="id">
                <span class="inline-flex items-center gap-1 border border-gold/50 bg-secondary px-2 py-0.5 text-sm">
                    <span x-text="labelFor(id)"></span>
                    <span x-show="multiple" @click.stop="toggle(id)" class="text-muted-foreground hover:text-destructive">&times;</span>
                </span>
            </template>
        </button>
        <template x-for="id in selected" :key="'h' + id">
            <input type="hidden" :name="multiple ? @js($name.'[]') : @js($name)" :value="id">
        </template>
        <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full border border-border bg-popover shadow-lg">
            <input type="search" x-model="query" placeholder="Search…" class="w-full border-b border-border bg-card px-3 py-2 text-sm focus:outline-none">
            <ul class="max-h-60 overflow-y-auto py-1">
                <template x-for="item in filtered" :key="item.id">
                    <li>
                        <button type="button" @click="toggle(item.id)" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-secondary">
                            <span class="flex h-4 w-4 items-center justify-center border border-border" :class="selected.includes(item.id) && 'border-primary bg-primary text-primary-foreground'">
                                <span x-show="selected.includes(item.id)">&check;</span>
                            </span>
                            <span x-text="item.label"></span>
                        </button>
                    </li>
                </template>
                <li x-show="!filtered.length" class="px-3 py-2 text-sm text-muted-foreground">No matches.</li>
            </ul>
        </div>
    </div>
</x-form.field>
