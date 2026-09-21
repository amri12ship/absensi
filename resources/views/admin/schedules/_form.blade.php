@php
    $timeIn = old('time_in', $schedule?->time_in);
    $timeOut = old('time_out', $schedule?->time_out);
    $tolerance = old('tolerance_minutes', $schedule?->tolerance_minutes ?? 0);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nama Jadwal <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $schedule?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ old('status', $schedule?->status ?? 'active') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Jam Masuk <span class="text-danger">*</span></label>
        <input type="time" name="time_in" value="{{ $timeIn }}" class="form-control @error('time_in') is-invalid @enderror" required>
        @error('time_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Jam Keluar <span class="text-danger">*</span></label>
        <input type="time" name="time_out" value="{{ $timeOut }}" class="form-control @error('time_out') is-invalid @enderror" required>
        @error('time_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Toleransi Keterlambatan (menit) <span class="text-danger">*</span></label>
        <input type="number" name="tolerance_minutes" value="{{ $tolerance }}" min="0" max="600" class="form-control @error('tolerance_minutes') is-invalid @enderror" required>
        @error('tolerance_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Check-in melewati batas + toleransi akan berstatus Terlambat.</div>
    </div>
</div>