@extends('layouts.app')

@section('title', 'Lokasi Absensi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="input-group" style="max-width:300px">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari lokasi...">
        </div>
        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
            <i class="bi bi-geo-alt me-1"></i> Tambah Lokasi
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Lokasi</th>
                        <th>Koordinat (Lat, Lng)</th>
                        <th>Radius</th>
                        <th>Public Token</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $location->name }}</div>
                                <small class="text-muted">{{ $location->address }}</small>
                            </td>
                            <td class="small">
                                {{ $location->latitude }}, {{ $location->longitude }}
                            </td>
                            <td>{{ $location->radius }} m</td>
                            <td>
                                <code class="small">{{ $location->public_token }}</code>
                            </td>
                            <td>
                                <span class="badge {{ $location->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $location->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.qrcodes.show', $location) }}" class="btn btn-sm btn-outline-dark" title="Lihat QR Code">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                                <a href="{{ route('admin.locations.show', $location) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.locations.toggle-status', $location) }}" class="d-inline">
                                    @csrf
                                    @if ($location->isActive())
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nonaktifkan"
                                                onclick="return confirm('Nonaktifkan lokasi ini?')">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus lokasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-geo-alt fs-3 d-block mb-2"></i>
                                Belum ada lokasi absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $locations->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>
@endpush