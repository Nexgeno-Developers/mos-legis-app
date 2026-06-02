@component('mail::message')
# New User Registration

A new user has registered on {{ config('app.name') }}.

**User Details:**

- **Name:** {{ $user->name }}
- **Email:** {{ $user->email }}
- **Phone:** {{ $user->phone ?? 'Not provided' }}
- **Location:** {{ $user->location ?? 'Not provided' }}
- **Registration Method:** {{ $registrationMethod }}
- **Registration Date:** {{ $user->created_at->format('M d, Y H:i:s') }}

@if($registrationMethod === 'Google')
This user registered using Google OAuth and their email is already verified.
@else
This user registered using the registration form and will need to verify their email address.
@endif

Thanks,
{{ config('app.name') }}
@endcomponent