@extends('backend.layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
    <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                <div class="mb-3">
                    <img src="{{ backend_logo_url() }}" alt="logo" class="img-fluid" style="max-height: 60px;">
                </div>
                <h4 class="fw-semibold mb-2 fs-18">Log in to your account</h4>

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

                <form action="{{ route('backend.login.submit') }}" method="POST" onsubmit="protect_with_recaptcha_v3(this, 'login')" class="text-start mb-3">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('backend.password.request') }}" class="fs-12">Forgot password?</a>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary fw-semibold" type="submit">Login</button>
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