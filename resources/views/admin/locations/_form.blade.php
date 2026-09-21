@php
    $latitude = old('latitude', $location?->latitude);
    $longitude = old('longitude', $location?->longitude);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $location?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
        <input type="number" name="radius" value="{{ old('radius', $location?->radius) }}" min="1" class="form-control @error('radius') is-invalid @enderror" required>
        @error('radius')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Alamat</label>
        <input type="text" name="address" value="{{ old('address', $location?->address) }}" class="form-control @error('address') is-invalid @enderror">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="border rounded-3 p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold"><i class="bi bi-satellite me-1"></i> Koordinat GPS</div>
                <button type="button" class="btn btn-sm btn-primary" onclick="getUserLocation()">
                    <i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saya
                </button>
            </div>
            <div id="gpsMessage" class="mb-2"></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Latitude <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">Lat</span>
                        <input type="text" id="latitude" name="latitude" value="{{ $latitude }}" step="any"
                               class="form-control @error('latitude') is-invalid @enderror" required placeholder="-7.7955798">
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Kisaran: -90 sampai 90</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Longitude <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">Lng</span>
                        <input type="text" id="longitude" name="longitude" value="{{ $longitude }}" step="any"
                               class="form-control @error('longitude') is-invalid @enderror" required placeholder="110.3694896">
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Kisaran: -180 sampai 180</div>
                </div>
            </div>
            <small class="text-muted d-block mt-2">
                <i class="bi bi-info-circle me-1"></i>
                Tekan tombol di atas agar koordinat terisi otomatis dari GPS perangkat. Anda tetap dapat mengubahnya secara manual.
            </small>
        </div>
    </div>

    @if ($location)
        <div class="col-12">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $location->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif
</div>

@push('scripts')
    <script>
        function showGpsMessage(message, type) {
            const el = document.getElementById('gpsMessage');
            el.innerHTML = '<div class="alert alert-' + type + ' py-2 small mb-0"><i class="bi ' +
                (type === 'success' ? 'bi-check-circle' : type === 'danger' ? 'bi-x-circle' : 'bi-exclamation-triangle') +
                ' me-1"></i>' + message + '</div>';
        }

        function getUserLocation() {
            if (!navigator.geolocation) {
                showGpsMessage('Perangkat atau browser Anda tidak mendukung Geolocation.', 'danger');
                return;
            }
            showGpsMessage('Mengambil koordinat GPS...', 'info');
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(7);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(7);
                    showGpsMessage('Koordinat berhasil diambil dari perangkat Anda.', 'success');
                },
                function (error) {
                    let message = 'Gagal mendapatkan lokasi.';
                    if (error.code === error.PERMISSION_DENIED) {
                        message = 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser dan coba lagi.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        message = 'Posisi GPS tidak tersedia di perangkat ini.';
                    } else if (error.code === error.TIMEOUT) {
                        message = 'Waktu pengambilan lokasi habis. Silakan coba lagi.';
                    }
                    showGpsMessage(message, 'danger');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }
    </script>
@endpush