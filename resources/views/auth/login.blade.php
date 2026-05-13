<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – {{ config('app.name', 'TaskFlow') }}</title>
    <meta name="description" content="Sign in to your TaskFlow account">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-card .login-submit {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        }
        .auth-card .login-submit:hover {
            background: #1d4ed8;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">✓</div>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to your TaskFlow account</p>
        </div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:12px;padding:12px 16px;margin-bottom:20px;color:#EF4444;font-size:13px;">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input"
                       value="{{ old('email') }}"
                       placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input"
                       placeholder="••••••••" required>
            </div>

            <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
                <input type="checkbox" id="remember" name="remember"
                       style="accent-color:#7C3AED;">
                <label for="remember" style="font-size:13px;color:#A89EC4;">Remember me</label>
            </div>

            <button type="submit" class="btn login-submit" style="width:100%;justify-content:center;">
                Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Create one</a>
        </div>
    </div>
</div>

</body>
</html>
