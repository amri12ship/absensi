@extends('layouts.app')

@section('title', 'QR Code - '.$location->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.qrcodes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('admin.qrcodes.print', $location) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak QR
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width:640px">
        <div class="card-body text-center p-4 p-md-5">
            <div class="qr-frame mb-3">
                <img src="{{ $qr }}" alt="QR {{ $location->name }}" class="img-fluid" style="width:min(400px,80vw);height:auto">
            </div>
            <h4 class="fw-bold mb-1">{{ $location->name }}</h4>
            <p class="text-muted small">{{ $location->address ?? '-' }}</p>
            <div class="alert alert-light border small text-start mx-auto" style="max-width:480px">
                <div class="text-muted">Isi QR Code (URL absensi):</div>
                <code class="d-block text-break">{{ $location->scan_url }}</code>
            </div>
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Tidak ada data sensitif (password/credential) yang dimasukkan ke dalam QR Code.
            </p>
        </div>
    </div>
@endsection