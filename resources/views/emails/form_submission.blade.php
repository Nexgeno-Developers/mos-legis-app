{{-- resources/views/emails/form_submission.blade.php --}}

@component('mail::message')
# {{ humanize($formName) }} Form Submission

@foreach($data as $key => $value)
**{{ humanize($key) }}:** {{ $value }}

@endforeach

Thanks,<br>
{{ config('app.name') }}
@endcomponent