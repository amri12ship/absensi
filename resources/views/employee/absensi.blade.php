@extends('layouts.app')

@section('title', 'Mulai Absensi')

@section('content')
    <div style="max-width:640px;margin:0 auto">
        @php
            $stepNames = ['Scan QR Lokasi', 'Verifikasi Lokasi', 'Ambil Selfie'];
        @endphp

        @if ($mode === 'complete')
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-md-5">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:2.2rem">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h3 class="fw-bold">Absensi Hari Ini Selesai</h3>
                    <p class="text-muted">Anda sudah check-in dan check-out hari ini.</p>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="small text-muted">Jam Masuk</div>
                                <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="small text-muted">Jam Keluar</div>
                                <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-3 mb-0">{{ $todayAttendance->location?->name ?? '-' }}</p>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge {{ $mode === 'checkin' ? 'bg-success' : 'bg-warning text-dark' }} mb-2">
                                {{ $mode === 'checkin' ? 'Check-in' : 'Check-out' }}
                            </span>
                            <h4 class="fw-bold mb-0">{{ $mode === 'checkin' ? 'Mulai Absensi' : 'Selesaikan Absensi' }}</h4>
                            <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
                        </div>
                        @if ($mode === 'checkout' && $todayAttendance)
                            <div class="text-end">
                                <div class="small text-muted">Sudah masuk</div>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') }}</div>
                            </div>
                        @endif
                    </div>

                    <ol class="progress-steps list-unstyled d-flex justify-content-between mb-4" id="progressSteps">
                        @foreach ($stepNames as $i => $name)
                            <li class="step-item {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}" style="flex:1">
                                <span class="step-badge">{{ $i + 1 }}</span>
                                <span class="step-name d-none d-sm-inline">{{ $name }}</span>
                            </li>
                        @endforeach
                    </ol>

                    <div id="flowError"></div>

                    {{-- STEP 1: SCAN QR --}}
                    <div id="step-scan" class="flow-step">
                        @if ($activeLocation->isNotEmpty())
                            <p class="text-muted mb-3">
                                Arahkan kamera ke QR Code yang tersedia di lokasi absensi untuk memulai.
                            </p>
                            <div class="camera-wrap mx-auto mb-3">
                                <video id="qrVideo" playsinline muted></video>
                                <canvas id="qrOverlay" class="scan-overlay"></canvas>
                            </div>
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" id="startCameraBtn" class="btn btn-primary" onclick="startCamera('qr')">
                                    <i class="bi bi-camera-video me-1"></i> Mulai Kamera
                                </button>
                            </div>
                            <div id="scanStatus" class="text-center text-muted small"></div>

                            <hr>
                            <details class="small">
                                <summary class="cursor-pointer text-muted">Tidak punya kamera? Masukkan token manual</summary>
                                <div class="input-group mt-2">
                                    <input type="text" id="manualToken" class="form-control" placeholder="Tempel token lokasi di sini">
                                    <button type="button" class="btn btn-outline-primary" onclick="verifyManualToken()">Verifikasi</button>
                                </div>
                            </details>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Belum ada lokasi absensi yang aktif. Hubungi administrator.
                            </div>
                        @endif
                    </div>

                    {{-- STEP 2: VERIFIKASI LOKASI --}}
                    <div id="step-location" class="flow-step d-none">
                        <div class="alert alert-info">
                            <i class="bi bi-qr-code me-1"></i>
                            QR <strong id="locName"></strong> terverifikasi.
                            <div class="small text-muted mt-1" id="locAddress"></div>
                        </div>
                        <p class="text-muted small mb-2">
                            Tekan tombol di bawah agar sistem mengambil posisi GPS Anda. Jarak Anda dari titik lokasi akan dihitung (radius {{-- diisi JS --}} <span id="locRadius"></span> m).
                        </p>
                        <div class="d-grid gap-2">
                            <button type="button" id="getGpsBtn" class="btn btn-primary" onclick="getGPS()">
                                <i class="bi bi-crosshair me-1"></i> Ambil Lokasi GPS Saya
                            </button>
                        </div>
                        <div id="gpsStatus" class="mt-3"></div>
                        <div class="d-grid mt-3">
                            <button type="button" id="goSelfieBtn" class="btn btn-success" disabled onclick="goToSelfie()">
                                <i class="bi bi-arrow-right me-1"></i> Lanjut ke Selfie
                            </button>
                        </div>
                    </div>

                    {{-- STEP 3: SELFIE --}}
                    <div id="step-selfie" class="flow-step d-none">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div class="fw-semibold">Ambil Foto Selfie</div>
                                <small class="text-muted">
                                    {{ $mode === 'checkout' ? 'Opsional untuk check-out.' : 'Wajib untuk check-in.' }}
                                </small>
                            </div>
<button type="button" class="btn btn-sm btn-outline-primary" id="selfieCameraBtn" onclick="startCamera('selfie')">
                                    <i class="bi bi-camera-video me-1"></i> Nyalakan Kamera Depan
                                </button>
                        </div>
                        <div class="camera-wrap mx-auto mb-3" id="selfieCameraWrap">
                            <video id="selfieVideo" playsinline muted></video>
                        </div>
                        <div class="text-center mb-3 d-none" id="selfiePreviewWrap">
                            <img id="selfiePreview" class="img-fluid rounded-3 border" style="max-height:320px">
                            <div class="small text-muted mt-1" id="selfieReadyLabel">Foto siap. Anda dapat mengulang jika kurang baik.</div>
                        </div>
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-primary w-100" id="captureBtn" onclick="takeSelfie()" disabled>
                                    <i class="bi bi-camera me-1"></i> Ambil Foto
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-secondary w-100 d-none" id="retakeBtn" onclick="retakeSelfie()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ulangi
                                </button>
                            </div>
                        </div>
                        @if ($mode === 'checkout')
                            <div class="d-grid mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="skipSelfieBtn" onclick="skipSelfie()">
                                    Lewati tanpa selfie
                                </button>
                            </div>
                        @endif
                        <div class="d-grid mt-3">
                            <button type="button" id="submitBtn" class="btn btn-lg btn-{{ $mode === 'checkin' ? 'success' : 'warning text-dark' }}" onclick="submitAttendance()" disabled>
                                <i class="bi bi-{{ $mode === 'checkin' ? 'box-arrow-in-right' : 'box-arrow-right' }} me-1"></i>
                                Konfirmasi {{ $mode === 'checkin' ? 'Check-in' : 'Check-out' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DONE CARD --}}
            <div id="doneCard" class="card border-0 shadow-sm d-none">
                <div class="card-body text-center p-4 p-md-5">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:2.2rem">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h3 class="fw-bold" id="doneTitle">Berhasil!</h3>
                    <p class="text-muted" id="doneSub"></p>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="small text-muted" id="doneLabel1">Jam Masuk</div>
                                <div class="fw-bold fs-5" id="doneValue1">--:--:--</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="small text-muted" id="doneLabel2">Lokasi</div>
                                <div class="fw-bold fs-6" id="doneValue2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                        <button type="button" class="btn btn-primary" onclick="resetFlow()"><i class="bi bi-arrow-repeat me-1"></i> Absensi Baru</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        const MODE = @json($mode);
        const PRELOADED_TOKEN = new URLSearchParams(window.location.search).get('token');
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        const state = {
            location: null,
            coords: null,
            distance: null,
            withinRadius: false,
            selfie: null,
            stream: null,
            scanning: false,
            submitActive: false,
        };

        const el = (id) => document.getElementById(id);
        const flowVideo = el('qrVideo');
        const selfieVideo = el('selfieVideo');

        async function fetchJson(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(payload),
            });
            return { status: res.status, data: await res.json().catch(() => ({})) };
        }

        function showFlowError(message) {
            el('flowError').innerHTML =
                '<div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>' + message + '</div>';
        }

        function clearFlowError() {
            el('flowError').innerHTML = '';
        }

        function setActiveStep(no) {
            document.querySelectorAll('.flow-step').forEach((s) => s.classList.add('d-none'));
            el('step-' + ['scan', 'location', 'selfie'][no - 1]).classList.remove('d-none');
            document.querySelectorAll('.progress-steps .step-item').forEach((li, i) => {
                li.classList.toggle('active', i <= no - 1);
                li.classList.toggle('done', i < no - 1);
            });
            el('step-' + ['scan', 'location', 'selfie'][no - 1]).scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        async function startCamera(mode) {
            clearFlowError();
            if (state.stream) {
                if (mode === 'qr') {
                    document.getElementById('startCameraBtn').innerHTML = '<i class="bi bi-camera-video me-1"></i> Mulai Kamera';
                }
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (mode === 'qr') {
                    el('scanStatus').innerHTML = '<span class="text-danger">Browser tidak mendukung kamera. Gunakan token manual di bawah ini.</span>';
                } else {
                    showFlowError('Browser tidak mendukung kamera untuk selfie.');
                }
                return;
            }
            try {
                const facing = mode === 'selfie' ? 'user' : 'environment';
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: facing }, audio: false });
                state.stream = stream;
                if (mode === 'qr') {
                    flowVideo.srcObject = stream;
                    await flowVideo.play();
                    el('startCameraBtn').innerHTML = '<i class="bi bi-camera-video-off me-1"></i> Kamera Aktif';
                    el('scanStatus').innerHTML = '<span class="text-success"><i class="bi bi-camera-video me-1"></i> arahkan ke QR Code...</span>';
                    startScanning();
                } else {
                    selfieVideo.srcObject = stream;
                    await selfieVideo.play();
                    el('selfieCameraBtn').innerHTML = '<i class="bi bi-camera-video-off me-1"></i> Kamera Depan Aktif';
                    el('captureBtn').disabled = false;
                }
            } catch (e) {
                let message = 'Gagal mengakses kamera.';
                if (e.name === 'NotAllowedError') {
                    message = 'Izin kamera ditolak. Aktifkan izin kamera pada browser dan coba lagi.';
                } else if (e.name === 'NotFoundError') {
                    message = 'Kamera tidak ditemukan pada perangkat ini.';
                }
                showFlowError(message);
            }
        }

        function startScanning() {
            if (state.scanning || !state.stream) return;
            state.scanning = true;
            const overlay = el('qrOverlay');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });

            overlay.width = 320;
            overlay.height = 320;

            function tick() {
                if (!state.scanning || !state.stream || flowVideo.readyState < 2) {
                    requestAnimationFrame(tick);
                    return;
                }
                const w = Math.min(flowVideo.videoWidth, 640);
                const h = Math.min(flowVideo.videoHeight, 640);
                const scale = 320 / w;
                canvas.width = w;
                canvas.height = h;
                ctx.drawImage(flowVideo, 0, 0, w, h);
                const imageData = ctx.getImageData(0, 0, w, h);

                if (window.jsQR) {
                    const code = jsQR(imageData.data, w, h);
                    if (code && code.data) {
                        state.scanning = false;
                        handleScannedValue(code.data);
                        return;
                    }
                }
                requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }

        async function handleScannedValue(value) {
            state.scanning = false;
            el('scanStatus').innerHTML = '<span class="text-primary">QR terdeteksi. Memverifikasi lokasi...</span>';
            await performVerify(value);
        }

        async function verifyManualToken() {
            clearFlowError();
            const value = el('manualToken').value.trim();
            if (!value) {
                showFlowError('Masukkan token lokasi terlebih dahulu.');
                return;
            }
            el('scanStatus').innerHTML = '<span class="text-primary">Memverifikasi token...</span>';
            await performVerify(value);
        }

        async function performVerify(value) {
            clearFlowError();
            const res = await fetchJson('{{ route("employee.absensi.verify") }}', { scan_value: value });

            if (!res.data.ok) {
                showFlowError(res.data.message || 'Lokasi tidak dikenali.');
                if (!PRELOADED_TOKEN && state.stream) {
                    setTimeout(() => { state.scanning = false; startScanning(); }, 1500);
                }
                return;
            }

            if (res.data.mode === 'complete') {
                showFlowError('Absensi hari ini sudah selesai.');
                return;
            }

            state.location = res.data.location;
            el('locName').textContent = state.location.name;
            el('locAddress').textContent = state.location.address || '';
            el('locRadius').textContent = state.location.radius;
            document.getElementById('startCameraBtn').innerHTML = '<i class="bi bi-camera-video me-1"></i> Kamera Aktif';
            setActiveStep(2);
            el('goSelfieBtn').disabled = true;
        }

        function haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const toRad = (d) => (d * Math.PI) / 180;
            const dLat = toRad(lat2 - lat1);
            const dLng = toRad(lng2 - lng1);
            const a = Math.sin(dLat / 2) ** 2 +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function getGPS() {
            clearFlowError();
            el('gpsStatus').innerHTML = '<div class="alert alert-info py-2 small"><i class="bi bi-hourglass-split me-1"></i> Mengambil koordinat GPS...</div>';
            if (!navigator.geolocation) {
                el('gpsStatus').innerHTML = '<div class="alert alert-danger py-2 small"><i class="bi bi-x-circle me-1"></i> Perangkat/browser tidak mendukung Geolocation.</div>';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    state.coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    state.distance = Math.round(haversine(state.coords.lat, state.coords.lng, state.location.latitude, state.location.longitude));
                    state.withinRadius = state.distance <= state.location.radius;

                    const badge = state.withinRadius
                        ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Dalam radius</span>'
                        : '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Di luar radius</span>';

                    el('gpsStatus').innerHTML =
                        '<div class="alert ' + (state.withinRadius ? 'alert-success' : 'alert-danger') + ' py-3">' +
                        '<div class="row text-center g-2 mb-2">' +
                        '<div class="col-4"><div class="small text-muted">Latitude</div><div class="fw-semibold small">' + state.coords.lat.toFixed(7) + '</div></div>' +
                        '<div class="col-4"><div class="small text-muted">Longitude</div><div class="fw-semibold small">' + state.coords.lng.toFixed(7) + '</div></div>' +
                        '<div class="col-4"><div class="small text-muted">Jarak</div><div class="fw-semibold small">' + state.distance + ' m</div></div>' +
                        '</div>' + badge +
                        '<div class="small text-muted mt-2">Titik lokasi (' + state.location.latitude.toFixed(7) + ', ' + state.location.longitude.toFixed(7) + ') &middot; radius ' + state.location.radius + ' m</div>' +
                        '</div>';
                    el('goSelfieBtn').disabled = !state.withinRadius;
                },
                (err) => {
                    let message = 'Gagal mendapatkan lokasi.';
                    if (err.code === err.PERMISSION_DENIED) message = 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser.';
                    else if (err.code === err.POSITION_UNAVAILABLE) message = 'Posisi GPS tidak tersedia.';
                    else if (err.code === err.TIMEOUT) message = 'Waktu pengambilan lokasi habis. Coba lagi.';
                    el('gpsStatus').innerHTML = '<div class="alert alert-danger py-2 small"><i class="bi bi-x-circle me-1"></i> ' + message + '</div>';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        function goToSelfie() {
            setActiveStep(3);
            document.getElementById('selfieCameraWrap').classList.remove('d-none');
            if (state.stream) {
                stopCamera();
            }
            startCamera('selfie');
        }

        async function takeSelfie() {
            clearFlowError();
            if (!state.stream) return;
            const host = el('selfieVideo') || flowVideo;
            if (host.readyState < 2) return;
            const canvas = document.createElement('canvas');
            canvas.width = host.videoWidth;
            canvas.height = host.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(host, 0, 0, canvas.width, canvas.height);
            state.selfie = canvas.toDataURL('image/png');
            el('selfiePreview').src = state.selfie;
            el('selfieCameraWrap').classList.add('d-none');
            el('selfiePreviewWrap').classList.remove('d-none');
            el('retakeBtn').classList.remove('d-none');
            el('captureBtn').disabled = true;
            enableSubmitIfReady();
        }

        function retakeSelfie() {
            state.selfie = null;
            el('selfieCameraWrap').classList.remove('d-none');
            el('selfiePreviewWrap').classList.add('d-none');
            el('retakeBtn').classList.add('d-none');
            el('captureBtn').disabled = !state.stream;
            el('submitBtn').disabled = true;
        }

        function skipSelfie() {
            el('selfiePreviewWrap').classList.add('d-none');
            el('selfieCameraWrap').classList.add('d-none');
            el('retakeBtn').classList.add('d-none');
            enableSubmitIfReady();
        }

        function enableSubmitIfReady() {
            if (MODE === 'checkout') {
                el('submitBtn').disabled = false;
                return;
            }
            if (state.selfie) {
                el('submitBtn').disabled = false;
            }
        }

        async function submitAttendance() {
            clearFlowError();
            if (el('submitBtn').disabled || state.submitActive) return;
            state.submitActive = true;

            const btn = el('submitBtn');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            const payload = {
                location_id: state.location.id,
                latitude: state.coords.lat,
                longitude: state.coords.lng,
                selfie: state.selfie,
            };

            const url = MODE === 'checkin' ? '{{ route("employee.absensi.checkin") }}' : '{{ route("employee.absensi.checkout") }}';
            const label1 = MODE === 'checkin' ? 'Jam Masuk' : 'Jam Keluar';
            const res = await fetchJson(url, payload);

            btn.innerHTML = original;
            btn.disabled = false;
            state.submitActive = false;

            if (!res.data.ok) {
                state.selfie = null;
                if (res.status === 422 && res.data.distance) {
                    state.withinRadius = false;
                    setActiveStep(2);
                    el('gpsStatus').innerHTML = '<div class="alert alert-danger py-2 small"><i class="bi bi-x-circle me-1"></i> ' + res.data.message + '</div>';
                } else {
                    showFlowError(res.data.message || 'Gagal menyimpan absensi.');
                }
                return;
            }

            document.querySelector('.progress-steps').classList.add('d-none');
            document.querySelectorAll('.flow-step').forEach((s) => s.classList.add('d-none'));
            el('doneCard').classList.remove('d-none');
            el('doneTitle').textContent = 'Absensi Tersimpan';
            el('doneSub').innerHTML = 'Anda telah ' + (res.data.action === 'checkin' ? 'check-in' : 'check-out') + ' di <strong>' + state.location.name + '</strong>';
            el('doneLabel1').textContent = label1;
            const key = res.data.action === 'checkin' ? 'check_in' : 'check_out';
            el('doneValue1').textContent = res.data.attendance[key] || '--:--:--';
            el('doneValue2').textContent = state.location.name;
            el('doneCard').scrollIntoView({ behavior: 'smooth' });
            stopCamera();
        }

        function stopCamera() {
            if (state.stream) {
                state.stream.getTracks().forEach((t) => t.stop());
            }
            state.stream = null;
            state.scanning = false;
        }

        function resetFlow() {
            window.location.reload();
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (PRELOADED_TOKEN) {
                document.getElementById('step-scan').classList.add('d-none');
                performVerify(PRELOADED_TOKEN);
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .camera-wrap {
            position: relative;
            width: 100%;
            max-width: 480px;
            aspect-ratio: 4 / 3;
            background: #0f172a;
            border-radius: 1rem;
            overflow: hidden;
        }
        .camera-wrap video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .scan-overlay {
            position: absolute;
            inset: 0;
            margin: auto;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            opacity: 0;
        }
        .progress-steps { gap: .25rem; }
        .step-item { text-align: center; position: relative; }
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            font-weight: 700;
            font-size: .85rem;
        }
        .step-item.done .step-badge { background: #198754; color: #fff; }
        .step-item.active .step-badge { background: #2563eb; color: #fff; }
        .step-item.active .step-name { color: #0f172a; font-weight: 600; }
        .step-name { font-size: .78rem; color: #94a3b8; }
        .cursor-pointer { cursor: pointer; }
    </style>
@endpush