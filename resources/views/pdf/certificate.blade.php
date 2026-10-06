@php
    $s = $certificate->snapshot_json;
    $published = \Illuminate\Support\Carbon::parse($s['published_at']);
    $details = array_filter([
        ! empty($s['volume']) ? 'Volume '.$s['volume'] : null,
        $s['content_category'] ?? null,
        $published->format('F Y'),
    ]);
@endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $certificate->certificate_number }}</title>
<style>
    {{-- A4 landscape: 842 × 595 pt. Layout uses absolute positions (dompdf has no flexbox). --}}
    @page { margin: 0; }
    body { margin: 0; font-family: 'Times New Roman', DejaVu Serif, serif; color: #1a1714; }
    .frame-outer { position: absolute; top: 16pt; left: 16pt; right: 16pt; bottom: 16pt; border: 4pt solid #B01B25; }
    .frame-inner { position: absolute; top: 24pt; left: 24pt; right: 24pt; bottom: 24pt; border: 1.2pt solid #C6A03C; }
    .watermark { position: absolute; top: 150pt; left: 0; right: 0; text-align: center; }
    .watermark img { width: 270pt; opacity: 0.07; }
    .watermark-text { font-size: 54pt; font-weight: bold; color: #B01B25; opacity: 0.05; letter-spacing: 4pt; }
    .content { position: absolute; top: 38pt; left: 70pt; right: 70pt; text-align: center; }
    .content.no-logo { top: 74pt; }
    .brand { font-size: 30pt; font-weight: bold; color: #B01B25; letter-spacing: 2pt; }
    .tagline { margin-top: 4pt; font-size: 9pt; font-style: italic; color: #B01B25; }
    .heading { margin-top: 12pt; font-size: 23pt; font-weight: bold; color: #B01B25; letter-spacing: 2.5pt; }
    .lead { margin-top: 8pt; font-size: 10.5pt; }
    .paper { margin-top: 5pt; font-size: 14pt; font-weight: bold; font-style: italic; color: #B01B25; line-height: 1.3; }
    .by { margin-top: 8pt; font-size: 9.5pt; font-weight: bold; color: #C6A03C; letter-spacing: 1.5pt; }
    .author { margin-top: 3pt; font-size: 18pt; font-weight: bold; color: #B01B25; }
    .coauthors { margin-top: 2pt; font-size: 10pt; color: #4a443d; }
    .published { margin-top: 8pt; font-size: 10pt; line-height: 1.5; }
    .rule { position: absolute; left: 50pt; right: 50pt; top: 452pt; border-top: 1pt solid #C6A03C; }
    .footer { position: absolute; left: 50pt; top: 462pt; width: 742pt; border-collapse: collapse; }
    .footer td { padding: 0; }
    .footer td { vertical-align: bottom; width: 33.33%; }
    .small { font-size: 7.5pt; color: #6f665c; font-style: italic; }
    .label { font-size: 8.5pt; font-weight: bold; color: #B01B25; }
    .number { font-size: 9.5pt; }
    .signatory { font-size: 10pt; font-weight: bold; color: #B01B25; }
    .sign-line { width: 140pt; margin: 0 0 4pt auto; border-top: 0.8pt solid #9a8f80; }
</style>
</head>
<body>
    <div class="frame-outer"></div>
    <div class="frame-inner"></div>

    {{-- Faint emblem behind the text --}}
    <div class="watermark">
        @if ($logo)<img src="{{ $logo }}" alt="">@else<div class="watermark-text">{{ strtoupper($appName) }}</div>@endif
    </div>

    <div class="content {{ $logo ? '' : 'no-logo' }}">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $appName }}" style="height: 100pt;">
        @else
            <div class="brand">{{ strtoupper($appName) }}</div>
        @endif
        <div class="tagline">Rooted in Tradition. Driven by Justice.</div>

        <div class="heading">CERTIFICATE OF PUBLICATION</div>
        <div class="lead">This is to certify that the paper titled</div>
        <div class="paper">{{ $s['title'] }}</div>

        <div class="by">AUTHORED BY</div>
        <div class="author">{{ $s['author'] }}</div>
        @if (! empty($s['co_authors']))<div class="coauthors">with {{ implode(', ', $s['co_authors']) }}</div>@endif

        <div class="published">
            has been published in {{ $appName }}@if ($issn) &nbsp;·&nbsp; ISSN: {{ $issn }}@endif<br>
            {{ implode('  ·  ', $details) }}
        </div>
    </div>

    <div class="rule"></div>

    <table class="footer">
        <tr>
            <td style="text-align: left;">
                <img src="{{ $qr }}" alt="QR code" style="width: 56pt; height: 56pt;"><br>
                <span class="small">Scan to verify on {{ $website }}</span>
            </td>
            <td style="text-align: center;">
                <div class="label">Certificate No.</div>
                <div class="number">{{ $certificate->certificate_number }}</div>
                <div class="small" style="font-style: normal;">{{ $website }}</div>
            </td>
            <td style="text-align: right;">
                @if ($signature)
                    <img src="{{ $signature }}" alt="" style="height: 40pt; max-width: 150pt;"><br>
                @else
                    <div class="sign-line"></div>
                @endif
                @if ($signatoryName)<div class="signatory">{{ $signatoryName }}</div>@endif
                <div class="small">{{ $signatoryTitle }}{{ $signatoryTitle ? ', ' : '' }}{{ $appName }}</div>
            </td>
        </tr>
    </table>
</body></html>
