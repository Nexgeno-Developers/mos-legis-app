@props(['stage'])
{{-- Visual progress of a manuscript through the SOW A.16 pipeline. --}}
@php
    $steps = [
        'pending' => 'Submitted',
        'plagiarism_accepted' => 'Screened',
        'in_review' => 'In review',
        'approved' => 'Approved',
        'published' => 'Published',
    ];
    $position = match ($stage->value) {
        'pending' => 0, 'plagiarism_accepted' => 1, 'in_review', 'revision', 'resubmitted' => 2, 'approved' => 3, 'published' => 4, default => -1,
    };
@endphp
@if ($stage->value === 'rejected')
    <div class="border-l-2 border-destructive bg-card px-4 py-3 text-destructive">This manuscript was not accepted.</div>
@else
    <ol class="grid grid-cols-5 gap-2" aria-label="Manuscript progress">
        @foreach (array_values($steps) as $i => $label)
            <li class="text-center">
                <span @class(['block h-1.5', 'bg-primary' => $i <= $position, 'bg-border' => $i > $position])></span>
                <span @class(['label-caps mt-2 block text-[0.65rem]', 'text-primary' => $i === $position, 'text-muted-foreground' => $i !== $position])>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
    @if (in_array($stage->value, ['revision', 'resubmitted'], true))
        <p class="mt-3 text-sm text-warning">{{ $stage->value === 'revision' ? 'Revision requested — upload your revised manuscript below.' : 'Revised manuscript with the reviewer.' }}</p>
    @endif
@endif
