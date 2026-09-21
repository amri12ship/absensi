@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('employee.history.index') }}" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label small text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" value="{{ $start }}" class="form-control">
                </div>
                <div class="col-sm-4">
                    <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ $end }}" class="form-control">
                </div>
                <div class="col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
                    <a href="{{ route('employee.history.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h4 fw-bold mb-0">{{ $summary['total'] }}</div>
                    <div class="small text-muted">Total Absensi</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h4 fw-bold mb-0 text-success">{{ $summary['hadir'] }}</div>
                    <div class="small text-muted">Hadir</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h4 fw-bold mb-0 text-warning">{{ $summary['terlambat'] }}</div>
                    <div class="small text-muted">Terlambat</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Riwayat Absensi</span>
            <a href="{{ route('employee.history.calendar') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-month me-1"></i> Kalender Absensi
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Tanggal</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Lokasi</th><th>Jarak</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->location?->name ?? '-' }}</td>
                            <td>{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</td>
                            <td><span class="badge {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data pada rentang tanggal tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $attendances->links() }}
        </div>
    </div>
@endsection