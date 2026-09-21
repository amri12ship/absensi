@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="row g-3">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $totalEmployees }}</div>
                        <div class="small text-muted">Total Karyawan</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-person-check fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $activeEmployees }}</div>
                        <div class="small text-muted">Karyawan Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-person-x fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $inactiveEmployees }}</div>
                        <div class="small text-muted">Karyawan Nonaktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-geo-alt fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $totalLocations }}</div>
                        <div class="small text-muted">Total Lokasi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-geo fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $activeLocations }}</div>
                        <div class="small text-muted">Lokasi Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-clipboard-check fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $todayAttendances }}</div>
                        <div class="small text-muted">Absensi Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $presentToday }}</div>
                        <div class="small text-muted">Hadir Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $lateToday }}</div>
                        <div class="small text-muted">Terlambat Hari Ini</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-hourglass-split fs-4"></i></span>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $notYetPresent }}</div>
                        <div class="small text-muted">Belum Absen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white">
            <span class="fw-semibold"><i class="bi bi-bar-chart me-2"></i>Statistik Status Kehadiran Hari Ini</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php
                    $cards = [
                        ['Hadir', $statusToday[\App\Models\Attendance::STATUS_PRESENT], 'bg-success'],
                        ['Terlambat', $statusToday[\App\Models\Attendance::STATUS_LATE], 'bg-warning text-dark'],
                        ['Izin', $statusToday[\App\Models\Attendance::STATUS_IZIN], 'bg-info text-dark'],
                        ['Sakit', $statusToday[\App\Models\Attendance::STATUS_SICK], 'bg-danger'],
                        ['Alpha', $statusToday[\App\Models\Attendance::STATUS_ALPHA], 'bg-dark'],
                        ['Libur', $statusToday[\App\Models\Attendance::STATUS_LIBUR], 'bg-secondary'],
                        ['Belum Absen', $notYetPresent, 'bg-light text-secondary border'],
                    ];
                @endphp
                @foreach ($cards as $card)
                    <div class="col-6 col-md-3 col-xl text-center">
                        <div class="p-3 rounded-3" style="background:#f8fafc">
                            <div class="h3 mb-0 fw-bold">{{ $card[1] }}</div>
                            <span class="badge {{ $card[2] }}">{{ $card[0] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-calendar-check me-2"></i>Absensi Hari Ini</span>
            <a href="{{ route('admin.absensi.index', ['tanggal' => now()->toDateString()]) }}" class="small">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>NIK</th>
                        <th>Lokasi</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th class="text-end">Jarak</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($todayAttendanceRows as $i => $attendance)
                        <tr>
                            <td>{{ $todayAttendanceRows->firstItem() + $i }}</td>
                            <td class="fw-semibold">{{ $attendance->employee?->user?->name ?? '-' }}</td>
                            <td>{{ $attendance->employee?->nik }}</td>
                            <td>{{ $attendance->location?->name ?? '-' }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                            <td>
                                <span class="badge {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span>
                            </td>
                            <td class="text-end">{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada absensi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($todayAttendanceRows->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $todayAttendanceRows->links() }}
            </div>
        @endif
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Karyawan Terbaru</span>
                    <a href="{{ route('admin.employees.index') }}" class="small">Lihat semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Nama</th><th>NIK</th><th>Jabatan</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($latestEmployees as $employee)
                                <tr>
                                    <td class="fw-semibold">{{ $employee->name }}</td>
                                    <td>{{ $employee->employee?->nik ?? '-' }}</td>
                                    <td>{{ $employee->employee?->position ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $employee->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $employee->isActive() ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada karyawan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Lokasi Absensi Terbaru</span>
                    <a href="{{ route('admin.locations.index') }}" class="small">Lihat semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Lokasi</th><th>Koordinat</th><th>Radius</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($latestLocations as $location)
                                <tr>
                                    <td class="fw-semibold">{{ $location->name }}</td>
                                    <td class="small">{{ $location->latitude }}, {{ $location->longitude }}</td>
                                    <td>{{ $location->radius }} m</td>
                                    <td>
                                        <span class="badge {{ $location->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $location->isActive() ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada lokasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection