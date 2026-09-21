@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="card border-0 shadow-sm" style="max-width:760px">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2"
                      style="width:72px;height:72px;font-size:1.8rem">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                <span class="badge {{ $user->isActive() ? 'bg-success' : 'bg-secondary' }}">
                    {{ $user->isActive() ? 'Aktif' : 'Nonaktif' }}
                </span>
                <div class="text-muted small">{{ $user->role }}</div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" value="{{ $user->email }}" class="form-control" disabled>
                        <div class="form-text">Email tidak dapat diubah di sini.</div>
                    </div>
                    @if ($user->employee)
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" value="{{ $user->employee->nik }}" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="position" value="{{ old('position', $user->employee->position) }}" class="form-control @error('position') is-invalid @enderror">
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->employee->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Bergabung</label>
                            <input type="text" value="{{ $user->created_at->format('d M Y') }}" class="form-control" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->employee->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak diganti" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection