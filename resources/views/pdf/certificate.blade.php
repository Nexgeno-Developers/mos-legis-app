@php $s = $certificate->snapshot_json; @endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $certificate->certificate_number }}</title>
@include('pdf._styles')
<style>
    @page { margin: 22px; }
    .frame { border: 3px double #C6A03C; padding: 34px 50px; height: 88%; text-align: center; }
    .script { font-family: 'Times New Roman', serif; font-style: italic; font-size: 30px; color: #B01B25; }
    .title { font-size: 20px; font-weight: bold; margin: 8px 0; }
</style>
</head>
<body>
<div class="frame">
    @if ($logo)<img src="{{ $logo }}" style="height:70px">@endif
    <div class="caps crimson" style="font-size:11px">{{ $appName }}</div>
    <h1 style="font-size:34px; margin-top:6px">Certificate of Publication</h1>
    <div class="rule" style="width:40%; margin:14px auto"></div>
    <p class="muted">This is to certify that the manuscript</p>
    <div class="title">“{{ $s['title'] }}”</div>
    <p class="muted">authored by</p>
    <div class="script">{{ $s['author'] }}</div>
    @if (! empty($s['co_authors']))<p>with {{ implode(', ', $s['co_authors']) }}</p>@endif
    @if (! empty($s['institution']))<p class="muted">{{ $s['institution'] }}</p>@endif
    <p>has been peer reviewed and published under <strong>{{ $s['content_category'] }}</strong>
        on {{ \Illuminate\Support\Carbon::parse($s['published_at'])->format('d F Y') }}
        @if (! empty($s['volume'])) in Volume {{ $s['volume'] }} — {{ $s['theme'] }} ({{ $s['theme_period'] }}) @endif.</p>
    <table style="margin-top:26px">
        <tr>
            <td style="width:33%" class="center"><div class="caps muted">Certificate No.</div>{{ $certificate->certificate_number }}</td>
            <td style="width:34%; text-align:center"><div class="caps muted">Manuscript ID</div>{{ $s['reference'] }}</td>
            <td style="width:33%; text-align:right"><div class="caps muted">Issued on</div>{{ $certificate->issued_at->format('d M Y') }}</td>
        </tr>
    </table>
    <p class="muted" style="margin-top:18px; font-size:10px">Verify this certificate at {{ $certificate->verificationUrl() }}</p>
</div>
</body></html>
