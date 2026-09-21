@extends('layouts.app')

@section('title', 'Hari Libur')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            Pada hari libur aktif, karyawan tidak diwajibkan absensi dan tidak akan dianggap Alpha.
        </p>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Hari Libur
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Hari Libur</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $i => $holiday)
                        <tr>
                            <td>{{ $holidays->firstItem() + $i }}</td>
                            <td class="fw-semibold">{{ $holiday->name }}</td>
                            <td>{{ $holiday->date->format('d M Y') }}</td>
                            <td class="text-muted small">{{ $holiday->description ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $holiday->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $holiday->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.holidays.toggle-status', $holiday) }}" class="d-inline">
                                    @csrf
                                    @if ($holiday->isActive())
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nonaktifkan"
                                                onclick="return confirm('Nonaktifkan hari libur ini?')">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus hari libur ini?')">
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
                                <i class="bi bi-sun fs-3 d-block mb-2"></i>
                                Belum ada hari libur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $holidays->links() }}
        </div>
    </div>
@endsection