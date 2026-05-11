<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Monitoring Gaji</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:    #4f46e5;
            --primary-dk: #3730a3;
            --danger:     #ef4444;
            --text:       #1e293b;
            --text-muted: #64748b;
            --border:     #e2e8f0;
            --bg:         #f1f5f9;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .25;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            background: #818cf8;
            top: -100px; left: -100px;
            animation: blob1 8s ease-in-out infinite;
        }
        body::after {
            width: 400px; height: 400px;
            background: #a78bfa;
            bottom: -80px; right: -80px;
            animation: blob2 10s ease-in-out infinite;
        }
        @keyframes blob1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%       { transform: translate(40px, 30px) scale(1.1); }
        }
        @keyframes blob2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%       { transform: translate(-30px, -20px) scale(1.08); }
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,.25);
            position: relative;
            z-index: 1;
            animation: cardIn .4s ease;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }

        /* Logo */
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #4f46e5, #818cf8);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(79,70,229,.35);
        }
        .login-logo h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
        }
        .login-logo p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Alert error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: #991b1b;
        }
        .alert-error i { margin-top: 1px; flex-shrink: 0; }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i.input-icon {
            position: absolute;
            left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            padding: 11px 12px 11px 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
        }
        .input-wrap input.is-error {
            border-color: var(--danger);
        }
        .input-wrap input.is-error:focus {
            box-shadow: 0 0 0 3px rgba(239,68,68,.12);
        }
        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94a3b8; cursor: pointer;
            font-size: 14px; padding: 4px;
        }
        .toggle-pw:hover { color: var(--text); }

        .field-error {
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Remember me */
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
        }
        .form-check input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .form-check label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(79,70,229,.35);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-dk), var(--primary));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79,70,229,.4);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled {
            opacity: .7; cursor: not-allowed; transform: none;
        }

        /* Divider info */
        .login-info {
            margin-top: 24px;
            padding: 14px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        .login-info p {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .credential-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text);
            padding: 3px 0;
        }
        .credential-row span:first-child { color: var(--text-muted); }
        .credential-row code {
            background: #e0e7ff;
            color: var(--primary-dk);
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Logo -->
    <div class="login-logo">
        <div class="logo-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <h1>Gaji sanpen</h1>
        <p>Sistem Monitoring Gaji Karyawan</p>
    </div>

    <!-- Alert error global -->
    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
        <div class="alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Form Login -->
    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
        @csrf

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrap">
                <i class="fas fa-envelope input-icon"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@payroll.com"
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'is-error' : '' }}"
                    required
                >
            </div>
            @error('email')
                <div class="field-error">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'is-error' : '' }}"
                    required
                >
                <button type="button" class="toggle-pw" id="togglePw" title="Tampilkan password">
                    <i class="fas fa-eye" id="togglePwIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="field-error">
                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember me -->
        <div class="form-check">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya</label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login" id="btnLogin">
            <i class="fas fa-right-to-bracket"></i>
            Masuk
        </button>
    </form>

    <div class="login-footer">
        &copy; {{ date('Y') }} PayrollApp — All rights reserved
    </div>
</div>

<script>
    // Toggle show/hide password
    const togglePw   = document.getElementById('togglePw');
    const pwInput    = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePwIcon');

    togglePw.addEventListener('click', () => {
        const isHidden = pwInput.type === 'password';
        pwInput.type       = isHidden ? 'text' : 'password';
        toggleIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Loading state saat submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    });
</script>

</body>
</html>
