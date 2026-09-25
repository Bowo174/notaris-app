<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | Notaris App</title>
    <link href="{{ asset('template_dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="{{ asset('template_dashboard/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        body { font-family: Nunito, sans-serif; background: #f4f6f8; }
        .login-panel { max-width: 470px; }
        .brand-mark { width: 52px; height: 52px; display: grid; place-items: center; margin: 0 auto 1rem; border-radius: 50%; background: #174a45; color: #fff; font-size: 1.25rem; }
        .btn-notaris { background: #174a45; border-color: #174a45; color: #fff; }
        .btn-notaris:hover { background: #103a36; border-color: #103a36; color: #fff; }
        .form-control-user { border-radius: .35rem; }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center">
    <main class="container py-5">
        <div class="login-panel mx-auto">
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="brand-mark"><i class="fas fa-balance-scale" aria-hidden="true"></i></div>
                        <h1 class="h4 text-gray-900 mb-1">Notaris App</h1>
                        <p class="text-muted mb-0">Masuk ke akun Anda</p>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Alamat email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control form-control-user @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="password">Kata sandi</label>
                            <input id="password" name="password" type="password" class="form-control form-control-user @error('password') is-invalid @enderror" autocomplete="current-password" required>
                        </div>
                        <div class="custom-control custom-checkbox small mb-4">
                            <input id="remember" name="remember" type="checkbox" class="custom-control-input" value="1">
                            <label class="custom-control-label" for="remember">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-notaris btn-user btn-block">Masuk</button>
                    </form>
                </div>
            </section>
            <p class="text-center text-muted small mt-4 mb-0">Akses khusus pengguna terdaftar</p>
        </div>
    </main>
</body>
</html>
