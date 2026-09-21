<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Laporan Absensi</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #0f172a; margin: 24px; font-size: 12px; }
        .head { text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
        .head h2 { margin: 0 0 4px; font-size: 20px; }
        .head div { color: #475569; }
        .meta { margin-bottom: 16px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 2px 8px; }
        .meta .label { width: 180px; color: #475569; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; }
        table.data th { background: #e2e8f0; }
        table.data td.num, table.data th.num { text-align: right; }
        table.data td.center, table.data th.center { text-align: center; }
        .empty { text-align: center; color: #475569; padding: 40px 0; }
        .signature { margin-top: 40px; display: flex; justify-content: flex-end; }
        .signature div { text-align: center; }
        .signature .name { margin-top: 70px; font-weight: 600; text-decoration: underline; }
        .no-print { text-align: right; margin-bottom: 12px; }
        @media print {
            .no-print { display: none; }
            body { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="print-btn">Tutup</button>
        <style>
            .print-btn { padding: 6px 14px; border: 1px solid #475569; background: #0f172a; color: #fff; border-radius: 6px; cursor: pointer; }
        </style>
    </div>

    <div class="head">
        <h2>SISTEM ABSENSI KARYAWAN</h2>
        <div>Laporan Absensi Periode {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</div>
    </div>

    <div class="meta">
        <table>
            <tr><td class="label">Karyawan</td><td>{{ $filterEmployeeId !== null && isset($rekap[0]) ? $rekap[0]['employee']->user->name : 'Semua Karyawan' }}</td></tr>
            <tr><td class="label">Lokasi</td><td>{{ $filterLocationId !== null ? (\App\Models\AttendanceLocation::find($filterLocationId)?->name ?? 'Semua Lokasi') : 'Semua Lokasi' }}</td></tr>
            <tr><td class="label">Status</td><td>{{ $filterStatus !== null ? ($statusOptions[$filterStatus] ?? ucfirst($filterStatus)) : 'Semua Status' }}</td></tr>
            <tr><td class="label">Total Data</td><td>{{ $attendances->count() }} record</td></tr>
        </table>
    </div>

    <h3 style="margin:0 0 8px;font-size:14px">REKAP ABSENSI</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Nama</th>
                <th>NIK</th>
                <th>Jabatan</th>
                <th class="center">Hari Kerja</th>
                <th class="center">Hadir</th>
                <th class="center">Terlambat</th>
                <th class="center">Izin</th>
                <th class="center">Sakit</th>
                <th class="center">Alpha</th>
                <th class="center">Libur</th>
                <th class="center">Tidak Hadir</th>
                <th class="center">% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap as $row)
                <tr>
                    <td>{{ $row['employee']->user->name }}</td>
                    <td>{{ $row['employee']->nik }}</td>
                    <td>{{ $row['employee']->position }}</td>
                    <td class="center">{{ $row['expected'] }}</td>
                    <td class="center">{{ $row['hadir'] }}</td>
                    <td class="center">{{ $row['terlambat'] }}</td>
                    <td class="center">{{ $row['izin'] }}</td>
                    <td class="center">{{ $row['sakit'] }}</td>
                    <td class="center">{{ $row['alpha'] }}</td>
                    <td class="center">{{ $row['libur'] }}</td>
                    <td class="center">{{ $row['tidak_hadir'] }}</td>
                    <td class="center">{{ $row['percentage'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="12" class="empty">Tidak ada karyawan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin:0 0 8px;font-size:14px">DETAIL LAPORAN</h3>
    @if ($attendances->isEmpty())
        <div class="empty">
            <strong>Tidak ada data absensi pada periode yang dipilih.</strong>
        </div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Jabatan</th>
                    <th>Lokasi</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Jarak</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->date->format('d M Y') }}</td>
                        <td>{{ $attendance->employee?->user?->name ?? '-' }}</td>
                        <td>{{ $attendance->employee?->nik ?? '-' }}</td>
                        <td>{{ $attendance->employee?->position ?? '-' }}</td>
                        <td>{{ $attendance->location?->name ?? '-' }}</td>
                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') : '-' }}</td>
                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') : '-' }}</td>
                        <td>{{ $attendance->check_in_distance !== null ? number_format($attendance->check_in_distance).' m' : '-' }}</td>
                        <td>{{ $attendance->statusLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature">
        <div>
            <div>{{ Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            <div class="name">{{ $currentUser->name }}</div>
            <div>Administrator</div>
        </div>
    </div>
</body>
</html>