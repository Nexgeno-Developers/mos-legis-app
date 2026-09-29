@props(['tone' => 'muted'])
@php
    $tones = [
        'muted' => 'border-border text-muted-foreground',
        'success' => 'border-success/40 text-success',
        'warning' => 'border-warning/50 text-warning',
        'destructive' => 'border-destructive/40 text-destructive',
        'info' => 'border-info/40 text-info',
        'gold' => 'border-gold/60 text-gold',
        'primary' => 'border-primary/40 text-primary',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'label-caps inline-block whitespace-nowrap border px-2.5 py-0.5 text-[0.65rem] '.($tones[$tone] ?? $tones['muted'])]) }}>{{ $slot }}</span>
