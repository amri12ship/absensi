@extends('layouts.app')

@section('title', 'Tambah Lokasi Absensi')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width:760px">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.locations.store') }}">
                @csrf
                @include('admin.locations._form', ['location' => null, 'statusOptions' => $statusOptions])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection