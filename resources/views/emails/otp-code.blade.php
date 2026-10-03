{{-- Body of the registration OTP email; wrapped in the branded frame by OtpCodeMail. --}}
<p>Use this code to finish creating your {{ settings('general.application_name') }} author account:</p>

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:8px 0 20px;">
    <tr>
        <td align="center" style="background:#f2ecdf; border:1px solid #e3daca; border-left:4px solid #c6a03c; padding:20px;">
            <span style="font-family:'Courier New', Courier, monospace; font-size:32px; font-weight:700; letter-spacing:10px; color:#b01b25;">{{ $code }}</span>
        </td>
    </tr>
</table>

<p>The code expires in <strong>{{ $minutes }} minutes</strong>. If you did not request it, you can safely ignore this email — no account will be created.</p>
