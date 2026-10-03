{{--
    Branded HTML email frame used by every email (see App\Support\BrandedEmail).
    Brand: MOS Legis — crimson #b01b25, gold #c6a03c, ink #1a1714, parchment #f2ecdf, serif type.
    Email-safe: tables, inline styles, 600px max, no web fonts required (Georgia fallback).
    Variables: $subject, $body (HTML), $preheader, $action (['label' => …, 'url' => …] or null), $brand (array).
--}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light only">
    <title>{{ $subject }}</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; background: #f2ecdf; }
        table { border-collapse: collapse; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        .content p { margin: 0 0 16px; }
        .content a { color: #b01b25; text-decoration: underline; }
        .content strong { color: #1a1714; }
        .content ul, .content ol { margin: 0 0 16px; padding-left: 22px; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .px { padding-left: 22px !important; padding-right: 22px !important; }
            .h1 { font-size: 24px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f2ecdf;">
    {{-- Inbox preview text (hidden) --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f2ecdf;">{{ $preheader }}&#8202;&zwnj;&#8202;&zwnj;&#8202;&zwnj;</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2ecdf;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding:0 0 20px;">
                            <a href="{{ $brand['url'] }}" style="text-decoration:none;">
                                <img src="{{ $brand['logo'] }}" width="72" height="72" alt="{{ $brand['name'] }}" style="display:block; width:72px; height:72px; margin:0 auto;">
                            </a>
                            <div style="margin-top:10px; font-family:'Crimson Pro', Georgia, 'Times New Roman', serif; font-size:26px; font-weight:700; color:#b01b25; letter-spacing:0.2px;">{{ $brand['name'] }}</div>
                            <div style="margin-top:4px; font-family:Georgia, 'Times New Roman', serif; font-size:10px; letter-spacing:2.5px; text-transform:uppercase; color:#6b6255;">Rooted in Tradition &middot; Driven by Justice</div>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e3daca; border-top:4px solid #b01b25;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="px" style="padding:36px 44px 8px;">
                                        <div style="font-family:Georgia, 'Times New Roman', serif; font-size:10px; letter-spacing:2.5px; text-transform:uppercase; color:#c6a03c; font-weight:700;">{{ $brand['name'] }} &middot; Editorial Office</div>
                                        <h1 class="h1" style="margin:10px 0 0; font-family:'Crimson Pro', Georgia, 'Times New Roman', serif; font-size:28px; line-height:1.25; font-weight:700; color:#1a1714;">{{ $subject }}</h1>
                                        <div style="margin:18px 0 0; height:2px; width:56px; background:#c6a03c; line-height:2px; font-size:0;">&nbsp;</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px content" style="padding:24px 44px 12px; font-family:Georgia, 'Times New Roman', serif; font-size:16px; line-height:1.65; color:#1a1714;">
                                        {!! $body !!}
                                    </td>
                                </tr>
                                @if ($action)
                                    <tr>
                                        <td class="px" style="padding:4px 44px 28px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="background:#b01b25;">
                                                        <a href="{{ $action['url'] }}" style="display:inline-block; padding:13px 26px; font-family:Georgia, 'Times New Roman', serif; font-size:13px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#fdfbf6; text-decoration:none;">{{ $action['label'] }}</a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="px" style="padding:8px 44px 32px;">
                                        <div style="border-top:1px solid #e3daca; padding-top:18px; font-family:Georgia, 'Times New Roman', serif; font-size:13px; line-height:1.6; color:#6b6255;">
                                            Questions? Reply to this email or write to
                                            <a href="mailto:{{ $brand['email'] }}" style="color:#b01b25; text-decoration:underline;">{{ $brand['email'] }}</a>.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" class="px" style="padding:24px 24px 0; font-family:Georgia, 'Times New Roman', serif; font-size:12px; line-height:1.7; color:#6b6255;">
                            <div style="font-weight:700; color:#1a1714;">{{ $brand['name'] }} &middot; Editorial Office</div>
                            @if ($brand['address'])<div>{!! nl2br(e($brand['address'])) !!}</div>@endif
                            <div>
                                <a href="{{ $brand['url'] }}" style="color:#b01b25; text-decoration:none;">{{ $brand['domain'] }}</a>
                                @if ($brand['phone']) &nbsp;&middot;&nbsp; {{ $brand['phone'] }} @endif
                            </div>
                            @if ($brand['socials'])
                                <div style="margin-top:8px;">
                                    @foreach ($brand['socials'] as $network => $url)
                                        <a href="{{ $url }}" style="color:#6b6255; text-decoration:underline;">{{ $network }}</a>@if (! $loop->last) &nbsp;&middot;&nbsp; @endif
                                    @endforeach
                                </div>
                            @endif
                            <div style="margin-top:14px; font-size:11px; color:#8a8174;">
                                You are receiving this email about your activity on {{ $brand['name'] }}.<br>
                                &copy; {{ now()->year }} {{ $brand['name'] }}. All rights reserved.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
