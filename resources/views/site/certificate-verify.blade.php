{{-- Replaced by the full public layout in the public-site module. --}}
<x-layouts.auth title="Certificate verification" eyebrow="MOS Legis">
    @if ($certificate)
        <p class="text-center text-success">This certificate is valid.</p>
        <x-dl class="mt-6 sm:grid-cols-1!" :items="[
            'Certificate No.' => e($certificate->certificate_number),
            'Manuscript' => e($certificate->snapshot_json['reference'] ?? ''),
            'Title' => e($certificate->snapshot_json['title'] ?? ''),
            'Author' => e($certificate->snapshot_json['author'] ?? ''),
            'Issued on' => format_date($certificate->issued_at),
        ]" />
    @else
        <p class="text-center text-destructive">No certificate matches this verification link.</p>
    @endif
</x-layouts.auth>
