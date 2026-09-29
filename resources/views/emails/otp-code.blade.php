<x-mail::message>
# Verify your email

Use this code to finish creating your {{ settings('general.application_name') }} author account:

<x-mail::panel>
<span style="font-size: 28px; letter-spacing: 8px; font-weight: bold;">{{ $code }}</span>
</x-mail::panel>

The code expires in {{ $minutes }} minutes. If you did not request it, you can ignore this email.

{{ settings('general.application_name') }} Editorial Office
</x-mail::message>
