{{-- resources/views/emails/form_submission.blade.php --}}

@component('mail::message')
# {{ ucfirst(str_replace('_', ' ', $formName)) }} Form Submission

@foreach($data as $key => $value)
**{{ ucwords(str_replace('_', ' ', $key)) }}:** {{ $value }}

@endforeach

Thanks,<br>
{{ config('app.name') }}
@endcomponent