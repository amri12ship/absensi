@extends('layouts.app')

@section('title', 'Penempatan Jadwal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">Atur jadwal kerja untuk setiap karyawan berdasarkan hari (Senin–Minggu).</p>
        <span class="badge bg-primary">{{ $employees->total() }} Karyawan</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>NIK</th>
                        <th>Penempatan Jadwal (Senin–Minggu)</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $i => $employeeUser)
                        @php
                            $assignments = $employeeUser->employee?->scheduleAssignments;
                            $days = collect([1, 2, 3, 4, 5, 6, 7])->map(function ($day) use ($assignments) {
                                return $assignments->firstWhere('day_of_week', $day);
                            });
                            $hasSchedule = $assignments->isNotEmpty();
                        @endphp
                        <tr>
                            <td>{{ $employees->firstItem() + $i }}</td>
                            <td class="fw-semibold">{{ $employeeUser->name }}</td>
                            <td>{{ $employeeUser->employee?->nik }}</td>
                            <td>
                                @if ($hasSchedule)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($days as $index => $assignment)
                                            @if ($assignment?->workSchedule)
                                                <span class="badge bg-primary bg-opacity-10 text-primary" title="{{ $index + 1 }}">
                                                    {{ $index + 1 }}. {{ $assignment->workSchedule->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary">{{ $index + 1 }}. -</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Belum ada penempatan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $employeeUser->isActive() ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $employeeUser->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.assignments.edit', $employeeUser) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-calendar-check me-1"></i> Atur Jadwal
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                                Belum ada karyawan.
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