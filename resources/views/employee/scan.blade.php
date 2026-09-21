@extends('layouts.app')

@section('title', 'Absensi - '.$location->name)

@section('content')
    <div class="text-center" style="max-width:520px;margin:0 auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <span class="badge bg-success mb-3"><i class="bi bi-geo-alt me-1"></i> Lokasi Absensi</span>
                <h4 class="fw-bold">{{ $location->name }}</h4>
                @if ($location->address)
                    <p class="text-muted mb-1">{{ $location->address }}</p>
                @endif
                <p class="text-muted small mb-4">
                    Radius {{ $location->radius }} meter dari titik koordinat
                </p>

                <div class="alert alert-warning py-3 text-start">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Fitur check-in / check-out</strong> akan tersedia pada tahap pengembangan berikutnya.
                    Saat ini Anda telah berada di halaman yang tertaut pada QR Code lokasi
                    <code>{{ $location->public_token }}</code>.
                </div>

                <div class="alert alert-secondary py-2 small text-start mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Kamera browser dan GPS akan digunakan pada tahap berikutnya untuk
                    memindai QR kembali, memverifikasi koordinat, dan melakukan absensi.
                </div>
            </div>
        </div>
    </div>
@endsection