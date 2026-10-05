<x-layouts.admin title="Content Category Themes">
    <x-admin.heading title="Manuscript Content Category Themes" description="Optional monthly or volume themes attached to a content category — when one is active, authors submitting under that category are guided to write to it.">
        <x-slot:actions>
            @can('themes.create')
                <x-button variant="primary" icon="plus" @click="$dispatch('open-modal', { name: 'theme' })">Add theme</x-button>
            @endcan
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search name, volume or period…" />
        <x-filter.select name="content_category_id" label="Content category" :options="$contentCategories" />
    </x-filter-bar>

    <x-table :columns="['Name', 'Volume', 'Date (Month & Year)', 'Content Category', 'Created Date', 'Actions']" :rows="$themes">
        @foreach ($themes as $theme)
            <tr>
                <td class="font-medium">
                    {{ $theme->name }}
                    @if ($theme->period->isSameMonth(now()))<x-badge tone="success" class="ml-2">Current</x-badge>@endif
                </td>
                <td>Vol. {{ $theme->volume }}</td>
                <td class="whitespace-nowrap">{{ $theme->periodLabel() }}</td>
                <td>{{ $theme->contentCategory->name }}</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($theme->created_at) }}</td>
                <td>
                    <x-row-actions>
                        @can('themes.edit')
                            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'theme', record: {{ Js::from([
                                'content_category_id' => (string) $theme->content_category_id, 'name' => $theme->name, 'volume' => $theme->volume,
                                'period' => $theme->period->format('Y-m'),
                                'action' => route('admin.themes.update', $theme), 'method' => 'PUT',
                            ]) }} })">Edit</x-action-link>
                        @endcan
                        @can('themes.delete')
                            <x-delete-button :action="route('admin.themes.destroy', $theme)" />
                        @endcan
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $themes->links() }}

    <x-admin.crud-modal name="theme" title-add="Add theme" title-edit="Edit theme"
        subtitle="One record covers the volume, theme name and publication period together."
        :store-url="route('admin.themes.store')"
        :defaults="['content_category_id' => '', 'name' => '', 'volume' => '', 'period' => now()->format('Y-m')]">
        <x-form.select name="content_category_id" label="Content category" :options="$contentCategories" placeholder="Select a content category" x-model="form.content_category_id" required />
        <x-form.input name="name" label="Theme name" placeholder="Artificial Intelligence in Law" x-model="form.name" required />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="volume" type="number" min="1" label="Volume" x-model="form.volume" required />
            <x-form.input name="period" type="month" label="Period" hint="Month & year, e.g. September 2026" x-model="form.period" required />
        </div>
    </x-admin.crud-modal>
</x-layouts.admin>
