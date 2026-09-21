@extends('layouts.app')

@section('title', 'Dashboard Karyawan')

@section('content')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                          style="width:80px;height:80px;font-size:2rem">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                    <div class="text-muted small">NIK: {{ $user->employee?->nik }}</div>
                    <div class="text-muted small">{{ $user->employee?->position }}</div>
                    <span class="badge {{ $user->isActive() ? 'bg-success' : 'bg-secondary' }} mt-2">
                        {{ $user->isActive() ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Status Absensi Hari Ini</span>
                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </li>
                    @if ($todayHoliday)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Hari Libur</span>
                            <span class="fw-semibold">{{ $todayHoliday->name }}</span>
                        </li>
                    @elseif ($todaySchedule)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Jadwal Hari Ini</span>
                            <span class="fw-semibold">{{ $todaySchedule->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Jam Masuk/Out</span>
                            <span class="fw-semibold">{{ $todaySchedule->time_in }} - {{ $todaySchedule->time_out }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Toleransi Terlambat</span>
                            <span class="fw-semibold">{{ $todaySchedule->tolerance_minutes }} menit</span>
                        </li>
                    @else
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Jadwal Hari Ini</span>
                            <span class="fw-semibold text-muted">Tidak ada jadwal</span>
                        </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Jam Masuk</span>
                        <span class="fw-semibold">{{ $todayAttendance && $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') : '--:--:--' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Jam Keluar</span>
                        <span class="fw-semibold">{{ $todayAttendance && $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') : '--:--:--' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Lokasi</span>
                        <span class="fw-semibold">{{ $todayAttendance->location?->name ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Jarak</span>
                        <span class="fw-semibold">{{ $todayAttendance?->check_in_distance !== null ? number_format($todayAttendance->check_in_distance).' m' : '-' }}</span>
                    </li>
                </ul>
                <div class="card-body">
                    @if ($todayHoliday)
                        <div class="alert alert-secondary py-1 small mb-0 text-center">
                            <i class="bi bi-sun me-1"></i> Hari ini adalah hari libur nasional ({{ $todayHoliday->name }}).
                        </div>
                    @elseif (! $todayAttendance || ! $todayAttendance->check_in || ! $todayAttendance->check_out)
                        @if ($activeLocation)
                            <a href="{{ route('employee.absensi.index') }}" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-{{ $todayAttendance && $todayAttendance->check_in ? 'box-arrow-right' : 'qr-code-scan' }} me-1"></i>
                                {{ $todayAttendance && $todayAttendance->check_in ? 'Check Out' : 'Mulai Absensi' }}
                            </a>
                            <small class="text-muted d-block text-center mt-2">
                                Lokasi aktif: {{ $activeLocation->name }}
                            </small>
                        @else
                            <button class="btn btn-secondary w-100 py-2" disabled>
                                <i class="bi bi-clock-history me-1"></i> Belum ada lokasi absensi aktif
                            </button>
                        @endif
                    @else
                        <div class="alert alert-success py-1 small mb-0 text-center">
                            <i class="bi bi-check-circle me-1"></i> Absensi hari ini selesai.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Riwayat Absensi Terbaru</span>
                    <a href="{{ route('employee.history.index') }}" class="small">Lihat semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Lokasi</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAttendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('d M Y') }}</td>
                                    <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                                    <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                                    <td>{{ $attendance->location?->name ?? '-' }}</td>
                                    <td><span class="badge {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection