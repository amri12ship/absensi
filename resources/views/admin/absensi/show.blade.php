@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <span class="badge fs-6 {{ $attendance->statusBadge() }}">{{ $attendance->statusLabel() }}</span>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="small text-muted mb-1">Foto Selfie</div>
                    @if ($attendance->selfie_url)
                        <img src="{{ $attendance->selfie_url }}" alt="Selfie" class="img-fluid rounded-3 border" style="max-height:340px">
                    @else
                        <div class="border rounded-3 py-5 text-muted">
                            <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                            Tidak ada foto selfie
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-2"></i>Data Karyawan</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Nama</label>
                            <div class="fw-semibold">{{ $attendance->employee?->user?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">NIK</label>
                            <div class="fw-semibold">{{ $attendance->employee?->nik ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jabatan</label>
                            <div class="fw-semibold">{{ $attendance->employee?->position ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Email</label>
                            <div>{{ $attendance->employee?->user?->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-clipboard-data me-2"></i>Data Absensi</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Tanggal</label>
                            <div class="fw-semibold">{{ $attendance->date->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Lokasi</label>
                            <div class="fw-semibold">{{ $attendance->location?->name ?? '-' }}</div>
                            <small class="text-muted">{{ $attendance->location?->address }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jam Masuk</label>
                            <div class="fw-semibold fs-5">{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</div>
                            @if ($attendance->check_in_lat)
                                <small class="text-muted">{{ $attendance->check_in_lat }}, {{ $attendance->check_in_lng }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jam Keluar</label>
                            <div class="fw-semibold fs-5">{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</div>
                            @if ($attendance->check_out_lat)
                                <small class="text-muted">{{ $attendance->check_out_lat }}, {{ $attendance->check_out_lng }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jarak Check-in</label>
                            <div class="fw-semibold">{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-0">Jarak Check-out</label>
                            <div class="fw-semibold">{{ $attendance->check_out_distance !== null ? number_format($attendance->check_out_distance).' m' : '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection