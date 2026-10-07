@include('errors.layout', [
    'code' => 500,
    'title' => 'Something went wrong',
    'description' => 'An unexpected error occurred on our side. Please try again in a few minutes.',
    'message' => null,
])
