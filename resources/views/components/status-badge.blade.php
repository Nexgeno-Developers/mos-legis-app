@props(['status'])
{{-- Renders any status enum (or string) with a sensible tone. --}}
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $label = $status instanceof \BackedEnum && method_exists($status, 'label') ? $status->label() : ucfirst(str_replace('_', ' ', $value));
    $tone = method_exists($status, 'tone') ? $status->tone() : match (strtolower($value)) {
        'active', 'published', 'approved', 'paid', 'completed' => 'success',
        'pending', 'processing', 'draft' => 'warning',
        'inactive', 'expired' => 'muted',
        'rejected', 'failed' => 'destructive',
        default => 'info',
    };
@endphp
<x-badge :tone="$tone" {{ $attributes }}>{{ $label }}</x-badge>
