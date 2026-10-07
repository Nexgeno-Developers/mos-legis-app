@include('errors.layout', [
    'code' => 429,
    'title' => 'Too many requests',
    'description' => 'You\'ve made a lot of requests in a short time. Please wait a minute and try again.',
    'message' => null,
])
