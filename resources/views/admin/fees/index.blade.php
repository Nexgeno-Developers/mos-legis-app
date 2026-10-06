<x-layouts.admin title="Manuscript Fees">
    <x-admin.heading title="Manuscript Fees" description="Publication fee configured for every Author Category × Content Category combination. Blank cells are treated as not offered." />

    @if ($authorCategories->isEmpty() || $contentCategories->isEmpty())
        <p class="mt-8 border-l-2 border-gold/60 pl-4 text-muted-foreground">
            Add at least one author category and one content category to configure fees.
        </p>
    @else
        <form method="POST" action="{{ route('admin.fees.update') }}" class="mt-8">
            @csrf
            @method('PUT')
            @error('fees')<p class="mb-4 text-sm text-destructive">{{ $message }}</p>@enderror

            <div class="overflow-x-auto border border-border bg-card">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="label-caps sticky left-0 bg-card px-5 py-4 text-xs text-muted-foreground">Author category ↓ / Content →</th>
                            @foreach ($contentCategories as $content)
                                <th class="label-caps min-w-40 px-4 py-4 text-xs text-muted-foreground">
                                    {{ $content->name }}
                                    @unless ($content->isActive())<span class="block text-[0.6rem] text-warning">Inactive</span>@endunless
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($authorCategories as $author)
                            <tr class="border-b border-border last:border-0">
                                <th scope="row" class="sticky left-0 bg-card px-5 py-3 text-base font-medium">
                                    {{ $author->name }}
                                    @unless ($author->isActive())<span class="block text-xs text-warning">Inactive</span>@endunless
                                </th>
                                @foreach ($contentCategories as $content)
                                    @php $key = "{$author->id}-{$content->id}"; @endphp
                                    <td class="px-4 py-3">
                                        <label class="flex items-center border border-border bg-background focus-within:border-gold">
                                            <span class="px-2 text-muted-foreground">{{ settings('payment.currency_symbol') }}</span>
                                            <input type="number" min="0" step="0.01" placeholder="—"
                                                name="fees[{{ $author->id }}][{{ $content->id }}]"
                                                value="{{ old("fees.{$author->id}.{$content->id}", isset($fees[$key]) ? (float) $fees[$key] : '') }}"
                                                aria-label="Fee for {{ $author->name }} × {{ $content->name }}"
                                                class="w-full bg-transparent py-2 pr-2 text-base focus:outline-none" @cannot('fees.edit') disabled @endcannot>
                                        </label>
                                        @error("fees.{$author->id}.{$content->id}")<p class="mt-1 text-xs text-destructive">{{ $message }}</p>@enderror
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Co-author surcharge: added to the publication fee for each co-author named on the manuscript. --}}
            <div class="mt-10">
                <h2 class="font-display text-2xl">Co-Author Surcharge</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Added to the publication fee for every co-author: the 1st and 2nd co-authors each pay the first rate, every co-author from the 3rd on pays the second rate.
                    Leave both blank for no surcharge.
                </p>
                @error('coauthor_fees')<p class="mt-3 text-sm text-destructive">{{ $message }}</p>@enderror

                <div class="mt-4 overflow-x-auto border border-border bg-card">
                    <table class="w-full min-w-[560px] text-left">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="label-caps px-5 py-4 text-xs text-muted-foreground">Content category</th>
                                <th class="label-caps w-56 px-4 py-4 text-xs text-muted-foreground">1st / 2nd co-author <span class="normal-case tracking-normal">(each)</span></th>
                                <th class="label-caps w-56 px-4 py-4 text-xs text-muted-foreground">3rd co-author onwards <span class="normal-case tracking-normal">(each)</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contentCategories as $content)
                                @php $rates = $coAuthorFees[$content->id] ?? null; @endphp
                                <tr class="border-b border-border last:border-0">
                                    <th scope="row" class="px-5 py-3 text-base font-medium">
                                        {{ $content->name }}
                                        @unless ($content->isActive())<span class="block text-xs text-warning">Inactive</span>@endunless
                                    </th>
                                    @foreach (['first_two' => ['first_two_fee', '1st / 2nd co-author'], 'additional' => ['additional_fee', '3rd co-author onwards']] as $field => [$column, $label])
                                        <td class="px-4 py-3">
                                            <label class="flex items-center border border-border bg-background focus-within:border-gold">
                                                <span class="px-2 text-muted-foreground">{{ settings('payment.currency_symbol') }}</span>
                                                <input type="number" min="0" step="0.01" placeholder="—"
                                                    name="coauthor_fees[{{ $content->id }}][{{ $field }}]"
                                                    value="{{ old("coauthor_fees.{$content->id}.{$field}", $rates ? (float) $rates->{$column} : '') }}"
                                                    aria-label="{{ $label }} surcharge for {{ $content->name }}"
                                                    class="w-full bg-transparent py-2 pr-2 text-base focus:outline-none" @cannot('fees.edit') disabled @endcannot>
                                            </label>
                                            @error("coauthor_fees.{$content->id}.{$field}")<p class="mt-1 text-xs text-destructive">{{ $message }}</p>@enderror
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @can('fees.edit')
                <div class="mt-6"><x-button type="submit" variant="primary" icon="save">Save fees</x-button></div>
            @endcan
        </form>
        <p class="mt-6 border-l-2 border-gold/60 pl-4 text-sm text-muted-foreground">
            The plagiarism pre-screening fee ({{ money(settings('manuscript.plagiarism_prescreening_fee')) }}) is set in Settings → Manuscript.
            Publication fees (including any co-author surcharge) are charged after a manuscript is approved and are inclusive of taxes.
        </p>
    @endif
</x-layouts.admin>
