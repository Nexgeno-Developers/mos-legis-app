<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Log In | {{ config('custom.app_name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Log In | {{ config('custom.app_name') }}" name="description" />
        <meta content="{{ config('custom.author') }}" name="author" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/backend/img/favicon.ico') }}">

        @include('backend.partials.css')
        @include('backend.partials.js')
    </head>

    <body>
        @yield('content')
    </body>
</html>
