<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>{{ __('Admin login') }} - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/password-toggle.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b 60%, #334155);
            min-height: 100vh;
        }
        .admin-login-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }
        .admin-login-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #fff;
            font-weight: 700;
        }
        .admin-login-field .form-label {
            font-weight: 500;
            margin-bottom: 0.35rem;
        }
        .admin-login-field .form-control-lg {
            border-radius: 10px;
        }
        .admin-login-field .password-toggle-wrap__btn {
            right: 0.75rem;
        }
    </style>
</head>
<body class="d-flex align-items-center py-5">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                <div class="card admin-login-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="admin-login-logo">{{ strtoupper(substr(config('app.name', 'A'), 0, 1)) }}</span>
                            <h1 class="h4 mt-3 mb-1">{{ __('Admin login') }}</h1>
                            <p class="text-muted small mb-0">{{ __('Sign in to access the admin dashboard') }}</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 small mb-3">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
                            @csrf
                            <div class="admin-login-field mb-3">
                                <label for="email" class="form-label">{{ __('Email') }}</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    required
                                    autofocus
                                    autocomplete="username"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="admin-login-field mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                @include('partials.password-input', [
                                    'id' => 'password',
                                    'name' => 'password',
                                    'inputClass' => 'form-control-lg'.($errors->has('password') ? ' is-invalid' : ''),
                                    'required' => true,
                                    'autocomplete' => 'current-password',
                                ])
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                            </div>
                            <button type="submit" class="btn btn-dark btn-lg w-100">{{ __('Login as admin') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @stack('scripts')
</body>
</html>
