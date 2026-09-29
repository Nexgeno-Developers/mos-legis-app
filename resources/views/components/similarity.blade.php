@props(['value', 'threshold' => null])
@php $threshold ??= settings()->float('manuscript.plagiarism_max_similarity_percent'); @endphp
@if ($value === null)
    <span class="text-sm text-muted-foreground">—</span>
@else
    <span @class(['font-mono text-sm', 'text-destructive' => (float) $value > $threshold, 'text-success' => (float) $value <= $threshold])>{{ number_format((float) $value, 2) }}%</span>
@endif
