{{-- Final step of the ORCID pop-up: pass the result to the opening page, then close. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ORCID | {{ settings('general.application_name') }}</title>
    <style>body{font-family:Georgia,serif;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;background:#f5efe3;color:#2b2622;text-align:center;padding:1rem}a{color:#b11c26}</style>
</head>
<body>
    <p>{{ $payload['message'] }} <br><a href="{{ $returnTo }}">Continue</a></p>
    <script>
        (function () {
            var payload = @js($payload);
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(payload, @js(request()->getSchemeAndHttpHost()));
                window.close();
            } else {
                window.location.replace(@js($returnTo));
            }
        })();
    </script>
</body>
</html>
