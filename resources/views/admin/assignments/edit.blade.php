@extends('layouts.app')

@section('title', 'Penempatan Jadwal - '.$employee->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div class="text-end">
            <div class="fw-semibold">{{ $employee->name }}</div>
            <small class="text-muted">NIK: {{ $employee->employee?->nik }} · {{ $employee->employee?->position }}</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="max-width:760px">
        <div class="card-body p-4">
            <p class="text-muted mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Pilih jadwal untuk setiap hari. Hari yang tidak memiliki jadwal dianggap hari libur/istirahat karyawan
                tersebut dan tidak dihitung sebagai hari kerja.
            </p>

            <form method="POST" action="{{ route('admin.assignments.update', $employee) }}">
                @csrf
                @method('PUT')
                @if ($errors->has('days'))
                    <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first('days') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jadwal Kerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayNames as $day => $name)
                                <tr>
                                    <td class="fw-semibold">{{ $name }}</td>
                                    <td>
                                        <select name="days[{{ $day }}]" class="form-select">
                                            <option value="">— Tidak bekerja —</option>
                                            @foreach ($schedules as $schedule)
                                                <option value="{{ $schedule->id }}"
                                                    {{ old('days.'.$day, $assignments->get($day)?->work_schedule_id) == $schedule->id ? 'selected' : '' }}>
                                                    {{ $schedule->name }} ({{ $schedule->time_in }} - {{ $schedule->time_out }}, toleransi {{ $schedule->tolerance_minutes }} menit)
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($schedules->isEmpty())
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Belum ada jadwal kerja aktif. <a href="{{ route('admin.schedules.create') }}">Buat jadwal terlebih dahulu</a>.
                    </div>
                @endif

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary" {{ $schedules->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-check-lg me-1"></i> Simpan Penempatan
                    </button>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection