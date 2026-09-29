{{-- Repeatable co-author names (stored as a JSON array). Expects $submission. --}}
<div class="mt-5" x-data="repeater(@js(collect(old('co_authors', $submission->co_authors ?? []))->map(fn ($n) => ['name' => $n])->values()), { name: '' })">
    <div class="flex items-center justify-between">
        <p class="label-caps text-xs text-muted-foreground">Co-authors</p>
        <x-button size="sm" icon="plus" @click="add()">Add co-author</x-button>
    </div>
    <template x-for="(row, index) in rows" :key="index">
        <div class="mt-2 flex gap-2">
            <input type="text" class="field-input" x-model="row.name" name="co_authors[]" placeholder="Full name">
            <button type="button" @click="remove(index)" class="px-2 text-muted-foreground hover:text-destructive" aria-label="Remove co-author">&times;</button>
        </div>
    </template>
    @error('co_authors.*')<p class="mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
</div>
