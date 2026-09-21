@extends('layouts.app')

@section('title', 'Jadwal Kerja')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">Kelola jadwal kerja, jam masuk/keluar, dan toleransi keterlambatan.</p>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jadwal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Toleransi</th>
                        <th>Penempatan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $i => $schedule)
                        <tr>
                            <td>{{ $schedules->firstItem() + $i }}</td>
                            <td class="fw-semibold">{{ $schedule->name }}</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $schedule->time_in }}</span></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $schedule->time_out }}</span></td>
                            <td>{{ $schedule->tolerance_minutes }} menit</td>
                            <td>{{ $schedule->assignments()->count() }} karyawan</td>
                            <td>
                                <span class="badge {{ $schedule->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $schedule->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.schedules.toggle-status', $schedule) }}" class="d-inline">
                                    @csrf
                                    @if ($schedule->isActive())
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nonaktifkan"
                                                onclick="return confirm('Nonaktifkan jadwal ini?')">
                                            <i class="bi bi-pause-circle"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-play-circle"></i>
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus jadwal ini?')">
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
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-week fs-3 d-block mb-2"></i>
                                Belum ada jadwal kerja. Tambahkan jadwal terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $schedules->links() }}
        </div>
    </div>
@endsection