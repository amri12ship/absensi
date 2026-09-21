@extends('layouts.app')

@section('title', 'Data Absensi')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari Nama / NIK</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau NIK...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $date }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select name="bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (string) $month === (string) $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach (range(now()->year, now()->year - 2) as $y)
                            <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Lokasi</label>
                    <select name="lokasi" class="form-select">
                        <option value="all">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" {{ (string) $locationId === (string) $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="all">Semua Status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Karyawan</label>
                    <select name="employee" id="employeeFilter" class="form-select">
                        <option value="">Semua Karyawan</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->employee?->id }}" {{ (string) $request->query('employee') === (string) $employee->employee?->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->employee?->nik }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
                    <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Lokasi</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Jarak</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $attendance->employee?->user?->name ?? '-' }}</div>
                                <small class="text-muted">NIK: {{ $attendance->employee?->nik }}</small>
                            </td>
                            <td>{{ $attendance->location?->name ?? '-' }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</td>
                            <td>
                                <span class="badge {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.absensi.show', $attendance) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-clipboard-data fs-3 d-block mb-2"></i>
                                Tidak ada data absensi pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">Total: {{ $attendances->total() }} data</small>
            {{ $attendances->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('employeeFilter').addEventListener('change', function () {
            this.form.submit();
        });
    </script>
@endpush