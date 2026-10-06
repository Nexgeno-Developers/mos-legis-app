{{-- Repeatable co-author names (stored as a JSON array). Starts empty; "Add co-author" adds a row. Expects $submission. --}}
<div class="mt-5" x-data="repeater(@js(collect(old('co_authors', $submission->co_authors ?? []))->filter()->map(fn ($n) => ['name' => $n])->values()), { name: '' }, 0)" x-effect="$dispatch('co-authors-changed', rows.filter(r => (r.name || '').trim() !== '').length)">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="label-caps text-xs text-muted-foreground">Co-authors</p>
            <p x-show="! rows.length" class="mt-1 text-sm text-muted-foreground">No co-authors added. Add anyone who wrote this manuscript with you.</p>
        </div>
        <x-button size="sm" icon="plus" @click="add()" x-bind:disabled="rows.length >= 10">Add co-author</x-button>
    </div>
    <div x-ref="list">
    <template x-for="(row, index) in rows" :key="index">
        <div data-row class="mt-2 flex items-center gap-2">
            <button type="button" data-drag-handle x-show="rows.length > 1" class="flex h-11 w-6 shrink-0 cursor-grab items-center justify-center text-muted-foreground hover:text-foreground active:cursor-grabbing" title="Drag to reorder" aria-label="Drag to reorder"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="9" cy="6" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="18" r="1"/></svg></button>
            <input type="text" class="field-input" x-model="row.name" name="co_authors[]" :placeholder="'Co-author ' + (index + 1) + ' — full name'" maxlength="150">
            <button type="button" @click="remove(index)" class="px-2 text-muted-foreground hover:text-destructive" aria-label="Remove co-author">&times;</button>
        </div>
    </template>
    </div>
    <p x-show="rows.length >= 10" x-cloak class="mt-2 text-sm text-muted-foreground">You can add up to 10 co-authors.</p>
    @error('co_authors.*')<p class="mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
</div>