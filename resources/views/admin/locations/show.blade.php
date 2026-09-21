@extends('layouts.app')

@section('title', 'Detail Lokasi Absensi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.qrcodes.show', $location) }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-qr-code me-1"></i> QR Code
            </a>
            <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $location->name }}</h4>
                            <p class="text-muted mb-0">{{ $location->address ?? '-' }}</p>
                        </div>
                        <span class="badge {{ $location->isActive() ? 'bg-success' : 'bg-secondary' }}">
                            {{ $location->isActive() ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="small text-muted">Latitude</div>
                            <div class="fw-semibold">{{ $location->latitude }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">Longitude</div>
                            <div class="fw-semibold">{{ $location->longitude }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">Radius</div>
                            <div class="fw-semibold">{{ $location->radius }} m</div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <div class="small text-muted mb-1">URL Absensi (terkode di QR)</div>
                        <code>{{ $location->scan_url }}</code>
                    </div>
                    <div class="mt-3">
                        <div class="small text-muted mb-1">Public Token</div>
                        <code>{{ $location->public_token }}</code>
                        <form method="POST" action="{{ route('admin.locations.regenerate-token', $location) }}" class="d-inline ms-2"
                              onsubmit="return confirm('Generate ulang token? QR Code lama tidak akan berlaku lagi.')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-arrow-repeat me-1"></i> Generate Ulang Token
                            </button>
                        </form>
                    </div>
                    <div class="small text-muted mt-3">
                        Dibuat: {{ $location->created_at->format('d M Y H:i') }} &middot; Diubah: {{ $location->updated_at->format('d M Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="mapCard">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-map me-1"></i> Peta Lokasi</h6>
                    <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                       target="_blank" class="btn btn-outline-primary w-100">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Google Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection