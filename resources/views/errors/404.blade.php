@include('errors.layout', [
    'code' => 404,
    'title' => 'Page not found',
    'description' => 'The page you\'re looking for doesn\'t exist or may have moved. Check the address, or start again from the home page.',
    'message' => \App\Support\ErrorMessage::for($exception ?? null),
])
