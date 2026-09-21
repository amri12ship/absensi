<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Absensi Karyawan</title>

    {{-- Bootstrap 5.3 (CDN) — tanpa Vite agar aman tanpa build --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #172554;        /* blue-950 — navy Metopel */
            --sidebar-section: #60a5fa;   /* biru muda judul grup */
            --sidebar-active: #2563eb;    /* biru terang item aktif */
            --sidebar-text: #93c5fd;
        }

        body {
            background: #f1f5f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: .925rem;
        }

        /* ================= SIDEBAR (Metopel: navy berkelompok) ================= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background: var(--sidebar-bg);
            color: #e2e8f0;
            z-index: 1045;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: 1rem 1.25rem;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }
        .sidebar .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: .65rem;
            background: var(--sidebar-active);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar .menu-section {
            padding: 1rem 1.25rem .3rem 1.25rem;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--sidebar-section);
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: var(--sidebar-text);
            padding: .58rem 1.25rem;
            border-left: 3px solid transparent;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .05);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: var(--sidebar-active);
            border-left-color: #93c5fd;
        }
        .sidebar .nav-link.disabled {
            color: #475569;
            cursor: not-allowed;
        }
        .sidebar .nav-link i {
            font-size: 1.05rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: .9rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        /* ================= MAIN ================= */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; }
        }

        .page-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .8rem 1.25rem;
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: .8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: #fff;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: .85rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        .card .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            border-radius: .85rem .85rem 0 0 !important;
        }

        .table th {
            background: #f8fafc;
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .table td { vertical-align: middle; }

        .btn-primary { background: var(--sidebar-active); border-color: var(--sidebar-active); }
        .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }

        /* QR frame gaya Metopel */
        .qr-frame {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
        }

        /* Mobile overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .4);
            z-index: 1040;
            display: none;
        }
        .sidebar-overlay.show { display: block; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .main-content { margin-left: 0; }
            .sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $currentUser = auth()->user();
    $currentRoute = request()->route()?->getName() ?? '';
    if ($currentUser->isAdmin()) {
        $menus = [
            ['section' => 'Utama', 'items' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard', 'active' => str_starts_with($currentRoute, 'admin.dashboard')],
            ]],
            ['section' => 'Data Karyawan', 'items' => [
                ['label' => 'Semua Karyawan', 'icon' => 'bi-people', 'route' => 'admin.employees.index', 'active' => str_starts_with($currentRoute, 'admin.employees')],
                ['label' => 'Tambah Karyawan', 'icon' => 'bi-person-plus', 'route' => 'admin.employees.create', 'active' => $currentRoute === 'admin.employees.create'],
            ]],
            ['section' => 'Absensi', 'items' => [
                ['label' => 'Absensi Hari Ini', 'icon' => 'bi-clipboard-check', 'route' => 'admin.absensi.index', 'active' => str_starts_with($currentRoute, 'admin.absensi')],
            ]],
            ['section' => 'Lokasi Absensi', 'items' => [
                ['label' => 'Daftar Lokasi', 'icon' => 'bi-geo-alt', 'route' => 'admin.locations.index', 'active' => str_starts_with($currentRoute, 'admin.locations')],
                ['label' => 'Tambah Lokasi', 'icon' => 'bi-geo-alt-fill', 'route' => 'admin.locations.create', 'active' => $currentRoute === 'admin.locations.create'],
                ['label' => 'QR Code', 'icon' => 'bi-qr-code-scan', 'route' => 'admin.qrcodes.index', 'active' => str_starts_with($currentRoute, 'admin.qrcodes')],
            ]],
            ['section' => 'Jadwal Kerja', 'items' => [
                ['label' => 'Daftar Jadwal', 'icon' => 'bi-calendar-week', 'route' => 'admin.schedules.index', 'active' => str_starts_with($currentRoute, 'admin.schedules')],
                ['label' => 'Tambah Jadwal', 'icon' => 'bi-calendar-plus', 'route' => 'admin.schedules.create', 'active' => $currentRoute === 'admin.schedules.create'],
                ['label' => 'Penempatan Jadwal', 'icon' => 'bi-person-video3', 'route' => 'admin.assignments.index', 'active' => str_starts_with($currentRoute, 'admin.assignments')],
                ['label' => 'Hari Libur', 'icon' => 'bi-sun', 'route' => 'admin.holidays.index', 'active' => str_starts_with($currentRoute, 'admin.holidays')],
            ]],
            ['section' => 'Laporan', 'items' => [
                ['label' => 'Laporan', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index', 'active' => str_starts_with($currentRoute, 'admin.reports')],
            ]],
        ];
    } else {
        $menus = [
            ['section' => 'Utama', 'items' => [
                ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'employee.dashboard', 'active' => str_starts_with($currentRoute, 'employee.dashboard')],
            ]],
            ['section' => 'Absensi', 'items' => [
                ['label' => 'Mulai Absensi', 'icon' => 'bi-qr-code-scan', 'route' => 'employee.absensi.index', 'active' => str_starts_with($currentRoute, 'employee.absensi')],
                ['label' => 'Riwayat Absensi', 'icon' => 'bi-clock-history', 'route' => 'employee.history.index', 'active' => str_starts_with($currentRoute, 'employee.history')],
            ]],
        ];
    }
@endphp

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar no-print" id="sidebar">
    <a href="{{ route($currentUser->isAdmin() ? 'admin.dashboard' : 'employee.dashboard') }}" class="brand">
        <span class="brand-icon"><i class="bi bi-fingerprint"></i></span>
        <span>Absensi Karyawan</span>
    </a>

    <nav class="pt-1 pb-2 flex-grow-1 overflow-auto">
        @foreach ($menus as $group)
            <div class="menu-section">{{ $group['section'] }}</div>
            @foreach ($group['items'] as $m)
                @if (! empty($m['disabled']))
                    <a class="nav-link disabled" title="Fitur akan tersedia pada tahap berikutnya">
                        <i class="bi {{ $m['icon'] }}"></i><span>{{ $m['label'] }}</span>
                    </a>
                @else
                    <a class="nav-link {{ $m['active'] ? 'active' : '' }}" href="{{ route($m['route']) }}">
                        <i class="bi {{ $m['icon'] }}"></i><span>{{ $m['label'] }}</span>
                    </a>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <a class="nav-link" href="{{ route('profile.show') }}">
            <i class="bi bi-person-circle"></i><span>Profil</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<div class="main-content">
    <header class="page-header py-2 d-flex align-items-center justify-content-between no-print">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <div class="fw-semibold lh-1">@yield('title', 'Dashboard')</div>
                <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-sm-block">
                <div class="fw-semibold lh-1 small">{{ $currentUser->name }}</div>
                <small class="text-muted text-uppercase">{{ $currentUser->role }}</small>
            </div>
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                  style="width:38px;height:38px">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
        </div>
    </header>

    <div class="p-3 p-md-4 flex-grow-1">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="text-center text-muted small pb-4 no-print">
        &copy; {{ date('Y') }} Sistem Absensi Karyawan
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
</script>
@stack('scripts')
</body>
</html>
