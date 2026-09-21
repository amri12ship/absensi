@extends('layouts.app')

@section('title', 'Edit Jadwal Kerja')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width:720px">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                @csrf
                @method('PUT')
                @include('admin.schedules._form', ['schedule' => $schedule, 'statusOptions' => $statusOptions])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection