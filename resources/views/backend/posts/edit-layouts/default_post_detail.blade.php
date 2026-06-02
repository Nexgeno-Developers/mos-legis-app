@php
    $layoutKey = 'default_post_detail';
@endphp

@include('backend.posts.partials.fields', ['layoutKey' => $layoutKey, 'postData' => $postData])
