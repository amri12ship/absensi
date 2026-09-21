<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $location->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #fff;
            padding: 2rem;
            text-align: center;
        }
        .qr-wrapper {
            border: 2px dashed #94a3b8;
            border-radius: 16px;
            padding: 24px;
            background: #fff;
        }
        .qr-wrapper img { width: 300px; height: auto; display: block; }
        .brand { font-size: 20px; font-weight: 700; margin-bottom: 4px; color: #0f172a; }
        .location-name { font-size: 16px; font-weight: 600; color: #334155; margin-top: 12px; }
        .location-address { font-size: 13px; color: #64748b; margin-top: 4px; max-width: 360px; }
        .token { font-size: 12px; color: #475569; margin-top: 12px; word-break: break-all; font-family: ui-monospace, monospace; }
        .footer { margin-top: 16px; font-size: 12px; color: #94a3b8; }
        .no-print { margin-bottom: 24px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; min-height: auto; }
            .qr-wrapper { border-width: 1px; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print" style="padding:10px 24px;background:#2563eb;color:#fff;border:0;border-radius:8px;font-size:14px;cursor:pointer">
        🖨 Cetak QR Code
    </button>
    <div class="qr-wrapper">
        <div class="brand">Absensi Karyawan</div>
        <img src="{{ $qr }}" alt="QR Code {{ $location->name }}">
        <div class="location-name">{{ $location->name }}</div>
        @if ($location->address)
            <div class="location-address">{{ $location->address }}</div>
        @endif
        <div class="token">Token: {{ $location->public_token }}</div>
    </div>
    <div class="footer">Bawa QR Code ini ke lokasi agar dapat dipindai karyawan | {{ now()->format('d M Y') }}</div>
</body>
</html>