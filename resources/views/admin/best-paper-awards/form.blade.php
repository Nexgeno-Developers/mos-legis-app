@php $editing = $award->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Best Paper award' : 'Choose a Best Paper winner'">
    <x-admin.heading :title="$editing ? 'Edit Best Paper award · '.$award->periodLabel() : 'Choose a Best Paper winner'"
        description="Pick the quarter and the winning published manuscript. The author is notified by email when their manuscript is chosen.">
        <x-slot:actions>
            <x-button :href="route('admin.best-paper-awards.index')" icon="arrow-left">Back to awards</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.best-paper-awards.update', $award) : route('admin.best-paper-awards.store') }}"
        class="mx-auto mt-8 max-w-3xl space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel title="1. Quarter" description="One Best Paper winner per quarter.">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.select name="award_quarter" label="Quarter" :options="['Q1' => 'Q1 (Jan – Mar)', 'Q2' => 'Q2 (Apr – Jun)', 'Q3' => 'Q3 (Jul – Sep)', 'Q4' => 'Q4 (Oct – Dec)']" :value="$award->award_quarter" required />
                <x-form.input name="award_year" type="number" label="Year" :value="$award->award_year" min="2000" max="2100" required />
            </div>
        </x-admin.panel>
        <x-admin.panel title="2. Winning manuscript" description="Only published manuscripts can win.">
            <div class="space-y-5">
                @if ($manuscripts->isEmpty())
                    <p class="border-l-2 border-warning bg-secondary px-4 py-3 text-sm">There are no published manuscripts yet. A manuscript can win once it is published.</p>
                @endif
                <x-form.select name="manuscript_submission_id" label="Manuscript" :options="$manuscripts" :value="$award->manuscript_submission_id"
                    placeholder="Search by ID, title or author…" required data-search />
                <x-form.textarea name="editorial_citation" label="Editorial citation" :value="$award->editorial_citation" rows="4" required
                    hint="Why this manuscript won — shown under the winner on the Best Paper page." />
                <x-form.input name="prize_amount" type="number" step="0.01" min="0" label="Cash prize (₹) — optional" :value="$award->prize_amount"
                    hint="Leave blank if there is no cash prize." />
            </div>
        </x-admin.panel>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" :icon="$editing ? 'save' : 'award'">{{ $editing ? 'Save award' : 'Save winner' }}</x-button>
            <x-button :href="route('admin.best-paper-awards.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
