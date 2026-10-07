@include('errors.layout', [
    'code' => 403,
    'title' => 'Access denied',
    'description' => 'You don\'t have permission to view this page. If you think this is a mistake, sign in with the right account or contact the editorial office.',
    'message' => \App\Support\ErrorMessage::for($exception ?? null),
])
