<x-layouts.admin title="Best Paper Awards">
    <x-admin.heading title="Best Paper Awards" description="Choose the winning published manuscript for each quarter. One winner per quarter; the winner appears on the Best Paper page and the home page.">
        <x-slot:actions>
            <x-button :href="route('admin.best-paper-awards.create')" variant="primary" icon="plus">Choose a winner</x-button>
        </x-slot:actions>
    </x-admin.heading>

    {{-- Last quarter at a glance --}}
    <div class="mt-6">
        @foreach ([$lastQuarter] as $period)
            <div @class(['flex items-center justify-between gap-4 border bg-card p-5', 'border-gold/60' => $period['award'], 'border-dashed border-border' => ! $period['award']])>
                <div class="min-w-0">
                    <p class="label-caps text-xs text-muted-foreground">Last quarter · {{ $period['period'] }}</p>
                    @if ($period['award'])
                        <p class="mt-1 flex items-center gap-2 font-medium"><x-icon name="award" class="h-4 w-4 shrink-0 text-gold" /> <span class="truncate">{{ $period['award']->submission->title }}</span></p>
                    @else
                        <p class="mt-1 text-muted-foreground">No winner chosen yet.</p>
                    @endif
                </div>
                @if ($period['award'])
                    <x-button :href="route('admin.best-paper-awards.edit', $period['award'])" size="sm" icon="pencil">Edit</x-button>
                @else
                    <x-button :href="route('admin.best-paper-awards.create', $period['query'])" size="sm" variant="gold" icon="award">Choose winner</x-button>
                @endif
            </div>
        @endforeach
    </div>

    <x-filter-bar class="mt-2">
        <x-filter.search placeholder="Search manuscript title or ID…" />
        <x-filter.select name="year" label="Year" :options="$years" />
        <x-filter.select name="category" label="Category" :options="$categories" />
    </x-filter-bar>

    <x-table :columns="['Quarter', 'Manuscript', 'Author', 'Category', 'Prize', 'Chosen', 'Actions']" :rows="$awards" empty="No Best Paper winners yet. Use “Choose a winner” to add the first one.">
        @foreach ($awards as $award)
            <tr>
                <td class="whitespace-nowrap font-medium">{{ $award->periodLabel() }}</td>
                <td class="max-w-sm">
                    <a href="{{ route('admin.submissions.show', $award->submission) }}" class="hover:text-primary">
                        <span class="block font-mono text-xs text-muted-foreground">{{ $award->submission->reference() }}</span>
                        <span class="line-clamp-2">{{ $award->submission->title }}</span>
                    </a>
                </td>
                <td class="whitespace-nowrap">{{ $award->submission->author?->name }}</td>
                <td class="text-sm">{{ $award->submission->contentCategory?->name }}</td>
                <td class="whitespace-nowrap">{!! $award->hasPrize() ? e(money($award->prize_amount)) : '<span class="text-muted-foreground">—</span>' !!}</td>
                <td class="whitespace-nowrap text-sm text-muted-foreground">{{ format_date($award->selected_at) }}@if ($award->selector)<span class="block text-xs">by {{ $award->selector->name }}</span>@endif</td>
                <td>
                    <x-row-actions>
                        <x-action-link :href="route('admin.best-paper-awards.edit', $award)" icon="pencil">Edit</x-action-link>
                        <x-action-link :href="route('admin.submissions.show', $award->submission)" icon="eye">View manuscript</x-action-link>
                        <x-delete-button :action="route('admin.best-paper-awards.destroy', $award)" label="Remove" confirm="Remove this Best Paper award? The manuscript stays published." />
                    </x-row-actions>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $awards->links() }}
</x-layouts.admin>
