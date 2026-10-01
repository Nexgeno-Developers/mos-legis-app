{{-- Repeatable co-author names (stored as a JSON array). Starts empty; "Add co-author" adds a row. Expects $submission. --}}
<div class="mt-5" x-data="repeater(@js(collect(old('co_authors', $submission->co_authors ?? []))->filter()->map(fn ($n) => ['name' => $n])->values()), { name: '' }, 0)">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="label-caps text-xs text-muted-foreground">Co-authors</p>
            <p x-show="! rows.length" class="mt-1 text-sm text-muted-foreground">No co-authors added. Add anyone who wrote this manuscript with you.</p>
        </div>
        <x-button size="sm" icon="plus" @click="add()">Add co-author</x-button>
    </div>
    <template x-for="(row, index) in rows" :key="index">
        <div class="mt-2 flex gap-2">
            <input type="text" class="field-input" x-model="row.name" name="co_authors[]" :placeholder="'Co-author ' + (index + 1) + ' — full name'" maxlength="150">
            <button type="button" @click="remove(index)" class="px-2 text-muted-foreground hover:text-destructive" aria-label="Remove co-author">&times;</button>
        </div>
    </template>
    @error('co_authors.*')<p class="mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
</div>