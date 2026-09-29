@php $matches = $check->api_response['matches'] ?? []; @endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Plagiarism report #{{ $check->id }}</title>@include('pdf._styles')</head>
<body>
@if ($logo)<img src="{{ $logo }}" style="height:54px">@endif
<h2 class="crimson">{{ $appName }} — Plagiarism Report</h2>
<div class="rule"></div>
<table>
    <tr><td style="width:30%" class="caps muted">Check</td><td>#{{ $check->id }} · {{ ucfirst($check->check_type->value) }} check</td></tr>
    <tr><td class="caps muted">Title</td><td>{{ $check->title }}</td></tr>
    @if ($check->submission)<tr><td class="caps muted">Manuscript</td><td>{{ $check->submission->reference() }}</td></tr>@endif
    <tr><td class="caps muted">Requested by</td><td>{{ $check->user->name }} ({{ $check->user->email }})</td></tr>
    <tr><td class="caps muted">Checked at</td><td>{{ $check->checked_at?->format('d M Y H:i') }}</td></tr>
    <tr><td class="caps muted">Similarity</td><td><h1 class="{{ (float) $check->similarity_percentage > settings()->float('manuscript.plagiarism_max_similarity_percent') ? 'crimson' : '' }}">{{ number_format((float) $check->similarity_percentage, 2) }}%</h1></td></tr>
</table>
<h3 style="margin-top:18px">Matched sources</h3>
<table class="lines">
    <thead><tr><th>Source</th><th class="right">Similarity</th></tr></thead>
    <tbody>
        @forelse ($matches as $match)
            <tr><td>{{ $match['source'] ?? 'Unknown' }} @if (! empty($match['url']))<br><span class="muted">{{ $match['url'] }}</span>@endif</td><td class="right">{{ $match['similarity'] ?? '—' }}%</td></tr>
        @empty
            <tr><td colspan="2" class="muted">No matching sources were reported.</td></tr>
        @endforelse
    </tbody>
</table>
<p class="muted" style="margin-top:24px">Maximum accepted similarity for manuscript submissions: {{ settings('manuscript.plagiarism_max_similarity_percent') }}%.</p>
</body></html>
