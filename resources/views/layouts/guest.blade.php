<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | Absensi Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }
        .brand-icon {
            width: 64px;
            height: 64px;
            font-size: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #2563eb;
            color: #fff;
            border-radius: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card auth-card mx-auto">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="brand-icon mb-3"><i class="bi bi-fingerprint"></i></span>
                    <h1 class="h4 mb-1 fw-bold">Absensi Karyawan</h1>
                    <p class="text-muted small mb-0">Silakan masuk untuk melanjutkan</p>
                </div>
                @yield('content')
            </div>
            <div class="card-footer text-center bg-white rounded-bottom">
                <small class="text-muted">&copy; {{ date('Y') }} Sistem Absensi Karyawan</small>
            </div>
        </div>
    </div>
</body>
</html>