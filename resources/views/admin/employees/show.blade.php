@extends('layouts.app')

@section('title', 'Detail Karyawan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                          style="width:80px;height:80px;font-size:2rem">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                    <h4 class="mb-0">{{ $employee->name }}</h4>
                    <span class="badge {{ $employee->isActive() ? 'bg-success' : 'bg-secondary' }} mt-2">
                        {{ $employee->isActive() ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <p class="text-muted small mt-2 mb-0">{{ $employee->employee?->position }}</p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">ID Akun</span><span>#{{ $employee->id }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Role</span><span>{{ $employee->role }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Bergabung</span>
                        <span>{{ $employee->created_at->format('d M Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="bi bi-person-vcard me-2"></i>Data Lengkap</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">NIK</label>
                            <div class="fw-semibold">{{ $employee->employee?->nik }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Email</label>
                            <div class="fw-semibold">{{ $employee->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Nomor HP</label>
                            <div class="fw-semibold">{{ $employee->employee?->phone ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jabatan</label>
                            <div class="fw-semibold">{{ $employee->employee?->position }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-0">Alamat</label>
                            <div class="fw-semibold">{{ $employee->employee?->address ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3"><i class="bi bi-clipboard-data me-2"></i>Absensi Hari Ini</h5>
                    @if ($todayAttendance)
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-center">
                                <div class="small text-muted">Masuk</div>
                                <div class="fw-bold fs-5">{{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') : '-' }}</div>
                            </div>
                            <i class="bi bi-arrow-right text-muted"></i>
                            <div class="text-center">
                                <div class="small text-muted">Keluar</div>
                                <div class="fw-bold fs-5">{{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') : '-' }}</div>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-success">{{ $todayAttendance->status }}</span>
                            </div>
                        </div>
                        <div class="small text-muted mt-2">Lokasi: {{ $todayAttendance->location?->name ?? '-' }}</div>
                    @else
                        <p class="text-muted mb-0">Belum ada absensi hari ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection