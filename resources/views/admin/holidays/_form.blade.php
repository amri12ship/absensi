<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $holiday?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-5">
        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
        <input type="date" name="date" value="{{ old('date', $holiday?->date?->toDateString()) }}" class="form-control @error('date') is-invalid @enderror" required>
        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-7">
        <label class="form-label">Keterangan</label>
        <input type="text" name="description" value="{{ old('description', $holiday?->description) }}" class="form-control @error('description') is-invalid @enderror">
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-5">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ old('status', $holiday?->status ?? 'active') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>