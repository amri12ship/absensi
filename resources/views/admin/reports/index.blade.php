@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Periode Dari</label>
                    <input type="date" name="dari" value="{{ $request->query('dari', '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Sampai</label>
                    <input type="date" name="sampai" value="{{ $request->query('sampai', '') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Bulan</label>
                    <select name="bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (string) $request->query('bulan') === (string) $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach (range(now()->year, now()->year - 3) as $y)
                            <option value="{{ $y }}" {{ (string) $request->query('tahun') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Karyawan</label>
                    <select name="employee" class="form-select">
                        <option value="all">Semua Karyawan</option>
                        @foreach ($employees as $employeeUser)
                            <option value="{{ $employeeUser->employee?->id }}" {{ (string) $request->query('employee') === (string) $employeeUser->employee?->id ? 'selected' : '' }}>
                                {{ $employeeUser->name }} ({{ $employeeUser->employee?->nik }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Lokasi</label>
                    <select name="lokasi" class="form-select">
                        <option value="all">Semua Lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" {{ (string) $request->query('lokasi') === (string) $location->id ? 'selected' : '' }}>
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
                            <option value="{{ $value }}" {{ $request->query('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
            <div class="small text-muted mt-2 border-top pt-2">
                <i class="bi bi-calendar-range me-1"></i>
                Periode aktif: <strong>{{ $start->format('d M Y') }}</strong> s/d <strong>{{ $end->format('d M Y') }}</strong>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Rekap Absensi</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.print', $request->query()) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            <form method="GET" action="{{ route('admin.reports.export') }}">
                @foreach ($request->query() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Jabatan</th>
                        <th class="text-center">Hari Kerja</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Alpha</th>
                        <th class="text-center">Libur</th>
                        <th class="text-center">Tidak Hadir</th>
                        <th class="text-end">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['employee']->user->name }}</td>
                            <td>{{ $row['employee']->nik }}</td>
                            <td>{{ $row['employee']->position }}</td>
                            <td class="text-center">{{ $row['expected'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $row['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $row['terlambat'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $row['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $row['sakit'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark">{{ $row['alpha'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $row['libur'] }}</span>
                            </td>
                            <td class="text-center">{{ $row['tidak_hadir'] }}</td>
                            <td class="text-end">
                                @if ($row['percentage'] !== null)
                                    <span class="fw-semibold">{{ $row['percentage'] }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                Tidak ada karyawan untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-check me-2"></i>Detail Laporan</span>
            <small class="text-muted">{{ $attendances->total() }} data</small>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Jabatan</th>
                        <th>Lokasi</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Jarak</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->format('d M Y') }}</td>
                            <td class="fw-semibold">{{ $attendance->employee?->user?->name ?? '-' }}</td>
                            <td>{{ $attendance->employee?->nik ?? '-' }}</td>
                            <td>{{ $attendance->employee?->position ?? '-' }}</td>
                            <td>{{ $attendance->location?->name ?? '-' }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                            <td>{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</td>
                            <td><span class="badge {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data absensi pada periode yang dipilih.
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