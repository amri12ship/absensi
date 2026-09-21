@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="input-group" style="max-width:300px">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama / NIK / email...">
        </div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Karyawan
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th>No. HP</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center"
                                          style="width:34px;height:34px">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $employee->name }}</div>
                                        <small class="text-muted">ID: #{{ $employee->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->employee?->nik ?? '-' }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->employee?->position ?? '-' }}</td>
                            <td>{{ $employee->employee?->phone ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $employee->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $employee->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.employees.toggle-status', $employee) }}" class="d-inline">
                                    @csrf
                                    @if ($employee->isActive())
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nonaktifkan"
                                                onclick="return confirm('Nonaktifkan akun ini? Karyawan tidak dapat melakukan absensi.')">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    @endif
                                </form>
                                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus karyawan ini?\nJika memiliki riwayat absensi, akun hanya akan dinonaktifkan dan data absensi dipertahankan.')">
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
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $employees->links() }}
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