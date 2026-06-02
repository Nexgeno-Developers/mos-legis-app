@props(['url'])
<tr>
<td class="header">
<a href="{{ config('app.url') }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ central_asset(uploaded_asset(get_setting('logo'))) }}" class="logo" alt="App Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>