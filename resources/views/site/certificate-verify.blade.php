<x-layouts.site title="Certificate verification">
    <x-page-header eyebrow="Verification" title="Publication Certificate" />
    <div class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 md:py-10">
        @if ($certificate)
            <div class="max-w-2xl border border-success/50 bg-card p-8">
                <p class="flex items-center gap-2 font-display text-2xl text-success"><x-icon name="badge-check" class="h-6 w-6" /> This certificate is valid</p>
                <x-dl class="mt-6" :items="[
                    'Certificate No.' => e($certificate->certificate_number),
                    'Manuscript' => e($certificate->snapshot_json['reference'] ?? ''),
                    'Title' => e($certificate->snapshot_json['title'] ?? ''),
                    'Author' => e($certificate->snapshot_json['author'] ?? ''),
                    'Category' => e($certificate->snapshot_json['content_category'] ?? ''),
                    'Published' => format_date($certificate->snapshot_json['published_at'] ?? null),
                    'Issued' => format_date($certificate->issued_at),
                ]" />
            </div>
        @else
            <div class="max-w-2xl border border-destructive/40 bg-card p-8">
                <p class="font-display text-2xl text-destructive">No certificate matches this link</p>
                <p class="mt-2 text-muted-foreground">Check the verification URL printed on the certificate, or <a href="{{ page_url('contact') }}" class="text-primary hover:underline">contact the editorial desk</a>.</p>
            </div>
        @endif
    </div>
</x-layouts.site>
