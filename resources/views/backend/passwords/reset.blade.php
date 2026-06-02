@extends('backend.layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
    <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                <div class="mb-3">
                    <img src="{{ backend_logo_url() }}" alt="logo" class="img-fluid" style="max-height: 60px;">
                </div>
                <h4 class="fw-semibold mb-2 fs-18">Reset your password</h4>
                <p class="text-muted mb-3 fs-12">
                    Choose a strong password that you don’t use elsewhere.
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

                <form action="{{ route('backend.password.update') }}" method="POST" class="text-start mb-3">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="{{ old('email', $email ?? '') }}"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">New password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter new password"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirm new password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Re-enter new password"
                            required
                        >
                    </div>

                    <div class="d-grid mb-2">
                        <button class="btn btn-primary fw-semibold" type="submit">Update password</button>
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

