@if (session('success') || session('error') || $errors->any())
<div class="alert alert-dismissible d-flex align-items-center border-2 
    {{ session('success') ? 'alert-success border-success' : (session('error') || $errors->any() ? 'alert-danger border-danger' : '') }}" 
    role="alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <iconify-icon icon="{{ session('success') ? 'solar:check-read-line-duotone' : 'solar:danger-triangle-bold-duotone' }}" class="fs-20 me-1"></iconify-icon>
    <div class="lh-1">
        @if (session('success'))
            <strong>Success - </strong> {{ session('success') }}
        @elseif (session('error'))
            <strong>Error - </strong> {{ session('error') }}
        @elseif ($errors->any())
            <strong>Error </strong>
            <ol class="mb-0 mt-1" style="padding-left: 15px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
@endif