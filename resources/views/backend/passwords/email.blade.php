@extends('backend.layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
    <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                <div class="mb-3">
                    <img src="{{ backend_logo_url() }}" alt="logo" class="img-fluid" style="max-height: 60px;">
                </div>
                <h4 class="fw-semibold mb-2 fs-18">Forgot your password?</h4>
                <p class="text-muted mb-3 fs-12">
                    Enter your email address and we will send you a secure link to reset your password.
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ol class="mb-0 fs-12 text-start">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('backend.password.email') }}" method="POST" onsubmit="protect_with_recaptcha_v3(this, 'password_email')" class="text-start mb-3">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>

                    <div class="d-grid mb-2">
                        <button class="btn btn-primary fw-semibold" type="submit">Send reset link</button>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('backend.login') }}" class="fs-12">Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script defer>
    $(document).ready(function() {
        initValidate('form');
    });
</script>
@endsection

