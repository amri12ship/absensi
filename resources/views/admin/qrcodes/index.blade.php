@extends('layouts.app')

@section('title', 'QR Code Lokasi')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                QR Code berisi URL absensi menggunakan public token lokasi. Karyawan dapat memindainya menggunakan kamera browser.
            </p>
            <div class="row g-3">
                @forelse ($qrcodes as $item)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="qr-frame mb-2">
                                    <img src="{{ $item['qr'] }}" alt="QR {{ $item['location']->name }}" class="img-fluid" style="width:130px;height:130px">
                                </div>
                                <div class="fw-semibold small text-truncate">{{ $item['location']->name }}</div>
                                <span class="badge {{ $item['location']->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $item['location']->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <div class="mt-2 d-grid gap-2">
                                    <a href="{{ route('admin.qrcodes.show', $item['location']) }}" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-zoom-in me-1"></i> Lihat Besar
                                    </a>
                                    <a href="{{ route('admin.qrcodes.print', $item['location']) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer me-1"></i> Cetak
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-qr-code fs-3 d-block mb-2"></i>
                        Belum ada lokasi. Tambahkan lokasi terlebih dahulu.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection