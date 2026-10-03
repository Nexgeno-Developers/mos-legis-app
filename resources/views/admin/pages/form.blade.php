@php $editing = $page->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit page' : 'Add page'">
    <x-admin.heading :title="$editing ? 'Edit page · '.$page->title : 'Add page'" description="Standard page data plus the fields of the selected template.">
        <x-slot:actions>
            <x-button :href="route('admin.pages.index')" icon="arrow-left">Back to pages</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-8 xl:grid-cols-[1fr_22rem]">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-8">
            <x-admin.panel title="Page">
                <div class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form.input name="title" label="Title" :value="$page->title" required />
                        <x-form.input name="slug" label="Slug" :value="$page->slug" hint="Leave blank to generate from the title." />
                    </div>
                    <x-form.textarea name="excerpt" label="Excerpt / introduction" :value="$page->excerpt" rows="2" />
                    <x-form.rich-text name="content" label="Content" :value="$page->content" />
                </div>
            </x-admin.panel>

            @if ($fields)
                <x-admin.panel :title="$templates[$page->template->value].' fields'">
                    <div class="space-y-6">
                        @foreach ($fields as $key => $field)
                            @if ($field['type'] === 'sections')
                                @php $sections = App\Support\PageTemplates::teamSections(old("meta.{$key}", $page->exists ? $page->meta($key) : null)); @endphp
                                <div>
                                    <p class="label-caps text-xs text-muted-foreground">{{ $field['label'] }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">The small label and the heading shown above each group on the public page. Leave a field empty to hide it.</p>
                                    <div class="mt-3 divide-y divide-border border border-border bg-background">
                                        @foreach (App\Support\PageTemplates::TEAM_SECTIONS as $section => $default)
                                            <div class="grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
                                                <p class="font-medium sm:col-span-2">{{ $default['label'] }}</p>
                                                <x-form.input :name="'meta['.$key.']['.$section.'][label]'" label="Label" :value="$sections[$section]['label']" maxlength="60" />
                                                <x-form.input :name="'meta['.$key.']['.$section.'][heading]'" label="Heading" :value="$sections[$section]['heading']" maxlength="120" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($field['type'] === 'repeater')
                                @php
                                    $rows = old("meta.{$key}", $page->exists ? $page->meta($key, []) : []);
                                    // Select columns can take their option labels from another field (e.g. renamed team sections).
                                    foreach ($field['columns'] as $columnKey => $column) {
                                        if (isset($column['labels_from'])) {
                                            $field['columns'][$columnKey]['options'] = App\Support\PageTemplates::teamSectionNames($page->exists ? $page->meta($column['labels_from']) : null);
                                        }
                                    }
                                    $blank = array_map(fn ($column) => $column['default'] ?? '', $field['columns']);
                                    // Text and select columns share one row; textareas take the full width.
                                    $inline = min(3, max(1, collect($field['columns'])->where('type', '!=', 'textarea')->count()));
                                @endphp
                                <div x-data="repeater(@js(array_values((array) $rows)), @js($blank))">
                                    <div class="flex items-center justify-between">
                                        <p class="label-caps text-xs text-muted-foreground">{{ $field['label'] }}</p>
                                        <x-button size="sm" icon="plus" @click="add()">Add more</x-button>
                                    </div>
                                    <template x-for="(row, index) in rows" :key="index">
                                        <div @class(['mt-3 grid gap-3 border border-border bg-background p-4', 'md:grid-cols-2' => $inline === 2, 'md:grid-cols-3' => $inline === 3])>
                                            @foreach ($field['columns'] as $column => $definition)
                                                <label class="flex flex-col gap-1 {{ $definition['type'] === 'textarea' ? 'md:col-span-full' : '' }}">
                                                    <span class="label-caps text-xs text-muted-foreground">{{ $definition['label'] }}</span>
                                                    @if ($definition['type'] === 'textarea')
                                                        <textarea rows="2" class="field-input" x-model="row.{{ $column }}" :name="`meta[{{ $key }}][${index}][{{ $column }}]`"></textarea>
                                                    @elseif ($definition['type'] === 'select')
                                                        <select class="field-input" data-native x-model="row.{{ $column }}" :name="`meta[{{ $key }}][${index}][{{ $column }}]`">
                                                            @foreach ($definition['options'] as $optionValue => $optionLabel)
                                                                <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <input type="text" class="field-input" x-model="row.{{ $column }}" :name="`meta[{{ $key }}][${index}][{{ $column }}]`">
                                                    @endif
                                                </label>
                                            @endforeach
                                            <div class="md:col-span-full">
                                                <button type="button" @click="remove(index)" class="inline-flex items-center gap-1 text-sm text-destructive hover:underline">
                                                    <x-icon name="x" /> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    @error("meta.{$key}.*")<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror
                                </div>
                            @elseif ($field['type'] === 'textarea')
                                <x-form.textarea :name="'meta['.$key.']'" :label="$field['label']" :value="$page->exists ? $page->meta($key) : null" rows="3" />
                            @else
                                <x-form.input :name="'meta['.$key.']'" :type="$field['type'] === 'email' ? 'email' : 'text'" :label="$field['label']" :hint="$field['hint'] ?? null" :value="$page->exists ? $page->meta($key) : null" />
                            @endif
                        @endforeach
                    </div>
                </x-admin.panel>
            @endif
        </div>

        <div class="space-y-8">
            <x-admin.panel title="Publishing">
                <div class="space-y-5">
                    @if ($editing)
                        <x-dl class="sm:grid-cols-1!" :items="['Template' => e($templates[$page->template->value]), 'Last edited by' => e($page->editor?->name ?? '—'), 'Updated' => format_date($page->updated_at, true)]" />
                    @else
                        <x-form.field label="Template" name="template" required hint="Fixed once the page is created.">
                            <select name="template" id="template" class="field-input" onchange="window.location = '{{ route('admin.pages.create') }}?template=' + this.value">
                                @foreach ($templates as $value => $label)
                                    <option value="{{ $value }}" @selected($page->template->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>
                    @endif
                    <x-form.select name="status" label="Status" :options="App\Enums\PublishStatus::options()" :value="$page->status" required />
                    <x-form.image name="featured_image" label="Featured image" :current="$page->featured_image" />
                    <x-button type="submit" variant="primary" icon="save" class="w-full">{{ $editing ? 'Save page' : 'Create page' }}</x-button>
                    @if ($editing && $page->status->value === 'Published')
                        <a href="{{ url($page->slug === 'home' ? '/' : $page->slug) }}" target="_blank" class="flex items-center justify-center gap-1 text-sm text-primary hover:underline"><x-icon name="external-link" /> View on site</a>
                    @endif
                </div>
            </x-admin.panel>

            <x-admin.panel title="SEO">
                <div class="space-y-5">
                    <x-form.input name="seo_title" label="Meta title" :value="$page->seo_title" />
                    <x-form.textarea name="seo_description" label="Meta description" :value="$page->seo_description" rows="3" />
                    <x-form.image name="og_image" label="OG image" :current="$page->og_image" />
                </div>
            </x-admin.panel>
        </div>
    </form>
</x-layouts.admin>
